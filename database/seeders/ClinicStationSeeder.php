<?php

namespace Database\Seeders;

use App\Models\ClinicStation;
use Illuminate\Database\Seeder;

class ClinicStationSeeder extends Seeder
{
    public function run(): void
    {
        $stations = [
            ['name' => 'Front Desk', 'description' => 'Patient reception and scheduling area'],
            ['name' => 'Triage Area', 'description' => 'Initial patient assessment and vitals'],
            ['name' => 'Consultation Room', 'description' => 'Doctor consultation area'],
            ['name' => 'Pharmacy', 'description' => 'Medicine dispensing and medication counseling'],
            ['name' => 'Records Area', 'description' => 'Medical records and encoding area'],
        ];

        foreach ($stations as $station) {
            ClinicStation::updateOrCreate(['name' => $station['name']], $station);
        }
    }
}
