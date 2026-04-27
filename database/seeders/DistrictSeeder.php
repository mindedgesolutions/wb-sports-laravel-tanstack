<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        District::create(['name' => 'Alipurduar']);
        District::create(['name' => 'Bankura']);
        District::create(['name' => 'Birbhum']);
        District::create(['name' => 'CoochBihar']);
        District::create(['name' => 'Dakshin 24 Parganas']);
        District::create(['name' => 'Dakshin Dinajpur']);
        District::create(['name' => 'Darjeeling']);
        District::create(['name' => 'Hooghly']);
        District::create(['name' => 'Howrah']);
        District::create(['name' => 'Jalpaiguri']);
        District::create(['name' => 'Jhargram']);
        District::create(['name' => 'Kalimpong']);
        District::create(['name' => 'Kolkata']);
        District::create(['name' => 'Malda']);
        District::create(['name' => 'Murshidabad']);
        District::create(['name' => 'Nadia']);
        District::create(['name' => 'Paschim Burdwan']);
        District::create(['name' => 'Purba Burdwan']);
        District::create(['name' => 'Paschim Medinipur']);
        District::create(['name' => 'Purba Medinipur']);
        District::create(['name' => 'Purulia']);
        District::create(['name' => 'Uttar 24 Parganas']);
        District::create(['name' => 'Uttar Dinajpur']);
    }
}
