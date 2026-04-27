<?php

namespace App\Imports;

use App\Models\DistrictBlockOffice;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DistrictOfficeImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new DistrictBlockOffice([
            'district_id'     => $row['district'],
            'name'     => $row['office_name'],
            'slug'      => Str::slug($row['office_name']),
            'address'     => $row['address'] ?? null,
            'landline_no'  => $row['landline_no'] && $row['landline_no'] !== 'N.A.' ? $row['landline_no'] : null,
            'email'  => $row['email'] && $row['email'] !== 'N.A.' ? $row['email'] : null,
            'officer_designation' => $row['officer_name'] ?? null,
            'officer_mobile_no' => $row['officer_mobile_no'] && $row['officer_mobile_no'] !== 'N.A.' ? $row['officer_mobile_no'] : null,
            'organisation' => 'services',
            'added_by' => 3,
            'uuid' => Str::uuid(),
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}
