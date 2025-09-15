<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Franchisee login
    public function showFranchiseeLogin()
    {
        return view('auth.login-franchisee');
    }

    public function loginFranchisee(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::guard('franchisee')->attempt([
            'franchisee_username' => $credentials['username'],
            'password' => $credentials['password'],
        ])) {
            return redirect()->route('franchisee.dashboard');
        }

        return back()->withErrors(['login_error' => 'Invalid username or password.']);
    }

    // Franchisee Staff login
    public function showFranchiseeStaffLogin()
    {
        return view('auth.login-franchisee-staff');
    }

    public function loginFranchiseeStaff(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::guard('franchisee_staff')->attempt([
            'fstaff_username' => $credentials['username'],
            'password' => $credentials['password'],
        ])) {
            return redirect()->route('franchisee-staff.dashboard');
        }

        return back()->withErrors(['login_error' => 'Invalid username or password.']);
    }

    // Franchisor Staff login
    public function showFranchisorStaffLogin()
    {
        return view('auth.login-franchisor-staff');
    }

    public function loginFranchisorStaff(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::guard('franchisor_staff')->attempt([
            'astaff_username' => $credentials['username'],
            'password' => $credentials['password'],
        ])) {
            return redirect()->route('franchisor-staff.dashboard');
        }

        return back()->withErrors(['login_error' => 'Invalid username or password.']);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
