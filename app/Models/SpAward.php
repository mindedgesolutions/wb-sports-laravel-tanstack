<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpAward extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'file_path',
        'is_active',
    ];
}
