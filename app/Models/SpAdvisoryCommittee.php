<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpAdvisoryCommittee extends Model
{
    protected $fillable = [
        'designation_id',
        'name',
        'slug',
        'designation_label',
        'address',
        'phone',
        'email',
        'fax',
        'is_active',
    ];

    public function designation()
    {
        return $this->belongsTo(SpWbsCouncilDesignation::class, 'designation_id');
    }
}
