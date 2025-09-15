<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Franchisee extends Authenticatable
{
    protected $table = 'franchisees';
    protected $primaryKey = 'franchisee_id';
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'franchisee_name',
        'franchisee_contactNo',
        'franchisee_email',
        'franchisee_username',
        'franchisee_pass',
        'franchisee_address',
        'franchisee_status',
    ];

    protected $hidden = [
        'franchisee_pass',
    ];

    public function getRoleAttribute() {
        return 'franchisee';
    }

    public function getAuthPassword()
    {
        return $this->franchisee_pass;
    }
}
