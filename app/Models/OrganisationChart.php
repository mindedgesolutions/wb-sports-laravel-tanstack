<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationChart extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'designation',
        'image_path',
        'message',
        'show_order',
        'is_active',
        'added_by',
        'updated_by',
        'rank',
        'department'
    ];
}
