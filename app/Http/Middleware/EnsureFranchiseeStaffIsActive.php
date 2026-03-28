<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureFranchiseeStaffIsActive
{
    /**
     * Ensure franchisee staff is still active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $staff = Auth::guard('franchisee_staff')->user();

        if ($staff && $staff->fstaff_status !== 'Active') {
            Auth::guard('franchisee_staff')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login.franchiseeStaff')
                ->withErrors(['login_error' => 'Your staff account has been archived. Please contact your franchisee admin.']);
        }

        return $next($request);
    }
}
