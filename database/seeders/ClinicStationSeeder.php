<?php

namespace Database\Seeders;

use App\Models\ClinicStation;
use Illuminate\Database\Seeder;

class ClinicStationSeeder extends Seeder
{
    public function run(): void
    {
        $stations = [
            [
                'code' => 'front_desk', 
                'name' => 'Front Desk & Receiving', 
                'description' => 'Patient reception, queueing, and NORSU ID verification.'
            ],
            [
                'code' => 'triage_area', 
                'name' => 'Triage Area', 
                'description' => 'Initial patient assessment, vitals checking (BP, Temp), and basic history taking.'
            ],
            [
                'code' => 'medical_consultation', 
                'name' => 'Medical Consultation Room', 
                'description' => 'Private physician consultation and physical examination area.'
            ],
            [
                'code' => 'pharmacy', 
                'name' => 'Clinic Pharmacy', 
                'description' => 'Medicine dispensing, medication counseling, and FEFO inventory management.'
            ],
            [
                'code' => 'records_area', 
                'name' => 'Records Area', 
                'description' => 'Secure filing of physical medical records and digital data encoding.'
            ],
            [
                'code' => 'observation_room', 
                'name' => 'Observation & Recovery Room', 
                'description' => 'Rest area with beds for students resting from dysmenorrhea, dizziness, or minor PE injuries.'
            ],
            [
                'code' => 'isolation_room', 
                'name' => 'Isolation Room', 
                'description' => 'Holding area for patients with suspected contagious illnesses (e.g., flu, chickenpox) pending campus exit.'
            ],
        ];

        foreach ($stations as $station) {
            ClinicStation::updateOrCreate(['code' => $station['code']], $station);
        }
    }
}
