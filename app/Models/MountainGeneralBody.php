<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MountainGeneralBody extends Model
{
    protected $fillable = [
        'designation',
        'name',
        'description',
        'organisation',
        'added_by',
        'slug',
    ];
}
