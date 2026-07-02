<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompSyllabus extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'file_path',
        'file_name',
        'organisation',
        'added_by',
        'is_active'
    ];
}
