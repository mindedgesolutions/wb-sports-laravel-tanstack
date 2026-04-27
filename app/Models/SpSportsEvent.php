<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpSportsEvent extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'event_date',
        'is_active',
    ];
}
