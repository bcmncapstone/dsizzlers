<?php

namespace App\Http\Controllers\Franchisee;

use App\Http\Controllers\Controller;
use App\Support\MediaStorage;
use Illuminate\Support\Facades\Auth;
use App\Models\Branch;
use Illuminate\Support\Facades\Storage;

class FranchiseeController extends Controller
{
    /**
     * Show all active contracts for the logged-in franchisee.
     */
    public function account()
    {
        $franchisee = Auth::user();

        $archivedBranchIds = $this->getArchivedBranchIds();

        $branches = Branch::query()
            ->where('email', $franchisee->franchisee_email)
            ->whereRaw('branch_status = true')
            ->when(! empty($archivedBranchIds), function ($query) use ($archivedBranchIds) {
                return $query->whereNotIn('branch_id', $archivedBranchIds);
            })
            ->orderByDesc('contract_expiration')
            ->orderByDesc('branch_id')
            ->get();

        return view('franchisee.account.index', compact('branches'));
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

    // Download and Preview Contract
    public function downloadContract($id)
    {
        $franchisee = Auth::user();

        $branch = Branch::where('email', $franchisee->franchisee_email)
                        ->where('branch_id', $id)
                        ->firstOrFail();

        if (! $branch->contract_file) {
            return back()
                ->with('error', 'Contract file not found.')
                ->with('flash_timeout', 3000);
        }

        if (MediaStorage::isRemote($branch->contract_file)) {
            $downloadName = basename(parse_url($branch->contract_file, PHP_URL_PATH) ?: 'contract');

            if (request()->query('mode') === 'download') {
                return MediaStorage::downloadResponse($branch->contract_file, null, $downloadName);
            }

            return MediaStorage::previewResponse($branch->contract_file);
        }

        $filePath = storage_path('app/private/public/contracts/' . $branch->contract_file);

        if (! file_exists($filePath)) {
            return back()
                ->with('error', 'Contract file not found.')
                ->with('flash_timeout', 3000);
        }

        if (request()->query('mode') === 'download') {
            return MediaStorage::downloadResponse($branch->contract_file, $filePath, basename($branch->contract_file));
        }

        return MediaStorage::previewResponse($branch->contract_file, $filePath);
    }
}
