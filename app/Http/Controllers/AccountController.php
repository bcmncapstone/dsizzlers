<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Franchisee;
use App\Models\FranchisorStaff;
use App\Models\FranchiseeStaff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


class AccountController extends Controller
{
    public function create()
    {
        $franchisees = Franchisee::all(); // for the dropdown
        return view('admin.accounts.create', compact('franchisees'));
    }

    public function store(Request $request)
    {
        // Common validation rules
        $request->validate([
            'role' => 'required|in:franchisee,franchisor_staff',
            'fname' => 'required|string',
            'lname' => 'required|string',
            'contact' => 'required|string|max:11',
            'password' => 'required|string|min:6',
        ]);

        $role = $request->role;

        // Role-specific validation for username and additional fields
        if ($role === 'franchisee') {
            $request->validate([
                'username' => 'required|string|unique:franchisees,franchisee_username',
                'email' => 'required|email|unique:franchisees,franchisee_email',
                'address' => 'required|string',
            ]);

            $created = Franchisee::create([
                'admin_id' => Auth::guard('admin')->user()->admin_id,
                'franchisee_name' => $request->fname . ' ' . $request->lname,
                'franchisee_contactNo' => $request->contact,
                'franchisee_username' => $request->username,
                'franchisee_pass' => Hash::make($request->password),
                'franchisee_status' => 'Active',
                'franchisee_email' => $request->email,
                'franchisee_address' => $request->address,
            ]);
        } elseif ($role === 'franchisor_staff') {
            $request->validate([
                'username' => 'required|string|unique:admin_staff,astaff_username',
                'email' => 'required|email|unique:admin_staff,astaff_email',
            ]);

            $created = FranchisorStaff::create([
                'admin_id' => Auth::guard('admin')->user()->admin_id,
                'astaff_fname' => $request->fname,
                'astaff_lname' => $request->lname,
                'astaff_contactNo' => $request->contact,
                'astaff_email' => $request->email,
                'astaff_username' => $request->username,
                'astaff_pass' => Hash::make($request->password),
                'astaff_status' => 'Active',
            ]);
        }

        $newId   = $created->getKey();
        $newType = ($role === 'franchisee') ? 'franchisee' : 'franchisor_staff';

        return redirect()->route('accounts.show', ['type' => $newType, 'id' => $newId])
            ->with('success', 'Account created successfully.')
            ->with('flash_timeout', 3000);
    }

    public function index()
    {
        $franchisees    = Franchisee::orderBy('franchisee_name')->get();
        $franchisorStaff = FranchisorStaff::orderByRaw("astaff_fname || ' ' || astaff_lname")->get();

        return view('admin.accounts.index', compact('franchisees', 'franchisorStaff'));
    }

    public function show($type, $id)
    {
        if ($type === 'franchisee') {
            $account = Franchisee::findOrFail($id);
        } else {
            $account = FranchisorStaff::findOrFail($id);
        }

        return view('admin.accounts.show', compact('account', 'type'));
    }

    //Show Franchisee Account Creation Form
 public function createFranchiseeStaff()
    {
        return view('franchisee.account.create', [
            'roles' => ['franchisee_staff'],
        ]);
    }

    //Show Franchisee Staff List (for franchisee to view their staff)
    public function indexFranchiseeStaff()
    {
        $franchiseeId = auth('franchisee')->id();
        $staff = FranchiseeStaff::where('franchisee_id', $franchiseeId)
            ->orderByRaw("fstaff_fname || ' ' || fstaff_lname")
            ->get();

        $activeStaff = $staff->filter(fn ($member) => $member->fstaff_status === 'Active')->values();
        $archivedStaff = $staff->filter(fn ($member) => $member->fstaff_status !== 'Active')->values();

        return view('franchisee.staff.index', compact('staff', 'activeStaff', 'archivedStaff'));
    }

    //Handle Franchisee Staff Account Creation
    public function storeFranchiseeStaff(Request $request)
    {
        // Validation based on Blade input names
        $request->validate([
            'fname'   => 'required|string|max:255',
            'lname'   => 'required|string|max:255',
            'contact' => 'required|string|max:20',
            'email'   => 'required|email|unique:franchisee_staff,fstaff_email',
            'username'=> 'required|string|unique:franchisee_staff,fstaff_username',
            'password'=> 'required|string|min:6',
        ]);

        FranchiseeStaff::create([
            'franchisee_id'    => auth('franchisee')->id(),
            'fstaff_fname'     => $request->fname,
            'fstaff_lname'     => $request->lname,
            'fstaff_contactNo' => $request->contact,
            'fstaff_email'     => $request->email,
            'fstaff_username'  => $request->username,
            'fstaff_pass'      => Hash::make($request->password),
            'fstaff_status'    => 'Active',
        ]);

        return redirect()->route('franchisee.staff.index')
            ->with('success', 'Franchisee Staff account created successfully!')
            ->with('flash_timeout', 3000);
    }

    /**
     * Archive a franchisee staff account.
     */
    public function archiveFranchiseeStaff(Request $request, int $staffId)
    {
        $franchisee = Auth::guard('franchisee')->user();

        $staff = FranchiseeStaff::where('fstaff_id', $staffId)
            ->where('franchisee_id', $franchisee->franchisee_id)
            ->firstOrFail();

        if ($staff->fstaff_status !== 'Active') {
            return redirect()->route('franchisee.staff.index')
                ->with('error', 'Staff account is already archived.')
                ->with('flash_timeout', 3000);
        }

        $staff->fstaff_status = 'Inactive';
        $staff->save();

        if (!empty($staff->fstaff_email)) {
            try {
                Mail::raw(
                    "Hi {$staff->fstaff_fname},\n\n" .
                    "Your D Sizzlers staff account has been archived by your franchisee admin.\n" .
                    "You can no longer access the staff portal using this account.\n\n" .
                    "If this was unexpected, please contact your franchisee admin.",
                    function ($message) use ($staff) {
                        $message->to($staff->fstaff_email)
                            ->subject('D Sizzlers Staff Account Archived');
                    }
                );
            } catch (\Throwable $e) {
                return redirect()->route('franchisee.staff.index')
                    ->with('success', 'Staff account archived successfully, but email notification could not be sent.')
                    ->with('flash_timeout', 5000);
            }

            return redirect()->route('franchisee.staff.index')
                ->with('success', 'Staff account archived and notified successfully.')
                ->with('flash_timeout', 3000);
        }

        return redirect()->route('franchisee.staff.index')
            ->with('success', 'Staff account archived successfully. No email is set for this staff account.')
            ->with('flash_timeout', 3000);
    }

    /**
     * Restore an archived franchisee staff account.
     */
    public function restoreFranchiseeStaff(Request $request, int $staffId)
    {
        $franchisee = Auth::guard('franchisee')->user();

        $staff = FranchiseeStaff::where('fstaff_id', $staffId)
            ->where('franchisee_id', $franchisee->franchisee_id)
            ->firstOrFail();

        if ($staff->fstaff_status === 'Active') {
            return redirect()->route('franchisee.staff.index')
                ->with('error', 'Staff account is already active.')
                ->with('flash_timeout', 3000);
        }

        $staff->fstaff_status = 'Active';
        $staff->save();

        return redirect()->route('franchisee.staff.index')
            ->with('success', 'Staff account restored successfully.')
            ->with('flash_timeout', 3000);
    }
    /**
     * Archive a franchisor staff account (admin action).
     */
    public function archiveFranchisorStaff(Request $request, int $staffId)
    {
        $staff = FranchisorStaff::findOrFail($staffId);
        if ($staff->astaff_status !== 'Active') {
            return redirect()->route('accounts.index')
                ->with('error', 'Staff account is already archived.')
                ->with('flash_timeout', 3000);
        }
        $staff->astaff_status = 'Inactive';
        $staff->save();
        if (!empty($staff->astaff_email)) {
            try {
                Mail::raw(
                    "Hi {$staff->astaff_fname},\n\n" .
                    "Your D Sizzlers franchisor staff account has been archived by the admin.\n" .
                    "You can no longer access the staff portal using this account.\n\n" .
                    "If this was unexpected, please contact your admin.",
                    function ($message) use ($staff) {
                        $message->to($staff->astaff_email)
                            ->subject('D Sizzlers Staff Account Archived');
                    }
                );
            } catch (\Throwable $e) {
                return redirect()->route('accounts.index')
                    ->with('success', 'Staff account archived successfully, but email notification could not be sent.')
                    ->with('flash_timeout', 5000);
            }
            return redirect()->route('accounts.index')
                ->with('success', 'Staff account archived and notified successfully.')
                ->with('flash_timeout', 3000);
        }
        return redirect()->route('accounts.index')
            ->with('success', 'Staff account archived successfully. No email is set for this staff account.')
            ->with('flash_timeout', 3000);
    }

    /**
     * Restore an archived franchisor staff account (admin action).
     */
    public function restoreFranchisorStaff(Request $request, int $staffId)
    {
        $staff = FranchisorStaff::findOrFail($staffId);
        if ($staff->astaff_status === 'Active') {
            return redirect()->route('accounts.index')
                ->with('error', 'Staff account is already active.')
                ->with('flash_timeout', 3000);
        }
        $staff->astaff_status = 'Active';
        $staff->save();
        return redirect()->route('accounts.index')
            ->with('success', 'Staff account restored successfully.')
            ->with('flash_timeout', 3000);
    }
}
