<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpKeyPersonnel extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'rank',
        'designation',
        'image_path',
        'department',
        'govt',
        'is_active',
        'added_by',
        'updated_by',
        'show_order'
    ];
}
