<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpPhotoGallery extends Model
{
    protected $fillable = [
        'category',
        'title',
        'slug',
        'description',
        'cover_img',
        'event_date',
        'is_active',
    ];

    public function photos()
    {
        return $this->hasMany(SpPhoto::class, 'gallery_id');
    }
}
