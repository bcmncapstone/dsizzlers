<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Franchisee;
use App\Support\MediaStorage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BranchController extends Controller
{

    // Show active branches
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        
        // Load archived branch IDs from JSON file
        $archivedIds = $this->getArchivedBranchIds();
        
        // Show only branches that are not archived
        $branches = Branch::query()
            ->when($search, function ($query) use ($search) {
                return $query->where('location', 'like', "%$search%")
                    ->orWhere('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            })
            ->when(!empty($archivedIds), function ($query) use ($archivedIds) {
                return $query->whereNotIn('branch_id', $archivedIds);
            })
            ->get();
        
        return view('admin.branches.index', compact('branches', 'search'));
    }

    // Show archived branches
    public function archived()
    {
        // Load archived branch IDs from JSON file
        $archivedIds = $this->getArchivedBranchIds();
        $branches = empty($archivedIds)
            ? collect()
            : Branch::whereIn('branch_id', $archivedIds)->get();
        return view('admin.branches.archive', compact('branches'));
    }

    // Show form to add a new branch
    public function create()
    {
        // Pass existing franchisees so admin can select and auto-fill details
        $franchisees = Franchisee::select('franchisee_id','franchisee_name','franchisee_email','franchisee_contactNo')->get();
        return view('admin.branches.create', compact('franchisees'));
    }

    //  Show edit form
    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        $franchisees = Franchisee::select('franchisee_id', 'franchisee_name', 'franchisee_email', 'franchisee_contactNo')->get();
        return view('admin.branches.edit', compact('branch', 'franchisees'));
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
                function ($attribute, $value, $fail) {
                    $exists = Franchisee::where('franchisee_email', $value)->exists();
                    if (!$exists) {
                        $fail('The email must belong to an existing Franchisee account.');
                    }
                }
            ],
            'contact_number' => 'required|string|max:20',
            'contract_file' => 'required|file|mimes:pdf,docx,doc|max:5120',
            'contract_expiration' => 'required|date',
        ], [
            'contract_file.max' => 'The contract file must not be greater than 5MB.',
        ]);

        if ($request->hasFile('contract_file')) {
            $file = $request->file('contract_file');
            $validated['contract_file'] = $this->storeContractFile($file);
        }

        // Create the branch without branch_status first
        $branchData = $validated;
        unset($branchData['branch_status']);
        $branch = Branch::create($branchData);
        
        // Update branch_status to true using raw query to avoid type casting issues
        $branch->update(['branch_status' => DB::raw('true')]);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch added successfully.')
            ->with('flash_timeout', 3000);
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
                function ($attribute, $value, $fail) {
                    $exists = Franchisee::where('franchisee_email', $value)->exists();
                    if (!$exists) {
                        $fail('The email must belong to an existing Franchisee account.');
                    }
                }
            ],
            'contact_number' => 'required|string|max:20',
            'contract_file' => 'nullable|file|mimes:pdf,docx,doc|max:5120',
            'contract_expiration' => 'required|date',
        ], [
            'contract_file.max' => 'The contract file must not be greater than 5MB.',
        ]);

        if ($request->hasFile('contract_file')) {
            MediaStorage::delete($branch->contract_file);

            $file = $request->file('contract_file');
            $validated['contract_file'] = $this->storeContractFile($file);
        }

        $branch->update($validated);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch updated successfully.')
            ->with('flash_timeout', 3000);
    }

    // Archive branch
    public function archive($id)
    {
        $branch = Branch::findOrFail($id);

        // Mark as archived in JSON file (do NOT update DB)
        $archivedIds = $this->getArchivedBranchIds();
        if (!in_array($branch->branch_id, $archivedIds)) {
            $archivedIds[] = $branch->branch_id;
            $this->saveArchivedBranchIds($archivedIds);
        }

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch archived successfully.')
            ->with('flash_timeout', 3000);
    }

    // Restore branch
    public function restore($id)
    {
        // Remove the branch ID from the archived list
        $archivedIds = $this->getArchivedBranchIds();
        $archivedIds = array_values(array_filter($archivedIds, function ($archivedId) use ($id) {
            return (int) $archivedId !== (int) $id;
        }));
        $this->saveArchivedBranchIds($archivedIds);

        return redirect()->route('admin.branches.archived')
            ->with('success', 'Branch restored successfully.')
            ->with('flash_timeout', 3000);
    }

    // Download/Preview contract file
    public function downloadContract(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        if (! $branch->contract_file) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'No contract file attached to this branch.')
                ->with('flash_timeout', 3000);
        }

        if (MediaStorage::isRemote($branch->contract_file)) {
            $downloadName = basename(parse_url($branch->contract_file, PHP_URL_PATH) ?: 'contract');
            if (pathinfo($downloadName, PATHINFO_EXTENSION) === '') {
                $downloadName .= '.pdf';
            }

            if ($request->query('mode') === 'download') {
                return MediaStorage::downloadResponse($branch->contract_file, null, $downloadName);
            }

            return MediaStorage::previewResponse($branch->contract_file);
        }

        // Local file path — only works on localhost, not on cloud hosts like Render
        $filePath = $this->resolveLocalContractPath($branch->contract_file);

        if ($filePath === null) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'File not found.')
                ->with('flash_timeout', 3000);
        }

        if ($request->query('mode') === 'download') {
            return MediaStorage::downloadResponse($branch->contract_file, $filePath, basename($branch->contract_file));
        }

        return MediaStorage::previewResponse($branch->contract_file, $filePath);
    }

    /**
     * Read archived branch IDs from storage/app/archived_branches.json
     * This avoids any database schema changes.
     */
    protected function getArchivedBranchIds(): array
    {
        if (!Storage::disk('local')->exists('archived_branches.json')) {
            return [];
        }

        $raw = Storage::disk('local')->get('archived_branches.json');
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Persist archived branch IDs into storage/app/archived_branches.json
     */
    protected function saveArchivedBranchIds(array $ids): void
    {
        // Ensure unique, numeric IDs
        $ids = array_values(array_unique(array_map('intval', $ids)));
        Storage::disk('local')->put('archived_branches.json', json_encode($ids));
    }

    protected function storeContractFile($file): string
    {
        return MediaStorage::upload($file, 'contracts');
    }

    protected function resolveLocalContractPath(string $contractFile): ?string
    {
        $candidates = [
            'public/contracts/' . ltrim($contractFile, '/'),
            ltrim($contractFile, '/'),
            'contracts/' . ltrim($contractFile, '/'),
        ];

        foreach ($candidates as $candidate) {
            if (Storage::disk('local')->exists($candidate)) {
                return Storage::disk('local')->path($candidate);
            }

            if (Storage::disk('public')->exists($candidate)) {
                return Storage::disk('public')->path($candidate);
            }
        }

        return null;
    }
}
