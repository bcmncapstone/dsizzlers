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
        $cloudName = config('services.cloudinary.cloud_name');
        $useCloudinary = !empty($cloudName)
            && config('services.cloudinary.api_key')
            && config('services.cloudinary.api_secret');

        $resolve = function (string $path) use ($cloudName, $useCloudinary): string {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path; // already a full URL (legacy data)
            }
            if ($useCloudinary) {
                $publicId = strpos($path, 'item_images/') === 0 ? $path : 'item_images/' . $path;
                return "https://res.cloudinary.com/{$cloudName}/image/upload/{$publicId}";
            }
            return strpos($path, 'item_images/') === 0 ? $path : 'item_images/' . $path;
        };

        $data = json_decode($this->item_image, true);
        if (is_array($data)) {
            return array_map($resolve, $data);
        } elseif (is_string($this->item_image) && !empty($this->item_image)) {
            return [$resolve($this->item_image)];
        } else {
            return [];
        }
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class, 'item_id', 'item_id');
    }
}
