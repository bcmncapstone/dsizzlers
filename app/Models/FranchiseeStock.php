<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FranchiseeStock extends Model
{
    protected $table = 'franchisee_stock';
    protected $primaryKey = 'stock_id';

    protected $fillable = [
        'franchisee_id',
        'item_id',
        'current_quantity',
        'minimum_quantity',
    ];

    protected $casts = [
        'current_quantity' => 'integer',
        'minimum_quantity' => 'integer',
    ];

    /**
     * Get the franchisee that owns this stock
     */
    public function franchisee(): BelongsTo
    {
        return $this->belongsTo(Franchisee::class, 'franchisee_id', 'franchisee_id');
    }

    /**
     * Get the item for this stock
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    /**
     * Get stock status (in_stock, low_stock, out_of_stock)
     */
    public function getStatusAttribute(): string
    {
        if ($this->current_quantity <= 0) {
            return 'out_of_stock';
        } elseif ($this->current_quantity <= $this->minimum_quantity) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    /**
     * Check if stock is low
     */
    public function isLowStock(): bool
    {
        return $this->current_quantity > 0 && $this->current_quantity <= $this->minimum_quantity;
    }

    /**
     * Check if out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->current_quantity <= 0;
    }
}
