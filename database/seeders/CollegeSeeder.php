<?php

namespace Database\Seeders;

use App\Models\College;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CollegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $college = [
            [
                'college_name' => 'College of Arts and Sciences (CAS)',
            ],
            [
                'college_name' => 'College of Business and Accountancy (CBA)',
            ],
            [
                'college_name' => 'College of Information Technology (CIT)',
            ],
            [
                'college_name' => 'College of Teacher Education (CTED)',
            ],
            [
                'college_name' => 'College of Nursing, Pharmacy, and Allied Health Sciences (CNPAHS)',
            ],
            [
                'college_name' => 'College of Agriculture, Food and Forestry (CAFF)',
            ],
            [
                'college_name' => 'College of Criminal Justice Education (CCJE)',
            ],
            [
                'college_name' => 'College of Engineering and Architecture (CEA)',
            ],
        ];

        College::insert($college);
    }
}
