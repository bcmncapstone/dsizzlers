<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle($request, \Closure $next)
{
    if (!session()->has('admin_id')) {
        return redirect()->route('admin.login')->withErrors(['Please log in first.']);
    }

    return $next($request);
}

}
