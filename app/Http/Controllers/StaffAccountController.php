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
        $request->validate([
            'contactNo' => 'required|string|max:13',
        ]);

        $user = auth('franchisor_staff')->user();
        $user->update([
            'astaff_contactNo' => $request->contactNo,
        ]);

        return back()->with('success', 'Profile updated successfully');
    }

    // Update Franchisee Staff Account
    public function updateFranchiseeStaff(Request $request)
    {
        $request->validate([
            'contactNo' => 'required|string|max:13',
        ]);

        $user = auth('franchisee_staff')->user();
        $user->update([
            'fstaff_contactNo' => $request->contactNo,
                ]);

        return back()->with('success', 'Profile updated successfully');
    }
}
