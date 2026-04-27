<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YctcUploadTemp extends Model
{
    protected $fillable = [
        'district',
        'yctc_name',
        'yctc_code',
        'center_category',
        'address_line_1',
        'address_line_2',
        'address_line_3',
        'city',
        'pincode',
        'center_incharge_name',
        'center_incharge_mobile',
        'center_incharge_email',
        'center_owner_name',
        'center_owner_mobile'
    ];
}
