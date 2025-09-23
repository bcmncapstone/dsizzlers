<?php

namespace App\Http\Controllers\Franchisee;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Branch;

class FranchiseeController extends Controller
{
    /**
     * Show the logged-in franchisee's branch details.
     */
    public function account()
    {
        $franchisee = Auth::user();

        // Find branch assigned to this franchisee (by email match)
        $branch = Branch::where('email', $franchisee->franchisee_email)
                        ->where('branch_status', true)
                        ->first();

        if (!$branch) {
            return view('franchisee.account.no-branch');
        }

        return view('franchisee.account.index', compact('branch'));
    }
//Download and Preview Contract
    public function downloadContract($id)
{
    $franchisee = Auth::user();

    // Find the branch that matches the logged-in franchisee's email
    $branch = Branch::where('email', $franchisee->franchisee_email)
                    ->where('branch_id', $id)
                    ->firstOrFail();

    $filePath = storage_path('app/private/public/contracts/' . $branch->contract_file);

    if (!file_exists($filePath)) {
        return back()->with('error', 'Contract file not found.');
    }

    // If mode=download → force download
    if (request()->query('mode') === 'download') {
        return response()->download($filePath);
    }

    // Otherwise → preview if supported (PDF/image)
    return response()->file($filePath);
}
}
