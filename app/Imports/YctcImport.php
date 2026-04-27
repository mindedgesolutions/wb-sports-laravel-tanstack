<?php

namespace App\Imports;

use App\Models\District;
use App\Models\YctcUploadTemp;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class YctcImport implements ToModel, WithHeadingRow
{
    private $districts;

    public function __construct()
    {
        $this->districts = District::select('id', 'name')->get();
    }

    public function model(array $row)
    {
        $district = $this->districts->where('name', 'like', '%' . trim($row['district_name']) . '%')->first();

        return new YctcUploadTemp([
            'district' => $district ? $district->id : null,
            'yctc_name' => trim($row['yctc_name']),
            'yctc_code' => trim($row['yctc_code']),
            'center_category' => trim($row['center_category']),
            'address_line_1' => trim($row['address_1']),
            'address_line_2' => trim($row['address_2']),
            'address_line_3' => trim($row['address_3']),
            'city' => trim($row['city']),
            'pincode' => trim($row['pincode']),
            'center_incharge_name' => trim($row['incharge_name']),
            'center_incharge_mobile' => trim($row['incharge_contact']),
            'center_incharge_email' => trim($row['incharge_email']),
            'center_owner_name' => trim($row['owner_name']),
            'center_owner_mobile' => trim($row['owner_contact']),
        ]);
    }
}
