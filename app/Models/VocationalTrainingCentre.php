<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VocationalTrainingCentre extends Model
{
    protected $fillable = [
        'district_id',
        'name_of_centre',
        'slug',
        'address',
        'phone',
        'is_active',
    ];
}
