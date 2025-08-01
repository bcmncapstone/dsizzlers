<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'branches';
    protected $primaryKey = 'branch_id';

       protected $fillable = [
        'location',
        'first_name',
        'last_name',
        'email',
        'contact_number',
        'branch_status',
        'contract_file',
        'contract_expiration',
        'archived'
    ];
}
