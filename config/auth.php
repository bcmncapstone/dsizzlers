<?php

return [

  'defaults' => [
    'guard' => 'web',
    'passwords' => 'users',
],

'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'admin' => [
        'driver' => 'session',
        'provider' => 'admins',
    ],
    'franchisee' => [
        'driver' => 'session',
        'provider' => 'franchisees',
    ],
    'franchisee_staff' => [
        'driver' => 'session',
        'provider' => 'franchisee_staff',
    ],
    'franchisor_staff' => [
        'driver' => 'session',
        'provider' => 'franchisor_staff',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
    'admins' => [
        'driver' => 'eloquent',
        'model' => App\Models\Admin::class,
    ],
    'franchisees' => [
        'driver' => 'eloquent',
        'model' => App\Models\Franchisee::class,
    ],
    'franchisee_staff' => [
        'driver' => 'eloquent',
        'model' => App\Models\FranchiseeStaff::class,
    ],
    'franchisor_staff' => [
        'driver' => 'eloquent',
        'model' => App\Models\FranchisorStaff::class,
    ],
],


    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];