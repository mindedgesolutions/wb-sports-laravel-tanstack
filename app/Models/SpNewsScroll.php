<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpNewsScroll extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'news_date',
        'file_name',
        'file_path',
        'is_active',
    ];
}
