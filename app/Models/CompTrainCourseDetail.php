<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompTrainCourseDetail extends Model
{
    protected $fillable = [
        'course_type',
        'course_name',
        'course_slug',
        'course_duration',
        'course_eligibility',
        'course_fees',
        'organisation',
        'is_active',
    ];
}
