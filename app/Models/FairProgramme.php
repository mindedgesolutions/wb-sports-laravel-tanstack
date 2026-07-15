<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FairProgramme extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'occurance',
        'description',
        'uuid',
        'added_by',
        'updated_by',
        'organisation',
        'cover_image',
        'is_active',
        'event_date',
        'gallery_type'
    ];

    public function images()
    {
        return $this->hasMany(FairProgrammGalleryImage::class, 'gallery_id');
    }
}
