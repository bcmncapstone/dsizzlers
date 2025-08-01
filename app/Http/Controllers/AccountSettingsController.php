<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AccountSettingsController extends Controller
{
    // Settings main page
    public function index()
    {
       if (!Auth::check()) {
    return redirect()->route('admin.login');
}
        return view('settings.index');
    }

    // Show password update form
    public function editPassword()
    {
        if (!$this->checkUserSession()) {
            return redirect()->route('admin.login');
        }

        return view('settings.password');
    }

    // Handle password update
    public function updatePassword(Request $request)
    {
        if (!$this->checkUserSession()) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Identify user based on role session and update password
        if (session()->has('franchisor_id')) {
            $userId = session('franchisor_id');
            DB::table('users')->where('id', $userId)->update([
                'password' => Hash::make($request->password),
            ]);
        } elseif (session()->has('franchisee_id')) {
            $userId = session('franchisee_id');
            DB::table('users')->where('id', $userId)->update([
                'password' => Hash::make($request->password),
            ]);
        } elseif (session()->has('franchisor_staff_id')) {
            $userId = session('franchisor_staff_id');
            DB::table('users')->where('id', $userId)->update([
                'password' => Hash::make($request->password),
            ]);
        } elseif (session()->has('franchisee_staff_id')) {
            $userId = session('franchisee_staff_id');
            DB::table('users')->where('id', $userId)->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return back()->with('success', 'Password updated successfully.');
    }

    // Check if any user session exists
    private function checkUserSession()
    {
        return session()->has('franchisor_id') ||
               session()->has('franchisee_id') ||
               session()->has('franchisor_staff_id') ||
               session()->has('franchisee_staff_id');
    }
}
