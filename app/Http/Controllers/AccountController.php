<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Franchisee;
use App\Models\FranchisorStaff;
use App\Models\FranchiseeStaff;
use Illuminate\Support\Facades\Auth;


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
            ]);

            $created = FranchisorStaff::create([
                'admin_id' => Auth::guard('admin')->user()->admin_id,
                'astaff_fname' => $request->fname,
                'astaff_lname' => $request->lname,
                'astaff_contactNo' => $request->contact,
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

    //Handle Franchisee Staff Account Creation
    public function storeFranchiseeStaff(Request $request)
    {
        // Validation based on Blade input names
        $request->validate([
            'fname'   => 'required|string|max:255',
            'lname'   => 'required|string|max:255',
            'contact' => 'required|string|max:20',
            'username'=> 'required|string|unique:franchisee_staff,fstaff_username',
        ]);

        FranchiseeStaff::create([
            'franchisee_id'    => auth('franchisee')->id(),
            'fstaff_fname'     => $request->fname,
            'fstaff_lname'     => $request->lname,
            'fstaff_contactNo' => $request->contact,
            'fstaff_username'  => $request->username,
            'fstaff_pass'      => Hash::make($request->password),
            'fstaff_status'    => 'Active',
        ]);

        return redirect()->route('account.create')
            ->with('success', 'Franchisee Staff account created successfully!')
            ->with('flash_timeout', 3000);
    }
}
