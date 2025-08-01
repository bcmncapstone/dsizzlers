<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;

class BranchController extends Controller
{
        public function index()
    {
        $branches = Branch::where('branch_status', true)->get();
        return view('admin.branches.index', compact('branches'));
    }

    // Show archived branches
    public function archived()
    {
        $branches = Branch::where('branch_status', false)->get();
        return view('admin.branches.archived', compact('branches'));
    }

    // Show form to add a new branch
    public function create()
    {
        return view('admin.branches.create');
    }

    // Store new branch in database
   public function store(Request $request)
{
    $validated = $request->validate([
        'location' => 'required|string|max:255',
        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'email' => 'required|email|unique:branches,email',
        'contact_number' => 'required|string|max:20',
        'contract_file' => 'required|file|mimes:pdf,docx,doc|max:2048',
        'contract_expiration' => 'required|date', 
    ]);

    // Save uploaded contract file
    if ($request->hasFile('contract_file')) {
        $file = $request->file('contract_file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('public/contracts', $filename);
        $validated['contract_file'] = $filename;
    }

    $validated['branch_status'] = true; // Active by default
    Branch::create($validated);

    return redirect()->route('admin.branches.index')->with('success', 'Branch added successfully.');
}


    // Archive branch (set branch_status to false)
    public function archive($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->branch_status = false;
        $branch->save();

        return redirect()->route('admin.branches.index')->with('success', 'Branch archived successfully.');
    }

    // Download/view contract file
    public function downloadContract($id)
    {
        $branch = Branch::findOrFail($id);
        $path = storage_path('app/public/contracts/' . $branch->contract_file);

        if (file_exists($path)) {
            return response()->download($path);
        } else {
            return redirect()->back()->with('error', 'Contract file not found.');
        }
    }
}