<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MultiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guards = ['admin', 'franchisor_staff', 'franchisee', 'franchisee_staff'];

        foreach ($guards as $guard) {
            if (auth($guard)->check()) {
                if ($guard === 'franchisee_staff') {
                    $staff = Auth::guard('franchisee_staff')->user();
                    if ($staff && $staff->fstaff_status !== 'Active') {
                        Auth::guard('franchisee_staff')->logout();
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();

                        return redirect()->route('login.franchiseeStaff')
                            ->withErrors(['login_error' => 'Your account has been archived. Please contact your franchisee admin.']);
                    }
                }

                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return redirect('/login');
    }
}
