<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Example Gate (optional for role check)
        Gate::define('manage-users', function ($user) {
            return $user->role === 'franchisor';
            if (!Gate::allows('create-users')) {
    abort(403);
}
        });
    }
}
