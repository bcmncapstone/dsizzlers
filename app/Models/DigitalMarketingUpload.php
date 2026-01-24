<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalMarketingUpload extends Model
{
    protected $fillable = ['uploaded_by', 'image_path', 'description'];
}

