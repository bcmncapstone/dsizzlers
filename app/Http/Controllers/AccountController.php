<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Franchisee;
use App\Models\AdminStaff;
use App\Models\FranchiseeStaff;

class AccountController extends Controller
{
    public function create()
    {
        $franchisees = Franchisee::all(); // for the dropdown
        return view('admin.accounts.create', compact('franchisees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'role' => 'required|in:franchisee,franchisor_staff,franchisee_staff',
            'fname' => 'required|string',
            'lname' => 'required|string',
            'contact' => 'required|string',
            'username' => 'required|string|unique:franchisees,franchisee_username|unique:admin_staff,astaff_username|unique:franchisee_staff,fstaff_username',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'franchisee_id' => 'nullable|exists:franchisees,franchisee_id',
        ]);

        if (!session()->has('admin_id')) {
            return redirect()->route('admin.login')->withErrors(['Session expired. Please log in again.']);
        }

        $adminId = session('admin_id');
        $role = $request->role;

        if ($role === 'franchisee') {
            Franchisee::create([
                'admin_id' => $adminId,
                'franchisee_name' => $request->fname . ' ' . $request->lname,
                'franchisee_contactNo' => $request->contact,
                'franchisee_username' => $request->username,
                'franchisee_pass' => Hash::make($request->password),
                'franchisee_status' => 'Active',
                'franchisee_email' => $request->email ?? 'n/a',
                'franchisee_address' => $request->address ?? 'n/a',
            ]);
        } elseif ($role === 'franchisor_staff') {
            AdminStaff::create([
                'staffAdmin_id' => $adminId,
                'astaff_fname' => $request->fname,
                'astaff_lname' => $request->lname,
                'astaff_contactNo' => $request->contact,
                'astaff_username' => $request->username,
                'astaff_pass' => Hash::make($request->password),
                'astaff_status' => 'Active',
            ]);
        } elseif ($role === 'franchisee_staff') {
            if (!$request->franchisee_id) {
                return redirect()->back()->withErrors(['Please select a franchisee for the Franchisee Staff.']);
            }

            FranchiseeStaff::create([
                'franchisee_id' => $request->franchisee_id,
                'fstaff_fname' => $request->fname,
                'fstaff_lname' => $request->lname,
                'fstaff_contactNo' => $request->contact,
                'fstaff_username' => $request->username,
                'fstaff_pass' => Hash::make($request->password),
                'fstaff_status' => 'Active',
            ]);
        }

        return redirect()->back()->with('success', 'Account created successfully.');
    }
}
