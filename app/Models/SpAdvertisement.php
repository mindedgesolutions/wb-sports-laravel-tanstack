<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpAdvertisement extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'file_path',
        'ad_date',
        'is_active',
    ];
}
