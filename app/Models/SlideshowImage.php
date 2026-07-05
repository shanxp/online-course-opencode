<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlideshowImage extends Model
{
    protected $fillable = [
        'image_path',
        'sort_order',
        'is_active',
    ];
}
