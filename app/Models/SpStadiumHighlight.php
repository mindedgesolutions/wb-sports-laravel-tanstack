<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpStadiumHighlight extends Model
{
    protected $fillable = [
        'stadium_id',
        'title',
        'is_active',
    ];

    public function stadium()
    {
        return $this->belongsTo(SpStadium::class, 'stadium_id');
    }
}
