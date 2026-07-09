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
        'event_date'
    ];

    public function gallery()
    {
        return $this->hasMany(FairProgrammeGallery::class, 'program_id')->orderBy('id', 'asc');
    }

    public function images()
    {
        return $this->hasMany(FairProgrammGalleryImage::class, 'gallery_id');
    }
}
