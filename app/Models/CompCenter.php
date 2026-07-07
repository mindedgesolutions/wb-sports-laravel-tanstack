<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompCenter extends Model
{
    protected $fillable = ['district_id', 'yctc_name', 'yctc_code', 'center_category', 'address_line_1', 'address_line_2', 'address_line_3', 'city', 'pincode', 'center_incharge_name', 'center_incharge_mobile', 'center_incharge_email', 'center_owner_name', 'center_owner_mobile', 'is_active', 'added_by', 'slug'];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function scopeSearchCentre(Object $query, ?String $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('districts.name', 'ilike', "%{$search}%")
                ->orWhere('comp_centers.center_category', 'ilike', "%{$search}%")
                ->orWhere('comp_centers.yctc_name', 'ilike', "%{$search}%")
                ->orWhere('comp_centers.yctc_code', 'ilike', "%{$search}%")
                ->orWhere('comp_centers.address_line_1', 'ilike', "%{$search}%")
                ->orWhere('comp_centers.address_line_2', 'ilike', "%{$search}%")
                ->orWhere('comp_centers.address_line_3', 'ilike', "%{$search}%")
                ->orWhere('comp_centers.city', 'ilike', "%{$search}%")
                ->orWhere('comp_centers.pincode', 'ilike', "%{$search}%")
                ->orWhere('comp_centers.center_incharge_name', 'ilike', "%{$search}%")
                ->orWhere('comp_centers.center_incharge_mobile', 'ilike', "%{$search}%")
                ->orWhere('comp_centers.center_incharge_email', 'ilike', "%{$search}%")
                ->orWhere('comp_centers.center_owner_name', 'ilike', "%{$search}%")
                ->orWhere('comp_centers.center_owner_mobile', 'ilike', "%{$search}%");
        });
    }
}
