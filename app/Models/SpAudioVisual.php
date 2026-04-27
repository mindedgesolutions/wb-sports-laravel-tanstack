<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpAudioVisual extends Model
{
    protected $fillable = [
        'title',
        'video_link',
        'is_active',
    ];
}
