<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpPhoto extends Model
{
    protected $fillable = [
        'gallery_id',
        'image_path',
    ];

    public function gallery()
    {
        return $this->belongsTo(SpPhotoGallery::class, 'gallery_id');
    }
}
