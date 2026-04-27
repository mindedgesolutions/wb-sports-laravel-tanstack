<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpStadiumDetail extends Model
{
    protected $fillable = [
        'stadium_id',
        'description',
    ];

    public function stadium()
    {
        return $this->belongsTo(SpStadium::class, 'stadium_id');
    }
}
