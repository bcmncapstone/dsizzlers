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
        $request->validate([
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

        $user = auth('franchisor_staff')->user();
        $user->astaff_pass = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }

    //Franchisee Staff Password
   
    public function editFranchiseeStaffPassword()
    {
        return view('franchisee-staff.password');
    }

    public function updateFranchiseeStaffPassword(Request $request)
    {
        $request->validate([
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

        $user = auth('franchisee_staff')->user();
        $user->fstaff_pass = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }

    //Franchisee Password
    public function editFranchiseePassword()
    {
        return view('franchisee.password');
    }

    public function updateFranchiseePassword(Request $request)
    {
        $request->validate([
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

        $user = auth('franchisee')->user();
        $user->franchisee_pass = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }

    // Admin Password
    public function editAdminPassword()
    {
        return view('admin.password');
    }

    public function updateAdminPassword(Request $request)
    {
        $request->validate([
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

        $user = auth('admin')->user();
        $user->admin_pass = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }
}
