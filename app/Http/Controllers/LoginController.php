<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Franchisee;
use App\Models\FranchisorStaff;
use App\Models\FranchiseeStaff;

class LoginController extends Controller
{
    //Franchisee
    public function showFranchiseeLogin()
    {
        return view('auth.login-franchisee');
    }

    public function loginFranchisee(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $franchisee = Franchisee::where('franchisee_username', $request->username)->first();

        if ($franchisee && Hash::check($request->password, $franchisee->franchisee_pass)) {
            session([
                'franchisee_id' => $franchisee->id,
                'franchisee_name' => $franchisee->franchisee_name,
                'role' => 'franchisee',
            ]);
            return redirect()->route('franchisee.dashboard');
        }

        return back()->withErrors(['login_error' => 'Invalid username or password.']);
    }

    //Franchisor Staff
    public function showFranchisorStaffLogin()
    {
        return view('auth.login-franchisor-staff');
    }

    public function loginFranchisorStaff(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $staff = FranchisorStaff::where('staff_username', $request->username)->first();

        if ($staff && Hash::check($request->password, $staff->staff_pass)) {
            session([
                'staff_id' => $staff->id,
                'staff_name' => $staff->staff_name,
                'role' => 'franchisor-staff',
            ]);
            return redirect()->route('franchisorStaff.dashboard');
        }

        return back()->withErrors(['login_error' => 'Invalid username or password.']);
    }

    //Franchisee Staff
    public function showFranchiseeStaffLogin()
    {
        return view('auth.login-franchisee-staff');
    }

    public function loginFranchiseeStaff(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $staff = FranchiseeStaff::where('staff_username', $request->username)->first();

        if ($staff && Hash::check($request->password, $staff->staff_pass)) {
            session([
                'staff_id' => $staff->id,
                'staff_name' => $staff->staff_name,
                'role' => 'franchisee-staff',
            ]);
            return redirect()->route('franchiseeStaff.dashboard');
        }

        return back()->withErrors(['login_error' => 'Invalid username or password.']);
    }
}
