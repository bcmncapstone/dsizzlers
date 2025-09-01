<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Franchisee extends Model
{
    protected $table = 'franchisees';      // table name
    protected $primaryKey = 'franchisee_id'; // PK column
    public $timestamps = false;            // no created_at / updated_at

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
}