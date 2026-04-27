<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpSportsPersonnel extends Model
{
    protected $fillable = [
        'sport',
        'name',
        'slug',
        'address',
        'dob',
        'contact_1',
        'contact_2',
        'is_active',
        'show_order',
        'added_by',
        'updated_by'
    ];
}
