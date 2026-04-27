<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpStadium extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'address',
        'location',
        'cover_img',
        'is_active',
    ];

    public function stadiumDetails()
    {
        return $this->hasOne(SpStadiumDetail::class, 'stadium_id', 'id');
    }

    // --------------------------------------

    public function stadiumHighlights()
    {
        return $this->hasMany(SpStadiumHighlight::class, 'stadium_id', 'id');
    }

    // --------------------------------------

    public function images()
    {
        return $this->hasMany(SpStadiumImage::class, 'stadium_id', 'id');
    }
}
