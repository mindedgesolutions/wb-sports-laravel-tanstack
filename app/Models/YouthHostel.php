<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YouthHostel extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'address',
        'phone_1',
        'phone_2',
        'email',
        'accommodation',
        'how_to_reach',
        'railway_station',
        'bus_stop',
        'airport',
        'road_network',
        'remarks',
        'district_id',
        'hostel_img',
        'added_by',
        'updated_by',
        'is_active',
        'uuid'
    ];

    public function scopeSearchHostel(Object $query, ?String $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('districts.name', 'ilike', "%{$search}%")
                ->orWhere('youth_hostels.name', 'ilike', "%{$search}%")
                ->orWhere('youth_hostels.address', 'ilike', "%{$search}%")
                ->orWhere('youth_hostels.phone_1', 'ilike', "%{$search}%")
                ->orWhere('youth_hostels.phone_2', 'ilike', "%{$search}%")
                ->orWhere('youth_hostels.email', 'ilike', "%{$search}%")
                ->orWhere('youth_hostels.accommodation', 'ilike', "%{$search}%")
                ->orWhere('youth_hostels.how_to_reach', 'ilike', "%{$search}%")
                ->orWhere('youth_hostels.railway_station', 'ilike', "%{$search}%")
                ->orWhere('youth_hostels.bus_stop', 'ilike', "%{$search}%")
                ->orWhere('youth_hostels.airport', 'ilike', "%{$search}%")
                ->orWhere('youth_hostels.road_network', 'ilike', "%{$search}%");
        });
    }
}
