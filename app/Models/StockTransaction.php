<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends Model
{
    protected $table = 'stock_transactions';
    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'franchisee_id',
        'item_id',
        'transaction_type',
        'quantity',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'performed_by_type',
        'performed_by_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'balance_after' => 'integer',
    ];

    /**
     * Get the franchisee for this transaction
     */
    public function franchisee(): BelongsTo
    {
        return $this->belongsTo(Franchisee::class, 'franchisee_id', 'franchisee_id');
    }

    /**
     * Get the item for this transaction
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }
}
