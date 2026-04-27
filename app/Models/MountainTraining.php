<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MountainTraining extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'courses_count',
        'duration',
        'age_group_start',
        'age_group_end',
        'course_fee',
        'file_path',
        'file_name',
        'is_active',
    ];
}
