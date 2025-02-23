<?php

namespace Database\Seeders;

use App\Models\YearLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class YearLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year_level = [
            [
                'year_level_name' => '1st Year',
            ],
            [
                'year_level_name' => '2nd Year',
            ],
            [
                'year_level_name' => '3rd Year',
            ],
            [
                'year_level_name' => '4th Year',
            ],
            [
                'year_level_name' => '5th Year',
            ],
            [
                'year_level_name' => '6th Year',
            ],
        ];

        YearLevel::insert($year_level);
    }
}
