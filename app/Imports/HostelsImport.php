<?php

namespace App\Imports;

use App\Models\YouthHostel;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class HostelsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new YouthHostel([
            'name'     => $row['name'],
            'slug'      => Str::slug($row['name']),
            'address'     => $row['address'],
            'phone_1'  => $row['phone_1'] ?? null,
            'phone_2'  => $row['phone_2'] ?? null,
            'accommodation' => $row['accommodation_type'] ?? null,
            'how_to_reach' => $row['how_to_reach'] ?? null,
            'railway_station' => $row['railway_station'] ?? null,
            'bus_stop' => $row['bus_stop'] ?? null,
            'airport' => $row['airport'] ?? null,
            'road_network' => $row['transportation_network'] ?? null,
            'remarks' => $row['remarks'] ?? null,
            'district_id' => $row['district'],
            'added_by' => 3,
            'uuid' => Str::uuid(),
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}
