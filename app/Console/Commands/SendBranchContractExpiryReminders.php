<?php

namespace App\Console\Commands;

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

    protected $description = 'Send reminder emails to franchisees before branch contract expiration.';

    public function handle(): int
    {
        $days = max(0, (int) $this->option('days'));
        $isDryRun = (bool) $this->option('dry-run');
        $targetDate = Carbon::today()->addDays($days)->toDateString();

        $archivedBranchIds = $this->getArchivedBranchIds();

        $branches = Branch::query()
            ->whereDate('contract_expiration', $targetDate)
            ->whereRaw('branch_status = true')
            ->whereNotNull('email')
            ->when(! empty($archivedBranchIds), function ($query) use ($archivedBranchIds) {
                return $query->whereNotIn('branch_id', $archivedBranchIds);
            })
            ->orderBy('branch_id')
            ->get();

        if ($branches->isEmpty()) {
            $this->info("No branch contracts expiring in {$days} day(s).");

            return self::SUCCESS;
        }

        $this->info("Found {$branches->count()} branch contract(s) expiring on {$targetDate}.");

        $sentCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        foreach ($branches as $branch) {
            $expirationDate = optional($branch->contract_expiration)->toDateString() ?? $targetDate;
            $cacheKey = $this->buildReminderCacheKey((int) $branch->branch_id, $expirationDate);

            if (Cache::has($cacheKey)) {
                $this->line("Skipped branch #{$branch->branch_id} ({$branch->email}): reminder already sent.");
                $skippedCount++;
                continue;
            }

            if ($isDryRun) {
                $this->line("Dry run: would send to {$branch->email} for branch #{$branch->branch_id}.");
                $sentCount++;
                continue;
            }

            try {
                Mail::to($branch->email)->send(new BranchContractExpiryReminder($branch, $days));

                // Keep reminder marker beyond expiration date to avoid duplicate sends.
                $reminderTtl = Carbon::parse($expirationDate)->endOfDay()->addDays(7);
                Cache::put($cacheKey, now()->toDateTimeString(), $reminderTtl);

                $this->info("Sent reminder to {$branch->email}.");
                $sentCount++;
            } catch (Throwable $exception) {
                $failedCount++;
                $this->error("Failed branch #{$branch->branch_id} ({$branch->email}): {$exception->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Summary: processed {$branches->count()}, sent {$sentCount}, skipped {$skippedCount}, failed {$failedCount}.");

        return $failedCount > 0 ? self::FAILURE : self::SUCCESS;
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

    protected function buildReminderCacheKey(int $branchId, string $expirationDate): string
    {
        return "contract-expiry-reminder:branch:{$branchId}:{$expirationDate}";
    }
}
