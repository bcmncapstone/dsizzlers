<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    use HasFactory;

    protected $table = 'stock_in';
    protected $primaryKey = 'stock_in_id';

    protected $fillable = [
        'item_id',
        'quantity_received',
        'received_date',
        'supplier_name',
        'restocked_by',
    ];

    protected $casts = [
        'received_date' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }
}
