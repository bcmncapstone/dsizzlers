<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('franchisor_id')) {
            return $next($request);
        }

        if (session()->has('franchisee_id')) {
            return $next($request);
        }

        if (session()->has('franchisor_staff_id')) {
            return $next($request);
        }

        if (session()->has('franchisee_staff_id')) {
            return $next($request);
        }

        // Route-specific redirection based on URL prefix
        if ($request->is('franchisor/*')) {
            return redirect()->route('admin.login'); // adjust if you want a separate franchisor login
        }

        if ($request->is('franchisee/*')) {
            return redirect()->route('login.franchisee');
        }

        if ($request->is('franchisor-staff/*')) {
            return redirect()->route('login.franchisorStaff');
        }

        if ($request->is('franchisee-staff/*')) {
            return redirect()->route('login.franchiseeStaff');
        }

        return redirect()->route('login'); // default to admin login
    }
}
