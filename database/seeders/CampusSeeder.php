<?php

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CampusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campus = [
            [
                'campus_name' => 'Main Campus 1',
            ],
            [
                'campus_name' => 'Main Campus 2',
            ],
        ];

        Campus::insert($campus);
    }
}
