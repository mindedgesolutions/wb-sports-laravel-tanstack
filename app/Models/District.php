<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = ['name', 'district_code', 'is_active'];

    public function districtOffices()
    {
        return $this->hasMany(DistrictBlockOffice::class, 'district_id', 'id');
    }

    public function compCenters()
    {
        return $this->hasMany(CompCenter::class, 'district_id', 'id');
    }
}
