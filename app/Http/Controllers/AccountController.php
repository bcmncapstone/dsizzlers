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
        // Common validation rules
        $request->validate([
            'role' => 'required|in:franchisee,franchisor_staff,franchisee_staff',
            'fname' => 'required|string',
            'lname' => 'required|string',
            'contact' => 'required|string',
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

            Franchisee::create([
                'admin_id' => session('admin_id'),
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

            AdminStaff::create([
                'staffAdmin_id' => session('admin_id'),
                'astaff_fname' => $request->fname,
                'astaff_lname' => $request->lname,
                'astaff_contactNo' => $request->contact,
                'astaff_username' => $request->username,
                'astaff_pass' => Hash::make($request->password),
                'astaff_status' => 'Active',
            ]);
        } elseif ($role === 'franchisee_staff') {
            $request->validate([
                'username' => 'required|string|unique:franchisee_staff,fstaff_username',
            ]);

            FranchiseeStaff::create([
                'franchisee_id' => session('admin_id'),
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
