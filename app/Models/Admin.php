<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
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
}
