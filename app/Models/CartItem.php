<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class CartItem extends Model
{
    protected $table = 'cart_items';
    protected $primaryKey = 'cart_item_id';

    protected $fillable = [
        'franchisee_id',
        'fstaff_id',
        'item_id',
        'quantity',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $cartItem) {
            $hasFranchisee = ! is_null($cartItem->franchisee_id);
            $hasStaff = ! is_null($cartItem->fstaff_id);

            if ($hasFranchisee === $hasStaff) {
                throw new InvalidArgumentException('Cart item must belong to exactly one owner: franchisee_id or fstaff_id.');
            }
        });
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }
}
