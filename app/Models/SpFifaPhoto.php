<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpFifaPhoto extends Model
{
    protected $fillable = [
        'fifa_id',
        'image_path',
    ];

    public function fifa()
    {
        return $this->belongsTo(SpFifa::class, 'fifa_id');
    }
}
