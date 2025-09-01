<?php

// app/Models/FranchisorStaff.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FranchisorStaff extends Model
{
    protected $table = 'admin_staff'; 
    protected $primaryKey = 'astaff_id'; //  table's primary key

    public $timestamps = false; // If your table does not use created_at / updated_at

    protected $fillable = [
        'astaff_fname', 'astaff_lname', 'astaff_contactNo',
        'astaff_username', 'astaff_pass', 'astaff_status'
    ];
}