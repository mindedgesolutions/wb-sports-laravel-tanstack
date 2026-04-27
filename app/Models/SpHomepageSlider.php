<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpHomepageSlider extends Model
{
    protected $fillable = [
        'image_path',
        'is_active',
        'show_order',
    ];
}
