<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class FranchiseeStaff extends Authenticatable
{
    use Notifiable;

    protected $table = 'franchisee_staff';
    protected $primaryKey = 'fstaff_id';
    public $timestamps = true;

    protected $fillable = [
        'franchisee_id',
        'fstaff_fname',
        'fstaff_lname',
        'fstaff_contactNo',
        'fstaff_email',
        'fstaff_username',
        'fstaff_pass',
        'fstaff_status',
    ];

    protected $hidden = [
        'fstaff_pass',
    ];

    public function getRoleAttribute() {
        return 'franchisee-staff';
    }

    public function getAuthPassword()
    {
        return $this->fstaff_pass;
    }

    public function getEmailForPasswordReset(): string
    {
        return (string) $this->fstaff_email;
    }
}
