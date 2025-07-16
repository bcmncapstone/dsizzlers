<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminStaff extends Model
{
    protected $table = 'admin_staff';     // table name
    protected $primaryKey = 'astaff_id';  // PK column
    public $timestamps = false;

    protected $fillable = [
        'staffAdmin_id',
        'astaff_fname',
        'astaff_lname',
        'astaff_contactNo',
        'astaff_username',
        'astaff_pass',
        'astaff_status',
    ];
}
