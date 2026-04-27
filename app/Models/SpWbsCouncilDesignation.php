<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpWbsCouncilDesignation extends Model
{
    protected $fillable = [
        'type',
        'designation',
        'slug',
        'weight',
        'is_active',
    ];

    public function advisoryCommittees()
    {
        return $this->hasMany(SpAdvisoryCommittee::class, 'designation_id');
    }
}
