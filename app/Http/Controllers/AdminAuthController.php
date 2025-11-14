<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;   // already imported
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        // Validate input (optional but good practice)
        $request->validate([
            'admin_username' => 'required',
            'admin_pass'     => 'required',
        ]);

        // Fetch the admin record by username
        $admin = Admin::where('admin_username', $request->admin_username)->first();

        // Use Hash::check to compare the plain‑text password with the hashed one
        if ($admin && Hash::check($request->admin_pass, $admin->admin_pass)) {
            // login success ➜ sign in using the 'admin' guard so middleware recognizes the session
            Auth::guard('admin')->login($admin);
            return redirect()->intended('/admin/dashboard');
        }

        // Login failed
        return back()->withErrors(['loginError' => 'Invalid credentials.']);
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect('/admin/login');
    }
}
