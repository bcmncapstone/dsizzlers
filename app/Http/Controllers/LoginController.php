<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Franchisee;
use App\Models\FranchiseeStaff;
use App\Models\FranchisorStaff;

class LoginController extends Controller
{
    // Show Franchisee Login Page
    public function showFranchiseeLogin()
    {
        return view('auth.login-franchisee');
    }

    // Handle Franchisee Login
    public function loginFranchisee(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $franchisee = Franchisee::where('franchisee_username', $request->username)->first();

        if ($franchisee && Hash::check($request->password, $franchisee->franchisee_pass)) {
            session([
                'user_id' => $franchisee->id,
                'user_name' => $franchisee->franchisee_name,
                'role' => 'franchisee',
            ]);
            return redirect()->route('franchisee.dashboard');
        }

        return back()->withErrors(['login_error' => 'Invalid username or password.']);
    }

    // Show Franchisee Staff Login Page
    public function showFranchiseeStaffLogin()
    {
        return view('auth.login-franchisee-staff');
    }

    // Handle Franchisee Staff Login
    public function loginFranchiseeStaff(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $staff = FranchiseeStaff::where('fstaff_username', $request->username)->first();

        if ($staff && Hash::check($request->password, $staff->fstaff_pass)) {
            session([
                'user_id' => $staff->id,
                'user_name' => $staff->fstaff_fname,
                'role' => 'franchisee-staff',
            ]);
            return redirect()->route('franchisee-staff.dashboard');
        }

        return back()->withErrors(['login_error' => 'Invalid username or password.']);
    }

    // Show Franchisor Staff Login Page
    public function showFranchisorStaffLogin()
    {
        return view('auth.login-franchisor-staff');
    }

    // Handle Franchisor Staff Login
public function loginFranchisorStaff(Request $request)
{
    // Validate input fields
    $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);

    // Query the admin_staff table for matching username (correct column: astaff_username)
    $staff = FranchisorStaff::where('astaff_username', $request->username)->first();

    // Check if a user was found and password matches
    // IMPORTANT: astaff_pass must be hashed. If it's plain text, use === instead of Hash::check temporarily
   if ($staff && Hash::check($request->password, $staff->astaff_pass)){
        // Store session data using the correct column names
        session([
            'user_id' => $staff->astaff_id,
            'user_name' => $staff->astaff_fname,
            'role' => 'franchisor-staff',
        ]);

        // Redirect to the staff dashboard
        return redirect()->route('franchisor-staff.dashboard');
    }

    // If login fails, return with error
    return back()->withErrors(['login_error' => 'Invalid username or password.']);
}
}