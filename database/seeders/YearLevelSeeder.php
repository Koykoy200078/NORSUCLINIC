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
            // Student year levels (1-6)
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
            // Faculty (7)
            [
                'year_level_name' => 'Faculty',
            ],
            // Staff (8)
            [
                'year_level_name' => 'Staff',
            ],
            // Guest (9)
            [
                'year_level_name' => 'Guest',
            ],
        ];

        // Insert only the rows that are missing (matched by name) so the seeder can be re-run safely.
        foreach ($year_level as $row) {
            YearLevel::firstOrCreate(['year_level_name' => $row['year_level_name']], $row);
        }
    }
}
