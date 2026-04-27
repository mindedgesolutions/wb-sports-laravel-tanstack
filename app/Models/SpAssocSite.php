<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpAssocSite extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'url',
        'last_updated',
        'is_active',
    ];
}
