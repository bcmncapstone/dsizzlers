<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class FranchisorStaff extends Authenticatable
{
    use Notifiable;

    protected $table = 'admin_staff'; 
    protected $primaryKey = 'astaff_id'; 

    public $timestamps = true;

    protected $fillable = [
        'admin_id', 'astaff_fname', 'astaff_lname', 'astaff_contactNo',
        'astaff_email', 'astaff_username', 'astaff_pass', 'astaff_status'
    ];

    protected $hidden = [
        'astaff_pass',
    ];

    public function getRoleAttribute() {
        return 'franchisor-staff';
    }

    public function getAuthPassword()
    {
        return $this->astaff_pass;
    }

    public function getEmailForPasswordReset(): string
    {
        return (string) $this->astaff_email;
    }
}