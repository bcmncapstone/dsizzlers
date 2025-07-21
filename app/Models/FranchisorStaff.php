<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FranchisorStaff extends Model
{
    protected $table = 'franchisor_staff'; 
    protected $primaryKey = 'id';
    public $timestamps = false;

  protected $fillable = [
    'franchisor_staff_name',
    'franchisor_staff_username',
    'franchisor_staff_pass',
];
}
