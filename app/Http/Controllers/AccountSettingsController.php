<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountSettingsController extends Controller
{
    // Settings main page
    public function index()
    {
        return view('settings.index');
    }

    // Show password update form
    public function editPassword()
    {
        return view('settings.password');
    }

    // Handle password update
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }
}
