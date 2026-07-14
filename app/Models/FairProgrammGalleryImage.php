<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FairProgrammGalleryImage extends Model
{
    protected $fillable = [
        'gallery_id',
        'image_path',
    ];
}
