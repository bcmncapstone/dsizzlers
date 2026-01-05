<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['item_name', 'item_description', 'price', 'stock_quantity', 'item_category', 'item_image'];

    public $timestamps = false;

    protected $primaryKey = 'item_id';

    public function getItemImagesAttribute()
    {
        $data = json_decode($this->item_image, true);
        if (is_array($data)) {
            return array_map(function($path) {
                return strpos($path, 'item_images/') === 0 ? $path : 'item_images/' . $path;
            }, $data);
        } elseif (is_string($this->item_image) && !empty($this->item_image)) {
            $path = $this->item_image;
            return [strpos($path, 'item_images/') === 0 ? $path : 'item_images/' . $path];
        } else {
            return [];
        }
    }
}