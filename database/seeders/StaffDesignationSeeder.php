<?php

namespace Database\Seeders;

use App\Models\StaffDesignation;
use Illuminate\Database\Seeder;

class StaffDesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = [
            ['code' => 'clinic_head', 'name' => 'Head of University Health Services'],
            ['code' => 'nurse', 'name' => 'Registered Nurse'],
            ['code' => 'pharmacist', 'name' => 'Pharmacist'], // Aligns with NORSU BS Pharmacy grads
            ['code' => 'triage_officer', 'name' => 'Triage Officer'],
            ['code' => 'clinic_staff', 'name' => 'Clinic Staff / Secretary'], // As listed in NORSU directory
            ['code' => 'records_officer', 'name' => 'Medical Records Officer'],
        ];

        foreach ($designations as $designation) {
            StaffDesignation::updateOrCreate(['code' => $designation['code']], $designation);
        }
    }
}
