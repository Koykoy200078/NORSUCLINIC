<?php

namespace Database\Seeders;

use App\Models\StaffDesignation;
use Illuminate\Database\Seeder;

class StaffDesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = [
            ['code' => 'nurse', 'name' => 'Nurse'],
            ['code' => 'clinic_admin', 'name' => 'Clinic Admin'],
            ['code' => 'secretary', 'name' => 'Secretary'],
            ['code' => 'pharmacy_assistant', 'name' => 'Pharmacy Assistant'],
            ['code' => 'triage_officer', 'name' => 'Triage Officer'],
        ];

        foreach ($designations as $designation) {
            StaffDesignation::updateOrCreate(['code' => $designation['code']], $designation);
        }
    }
}
