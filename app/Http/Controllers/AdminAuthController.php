<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;   // already imported
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
            // login success ➜ store ID in session
            session(['admin_id' => $admin->admin_id]);
            return redirect('/admin/dashboard');
        }

        // Login failed
        return back()->withErrors(['loginError' => 'Invalid credentials.']);
    }

    public function logout()
    {
        session()->forget('admin_id');
        return redirect('/admin/login');
    }
}
