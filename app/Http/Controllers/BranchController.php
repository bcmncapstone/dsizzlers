<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Franchisee;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    // Show active branches
    public function index()
    {
        $branches = Branch::where('branch_status', '=', DB::raw('true'))->get();
        return view('admin.branches.index', compact('branches'));
    }

    // Show archived branches
    public function archived()
    {
        $branches = Branch::where('branch_status', '=', DB::raw('false'))->get();
        return view('admin.branches.archive', compact('branches'));
    }

    // Show form to add a new branch
    public function create()
    {
        return view('admin.branches.create');
    }

    //  Show edit form
    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        return view('admin.branches.edit', compact('branch'));
    }

    // Store new branch
    public function store(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'unique:branches,email',
                function ($attribute, $value, $fail) {
                    $exists = Franchisee::where('franchisee_email', $value)->exists();
                    if (!$exists) {
                        $fail('The email must belong to an existing Franchisee account.');
                    }
                }
            ],
            'contact_number' => 'required|string|max:20',
            'contract_file' => 'required|file|mimes:pdf,docx,doc|max:2048',
            'contract_expiration' => 'required|date',
        ]);

        if ($request->hasFile('contract_file')) {
            $file = $request->file('contract_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/contracts', $filename);
            $validated['contract_file'] = $filename;
        }

        // Create the branch without branch_status first
        $branchData = $validated;
        unset($branchData['branch_status']);
        $branch = Branch::create($branchData);
        
        // Update branch_status to true using raw query to avoid type casting issues
        $branch->update(['branch_status' => DB::raw('true')]);

        return redirect()->route('admin.branches.index')->with('success', 'Branch added successfully.');
    }

    // Update existing branch
    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'unique:branches,email,' . $branch->branch_id . ',branch_id',
                function ($attribute, $value, $fail) {
                    $exists = Franchisee::where('franchisee_email', $value)->exists();
                    if (!$exists) {
                        $fail('The email must belong to an existing Franchisee account.');
                    }
                }
            ],
            'contact_number' => 'required|string|max:20',
            'contract_file' => 'nullable|file|mimes:pdf,docx,doc|max:2048',
            'contract_expiration' => 'required|date',
        ]);

        if ($request->hasFile('contract_file')) {
            $file = $request->file('contract_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/contracts', $filename);
            $validated['contract_file'] = $filename;
        }

        $branch->update($validated);

        return redirect()->route('admin.branches.index')->with('success', 'Branch updated successfully.');
    }

    // Archive branch
    public function archive($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->update(['branch_status' => DB::raw('false')]);

        return redirect()->route('admin.branches.index')->with('success', 'Branch archived successfully.');
    }

    // Restore branch
    public function restore($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->update(['branch_status' => DB::raw('true')]);

        return redirect()->route('admin.branches.archived')->with('success', 'Branch restored successfully.');
    }

    // Download/Preview contract file
     // Download/view contract file
 public function downloadContract(Request $request, $id)
{
    $branch = Branch::findOrFail($id);
    $filePath = storage_path('app/private/public/contracts/' . $branch->contract_file);

    if (!file_exists($filePath)) {
        return back()->with('error', 'File not found.');
    }

    // This forces download for ALL file types (PDF, DOCX, etc.)
    if ($request->query('mode') === 'download') {
        return response()->download($filePath);
    }

    // This tries to preview if supported (PDF, image, etc.)
    return response()->file($filePath);
}
}
