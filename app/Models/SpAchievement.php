<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpAchievement extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'achievement_date',
        'show_order',
        'is_active',
    ];
}
