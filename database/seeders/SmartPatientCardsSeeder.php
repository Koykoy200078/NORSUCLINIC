<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SmartPatientCards;

class SmartPatientCardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $input = [
            'template_name' => 'demo temp',
            'address' => 'Guihulngan City, Negros Oriental',
            'header_color' => '#007fff',
            'show_email' => 1,
            'show_phone' => 1,
            'show_dob' => 1,
            'show_blood_group' => 1,
            'show_address' => 1,
            'show_patient_unique_id' => 1,
        ];

        SmartPatientCards::create($input);
    }
}
