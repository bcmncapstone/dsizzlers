<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    // Specify table name
    protected $table = 'admins';

    // Specify primary key
    protected $primaryKey = 'admin_id';

    // Disable timestamps (no created_at or updated_at)
    public $timestamps = false;

    // Fillable fields for mass assignment
    protected $fillable = [
        'admin_fname',
        'admin_lname',
        'admin_contactNo',
        'admin_email',
        'admin_username',
        'admin_pass',
        'admin_status',
    ];
     protected $hidden = [
        'admin_pass',
    ];

    public function getAuthPassword()
    {
        return $this->admin_pass; 
    }

    public function getEmailForPasswordReset(): string
    {
        return (string) $this->admin_email;
    }
}