<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountSettingsController extends Controller
{
    //Franchisor Staff Password
    public function editFranchisorStaffPassword()
    {
        return view('franchisor-staff.password');
    }

    public function updateFranchisorStaffPassword(Request $request)
    {
        $user = auth('franchisor_staff')->user();

        $request->validate([
            'username' => 'required|string|max:100|unique:admin_staff,astaff_username,'.$user->astaff_id.',astaff_id',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z0-9]).+$/',
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character.',
        ]);

        // Update username and password
        $user->astaff_username = $request->username;
        $user->astaff_pass = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    //Franchisee Staff Password
   
    public function editFranchiseeStaffPassword()
    {
        return view('franchisee-staff.password');
    }

    public function updateFranchiseeStaffPassword(Request $request)
    {
        $user = auth('franchisee_staff')->user();

        $request->validate([
            'username' => 'required|string|max:100|unique:franchisee_staff,fstaff_username,'.$user->fstaff_id.',fstaff_id',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z0-9]).+$/',
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character.',
        ]);

        $user->fstaff_username = $request->username;
        $user->fstaff_pass = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    //Franchisee Password
    public function editFranchiseePassword()
    {
        return view('franchisee.password');
    }

    public function updateFranchiseePassword(Request $request)
    {
        $user = auth('franchisee')->user();

        $request->validate([
            'username' => 'required|string|max:100|unique:franchisees,franchisee_username,'.$user->franchisee_id.',franchisee_id',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z0-9]).+$/',
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character.',
        ]);

        $user->franchisee_username = $request->username;
        $user->franchisee_pass = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    // Admin Password
    public function editAdminPassword()
    {
        return view('admin.password');
    }

    public function updateAdminPassword(Request $request)
    {
        $user = auth('admin')->user();

        $request->validate([
            'username' => 'required|string|max:100|unique:admins,admin_username,'.$user->admin_id.',admin_id',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z0-9]).+$/',
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character.',
        ]);

        $user->admin_username = $request->username;
        $user->admin_pass = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    // --- Username update methods for each guard ---
    // Franchisor Staff Username
    public function editFranchisorStaffUsername()
    {
        return view('franchisor-staff.username');
    }

    public function updateFranchisorStaffUsername(Request $request)
    {
        $user = auth('franchisor_staff')->user();
        $request->validate([
            'username' => 'required|string|max:100|unique:admin_staff,astaff_username,'.$user->astaff_id.',astaff_id',
        ]);

        $user->astaff_username = $request->username;
        $user->save();

        return back()->with('success', 'Username updated successfully.');
    }

    // Franchisee Staff Username
    public function editFranchiseeStaffUsername()
    {
        return view('franchisee-staff.username');
    }

    public function updateFranchiseeStaffUsername(Request $request)
    {
        $user = auth('franchisee_staff')->user();
        $request->validate([
            'username' => 'required|string|max:100|unique:franchisee_staff,fstaff_username,'.$user->fstaff_id.',fstaff_id',
        ]);

        $user->fstaff_username = $request->username;
        $user->save();

        return back()->with('success', 'Username updated successfully.');
    }

    // Franchisee Username
    public function editFranchiseeUsername()
    {
        return view('franchisee.username');
    }

    public function updateFranchiseeUsername(Request $request)
    {
        $user = auth('franchisee')->user();
        $request->validate([
            'username' => 'required|string|max:100|unique:franchisees,franchisee_username,'.$user->franchisee_id.',franchisee_id',
        ]);

        $user->franchisee_username = $request->username;
        $user->save();

        return back()->with('success', 'Username updated successfully.');
    }

    // Admin Username
    public function editAdminUsername()
    {
        return view('admin.username');
    }

    public function updateAdminUsername(Request $request)
    {
        $user = auth('admin')->user();
        $request->validate([
            'username' => 'required|string|max:100|unique:admins,admin_username,'.$user->admin_id.',admin_id',
        ]);

        $user->admin_username = $request->username;
        $user->save();

        return back()->with('success', 'Username updated successfully.');
    }
}
