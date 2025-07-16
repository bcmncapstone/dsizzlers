<?php

namespace App\Http;         

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /* … global & group arrays stay the same … */

    /**  Route / alias middleware  */
    protected $middlewareAliases = [       
        'auth'   => \App\Http\Middleware\Authenticate::class,
        'guest'  => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'role'   => \App\Http\Middleware\CheckUserRole::class,
        'admin.auth' => \App\Http\Middleware\AdminAuth::class,   
    ];
}
