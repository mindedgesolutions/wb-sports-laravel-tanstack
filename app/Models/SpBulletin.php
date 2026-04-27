<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpBulletin extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'file_path',
        'event_date',
        'is_active',
    ];
}
