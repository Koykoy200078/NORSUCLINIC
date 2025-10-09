<?php

namespace Database\Seeders;

use App\Models\Vaccination;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VaccinationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vaccination = [
            [
                'vaccination_status' => 'Fully Vaccinated with Booster Shot',
            ],
            [
                'vaccination_status' => 'Fully Vaccinated',
            ],
            [
                'vaccination_status' => 'Partially Vaccinated',
            ],
            [
                'vaccination_status' => 'Not-Vaccinated',
            ],
            [
                'vaccination_status' => 'Unknown',
            ]
        ];

        Vaccination::insert($vaccination);
    }
}
