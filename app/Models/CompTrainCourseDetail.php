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

    public function scopeSearchCourse(Object $query, ?String $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('course_type', 'ilike', "%{$search}%")
                ->orWhere('course_name', 'ilike', "%{$search}%")
                ->orWhere('course_duration', 'ilike', "%{$search}%")
                ->orWhere('course_eligibility', 'ilike', "%{$search}%")
                ->orWhere('course_fees', 'ilike', "%{$search}%");
        });
    }
}
