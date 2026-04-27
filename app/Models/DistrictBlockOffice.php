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
}
