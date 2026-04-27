<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpStadiumImage extends Model
{
    protected $fillable = [
        'stadium_id',
        'image_path',
    ];

    public function stadium()
    {
        return $this->belongsTo(SpStadium::class, 'stadium_id');
    }
}
