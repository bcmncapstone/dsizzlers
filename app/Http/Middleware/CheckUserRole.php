<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Debug log
        if (Auth::check()) {
            \Log::info('CheckUserRole: Logged in user', [
                'id' => Auth::id(),
                'role_from_db' => Auth::user()->role,
                'expected_role' => $role,
            ]);
        } else {
            \Log::info('CheckUserRole: No user logged in');
        }

        if (!Auth::check() || Auth::user()->role !== $role) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
