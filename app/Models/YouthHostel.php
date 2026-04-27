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
}
