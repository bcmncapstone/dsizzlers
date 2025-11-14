<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id'; // important since you used order_id in migration

    protected $fillable = [
        'franchisee_id',
        'fstaff_id',
        'order_date',
        'order_status',
        'total_amount',
        'name',
        'contact',
        'address',
        'payment_receipt',
        'payment_status',
        'delivery_status',
    ];

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }

    // relationships if needed
    public function franchisee()
    {
        return $this->belongsTo(Franchisee::class, 'franchisee_id');
    }

    public function franchiseeStaff()
    {
        return $this->belongsTo(FranchiseeStaff::class, 'fstaff_id');
    }
}