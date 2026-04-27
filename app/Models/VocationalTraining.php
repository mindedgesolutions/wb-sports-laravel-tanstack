<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VocationalTraining extends Model
{
    protected $fillable = [
        'content',
        'slug',
        'show_order',
        'is_active',
    ];
}
