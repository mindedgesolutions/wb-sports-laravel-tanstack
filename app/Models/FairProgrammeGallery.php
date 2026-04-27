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

    public function latestImage()
    {
        return $this->hasOne(FairProgrammGalleryImage::class, 'program_id')->latest();
    }

    public function programme()
    {
        return $this->belongsTo(FairProgramme::class, 'program_id', 'id');
    }

    public function cover()
    {
        return $this->hasOne(FairProgrammGalleryImage::class, 'gallery_id', 'id')->orderBy('id');
    }
}
