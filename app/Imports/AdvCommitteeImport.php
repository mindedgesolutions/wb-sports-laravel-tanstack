<?php

namespace App\Imports;

use App\Models\SpAdvisoryCommittee;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AdvCommitteeImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new SpAdvisoryCommittee([
            'designation_id'       => $row['designation'],
            'name'     => $row['name'],
            'slug'      => Str::slug($row['name']),
            'designation_label' => $row['designation_label'] ?? null,
            'address' => $row['address'] ?? null,
            'phone' => $row['phone'] ?? null,
            'fax' => $row['fax'] ?? null,
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}
