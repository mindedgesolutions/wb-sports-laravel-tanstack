<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicesHomepageScroller extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'event_date',
        'type',
        'file_name',
        'file_path',
        'link',
        'is_active',
    ];
}
