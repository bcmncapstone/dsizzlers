<?php

namespace App\Console\Commands;

use App\Mail\BranchContractExpiredNotification;
use App\Mail\BranchContractExpiryReminder;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SendBranchContractExpiryReminders extends Command
{
    protected $signature = 'contracts:send-expiry-reminders
        {--days=7 : Days before expiration to notify}
        {--dry-run : List recipients without sending email}';

    protected $description = 'Send 7-day contract reminders, auto-archive expired contracts, and send expiry notifications.';

    public function handle(): int
    {
        $days = max(0, (int) $this->option('days'));
        $isDryRun = (bool) $this->option('dry-run');
        $today = Carbon::today();
        $targetDate = $today->copy()->addDays($days)->toDateString();

        $archivedBranchIds = $this->getArchivedBranchIds();

        $reminderBranches = Branch::query()
            ->whereDate('contract_expiration', $targetDate)
            ->whereRaw('branch_status = true')
            ->whereNotNull('contract_expiration')
            ->whereNotNull('email')
            ->when(! empty($archivedBranchIds), function ($query) use ($archivedBranchIds) {
                return $query->whereNotIn('branch_id', $archivedBranchIds);
            })
            ->orderBy('branch_id')
            ->get();

        if ($reminderBranches->isEmpty()) {
            $this->info("No branch contracts expiring in {$days} day(s).");
        } else {
            $this->info("Found {$reminderBranches->count()} branch contract(s) expiring on {$targetDate}.");
        }

        $reminderSentCount = 0;
        $reminderSkippedCount = 0;
        $reminderFailedCount = 0;

        foreach ($reminderBranches as $branch) {
            /** @var Branch $branch */
            $expirationDate = optional($branch->contract_expiration)->toDateString() ?? $targetDate;
            $cacheKey = $this->buildReminderCacheKey((int) $branch->branch_id, $expirationDate);

            if (Cache::has($cacheKey)) {
                $this->line("Skipped reminder for branch #{$branch->branch_id} ({$branch->email}): already sent.");
                $reminderSkippedCount++;
                continue;
            }

            if ($isDryRun) {
                $this->line("Dry run: would send reminder to {$branch->email} for branch #{$branch->branch_id}.");
                $reminderSentCount++;
                continue;
            }

            try {
                Mail::to($branch->email)->queue(new BranchContractExpiryReminder($branch, $days));

                // Keep reminder marker beyond expiration date to avoid duplicate sends.
                $reminderTtl = Carbon::parse($expirationDate)->endOfDay()->addDays(7);
                Cache::put($cacheKey, now()->toDateTimeString(), $reminderTtl);

                $this->info("Sent reminder to {$branch->email}.");
                $reminderSentCount++;
            } catch (Throwable $exception) {
                $reminderFailedCount++;
                $this->error("Failed reminder for branch #{$branch->branch_id} ({$branch->email}): {$exception->getMessage()}");
            }
        }

        $expiredBranches = Branch::query()
            ->whereDate('contract_expiration', '<=', $today->toDateString())
            ->whereNotNull('contract_expiration')
            ->when(! empty($archivedBranchIds), function ($query) use ($archivedBranchIds) {
                return $query->whereNotIn('branch_id', $archivedBranchIds);
            })
            ->orderBy('branch_id')
            ->get();

        if ($expiredBranches->isEmpty()) {
            $this->info('No newly expired branch contracts to archive today.');
        } else {
            $this->info("Found {$expiredBranches->count()} expired contract(s) to auto-archive.");
        }

        $expiredArchivedCount = 0;
        $expiredNotifiedCount = 0;
        $expiredEmailSkippedCount = 0;
        $expiredFailedCount = 0;

        foreach ($expiredBranches as $branch) {
            /** @var Branch $branch */
            $branchId = (int) $branch->branch_id;

            if (! in_array($branchId, $archivedBranchIds, true)) {
                $archivedBranchIds[] = $branchId;
                $expiredArchivedCount++;
            }

            $expirationDate = optional($branch->contract_expiration)->toDateString() ?? $today->toDateString();
            $notificationKey = $this->buildExpiredCacheKey($branchId, $expirationDate);

            if (empty($branch->email)) {
                $this->line("Skipped expiry email for branch #{$branchId}: no email address.");
                $expiredEmailSkippedCount++;
                continue;
            }

            if (Cache::has($notificationKey)) {
                $this->line("Skipped expiry email for branch #{$branchId} ({$branch->email}): already sent.");
                $expiredEmailSkippedCount++;
                continue;
            }

            if ($isDryRun) {
                $this->line("Dry run: would send expiry notice to {$branch->email} for branch #{$branchId}.");
                $expiredNotifiedCount++;
                continue;
            }

            try {
                Mail::to($branch->email)->queue(new BranchContractExpiredNotification($branch));

                // Keep a marker for 30 days to avoid duplicate notices.
                Cache::put($notificationKey, now()->toDateTimeString(), now()->addDays(30));

                $this->info("Sent expiry notice to {$branch->email}.");
                $expiredNotifiedCount++;
            } catch (Throwable $exception) {
                $expiredFailedCount++;
                $this->error("Failed expiry notice for branch #{$branchId} ({$branch->email}): {$exception->getMessage()}");
            }
        }

        if (! $isDryRun) {
            $this->saveArchivedBranchIds($archivedBranchIds);
        }

        $this->newLine();
        $this->info(
            "Reminder summary: processed {$reminderBranches->count()}, sent {$reminderSentCount}, skipped {$reminderSkippedCount}, failed {$reminderFailedCount}."
        );
        $this->info(
            "Expiry summary: processed {$expiredBranches->count()}, archived {$expiredArchivedCount}, notified {$expiredNotifiedCount}, email skipped {$expiredEmailSkippedCount}, failed {$expiredFailedCount}."
        );

        return ($reminderFailedCount + $expiredFailedCount) > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function getArchivedBranchIds(): array
    {
        if (! Storage::disk('local')->exists('archived_branches.json')) {
            return [];
        }

        $raw = Storage::disk('local')->get('archived_branches.json');
        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $decoded)));
    }

    protected function saveArchivedBranchIds(array $ids): void
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        Storage::disk('local')->put('archived_branches.json', json_encode($ids));
    }

    protected function buildReminderCacheKey(int $branchId, string $expirationDate): string
    {
        return "contract-expiry-reminder:branch:{$branchId}:{$expirationDate}";
    }

    protected function buildExpiredCacheKey(int $branchId, string $expirationDate): string
    {
        return "contract-expired-notification:branch:{$branchId}:{$expirationDate}";
    }
}
