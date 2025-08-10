<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['item_name', 'item_description', 'price', 'stock_quantity', 'item_category'];

    public $timestamps = false;

    protected $primaryKey = 'item_id';  
}
