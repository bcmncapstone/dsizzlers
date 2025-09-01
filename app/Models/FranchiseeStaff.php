<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FranchiseeStaff extends Model
{
    protected $table = 'franchisee_staff'; // table name
    protected $primaryKey = 'franchisee_id';   // PK column
    public $timestamps = false;

    protected $fillable = [
        'franchisee_id',
        'fstaff_fname',
        'fstaff_lname',
        'fstaff_contactNo',
        'fstaff_username',
        'fstaff_pass',
        'fstaff_status',
    ];
}