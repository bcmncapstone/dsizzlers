<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaffAccountController extends Controller
{
    // Show Franchisor Staff Account
    public function showFranchisorStaff(Request $request)
    {
        $user = auth('franchisor_staff')->user();
        return view('franchisor-staff.account', compact('user'));
    }

    // Show Franchisee Staff Account
    public function showFranchiseeStaff(Request $request)
    {
        $user = auth('franchisee_staff')->user();
        return view('franchisee-staff.account', compact('user'));
    }

    // Update Franchisor Staff Account
    public function updateFranchisorStaff(Request $request)
    {
        $user = auth('franchisor_staff')->user();
        
        $request->validate([
            'contactNo' => 'required|string|max:13',
            'email' => 'required|email|unique:admin_staff,astaff_email,'.$user->astaff_id.',astaff_id',
        ]);

        $user->update([
            'astaff_contactNo' => $request->contactNo,
            'astaff_email' => $request->email,
        ]);

        return back()
            ->with('success', 'Profile updated successfully')
            ->with('flash_timeout', 3000);
    }

    // Update Franchisee Staff Account
    public function updateFranchiseeStaff(Request $request)
    {
        $user = auth('franchisee_staff')->user();
        
        $request->validate([
            'contactNo' => 'required|string|max:13',
            'email' => 'required|email|unique:franchisee_staff,fstaff_email,'.$user->fstaff_id.',fstaff_id',
        ]);

        $user->update([
            'fstaff_contactNo' => $request->contactNo,
            'fstaff_email' => $request->email,
        ]);

        return back()
            ->with('success', 'Profile updated successfully')
            ->with('flash_timeout', 3000);
    }
}
