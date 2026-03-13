<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

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

    // Unified login for all roles from welcome page
    public function unifiedLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
            'role_type' => 'required|in:franchisor,franchisee,franchisor-staff,franchisee-staff',
        ]);

        $role = $credentials['role_type'];
        $username = $credentials['username'];
        $password = $credentials['password'];

        // Franchisor login
        if ($role === 'franchisor') {
            $admin = Admin::where('admin_username', $username)->first();
            if ($admin && Hash::check($password, $admin->admin_pass)) {
                Auth::guard('admin')->login($admin);
                return redirect()->route('admin.dashboard');
            }
        }

        // Franchisee login
        if ($role === 'franchisee') {
            if (Auth::guard('franchisee')->attempt([
                'franchisee_username' => $username,
                'password' => $password,
            ])) {
                return redirect()->route('franchisee.dashboard');
            }
        }

        // Franchisor Staff login
        if ($role === 'franchisor-staff') {
            if (Auth::guard('franchisor_staff')->attempt([
                'astaff_username' => $username,
                'password' => $password,
            ])) {
                return redirect()->route('franchisor-staff.dashboard');
            }
        }

        // Franchisee Staff login
        if ($role === 'franchisee-staff') {
            if (Auth::guard('franchisee_staff')->attempt([
                'fstaff_username' => $username,
                'password' => $password,
            ])) {
                return redirect()->route('franchisee-staff.dashboard');
            }
        }

        return back()
            ->withInput($request->only('username', 'role_type'))
            ->withErrors(['login_error' => 'Invalid username or password for the selected role.']);
    }
}
