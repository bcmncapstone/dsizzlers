<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Franchisor extends Authenticatable
{
    use Notifiable;

    protected $guard = 'franchisor';

    protected $fillable = [
        'name', 'email', 'password', // add other fields as needed
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}
