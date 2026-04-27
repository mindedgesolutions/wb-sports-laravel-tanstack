<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsEvent extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'file_path',
        'is_active',
        'event_date',
        'type',
    ];
}
