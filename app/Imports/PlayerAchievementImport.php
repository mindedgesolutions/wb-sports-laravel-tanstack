<?php

namespace App\Imports;

use App\Models\SpPlayersAchievement;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PlayerAchievementImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new SpPlayersAchievement([
            'sport'     => $row['sport'],
            'name'     => $row['name'],
            'slug'      => Str::slug($row['name']),
            'description'  => $row['description'] ?? '',
            'achievement_date'  => $this->parseDate($row['achievement_date'] ?? null),
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
