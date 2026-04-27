<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpFifa extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'event_date',
        'is_active',
    ];

    public function photos()
    {
        return $this->hasMany(SpFifaPhoto::class, 'fifa_id');
    }

    public function firstPhoto()
    {
        return $this->hasOne(SpFifaPhoto::class, 'fifa_id')->orderBy('id', 'asc');
    }
}
