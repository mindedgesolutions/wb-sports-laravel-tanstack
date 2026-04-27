<?php

namespace App\Imports;

use App\Models\SpSportsPersonnel;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class SportsPersonnelImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new SpSportsPersonnel([
            'sport'     => $row['sport'],
            'name'      => $row['name'],
            'slug'      => Str::slug($row['name']),
            'address'   => $row['address'] ?? null,
            'dob'       => $this->parseDate($row['dob'] ?? null),
            'contact_1' => $row['phone_1'] ?? null,
            'contact_2' => $row['phone_2'] ?? null,
            'added_by'  => 3,
        ]);
    }

    private function parseDate($value)
    {
        if (!$value) {
            return null;
        }

        if (is_string($value) && trim($value) === '') {
            return null;
        }

        // Excel empty date often appears as 0
        if ($value === 0 || $value === '0') {
            return null;
        }

        // If numeric → Excel date
        if (is_numeric($value)) {
            // Excel zero date safety check again
            if ($value < 1) {
                return null;
            }

            return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
        }

        // If string → parse normally
        try {
            return Date::parse($value)->format('Y-m-d');
        } catch (\Exception $ex) {
            return null; // fallback
        }
    }

    public function headingRow(): int
    {
        return 1;
    }
}
