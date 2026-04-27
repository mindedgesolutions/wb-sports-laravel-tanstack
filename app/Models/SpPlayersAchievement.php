<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpPlayersAchievement extends Model
{
    protected $fillable = [
        'sport',
        'name',
        'slug',
        'description',
        'achievement_date',
        'is_active',
    ];
}
