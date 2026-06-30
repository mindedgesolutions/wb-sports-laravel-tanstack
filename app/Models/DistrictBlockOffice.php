<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistrictBlockOffice extends Model
{
    protected $fillable = [
        'district_id',
        'name',
        'slug',
        'address',
        'landline_no',
        'mobile_1',
        'mobile_2',
        'email',
        'officer_name',
        'officer_designation',
        'officer_mobile',
        'is_active',
        'organisation',
        'added_by',
        'updated_by'
    ];

    public function scopeSearchOffice(Object $query, ?String $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('district_block_offices.name', 'ilike', "%{$search}%")
                ->orWhere('district_block_offices.address', 'ilike', "%{$search}%")
                ->orWhere('district_block_offices.landline_no', 'ilike', "%{$search}%")
                ->orWhere('district_block_offices.email', 'ilike', "%{$search}%")
                ->orWhere('district_block_offices.officer_name', 'ilike', "%{$search}%")
                ->orWhere('district_block_offices.officer_designation', 'ilike', "%{$search}%")
                ->orWhere('district_block_offices.officer_mobile', 'ilike', "%{$search}%")
                ->orWhere('districts.name', 'ilike', "%{$search}%");
        });
    }
}
