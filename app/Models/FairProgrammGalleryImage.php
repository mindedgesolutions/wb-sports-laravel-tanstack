<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FairProgrammGalleryImage extends Model
{
    protected $fillable = [
        'gallery_id',
        'image_path',
    ];

    public function gallery()
    {
        return $this->belongsTo(FairProgrammeGallery::class, 'gallery_id', 'id');
    }
}
