<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FairProgrammeGallery extends Model
{
    protected $fillable = [
        'program_id',
        'title',
        'slug',
        'programme_date',
        'description',
        'cover_image',
        'organisation',
        'added_by',
        'updated_by',
        'show_in_gallery',
    ];

    public function images()
    {
        return $this->hasMany(FairProgrammGalleryImage::class, 'gallery_id', 'id');
    }
}
