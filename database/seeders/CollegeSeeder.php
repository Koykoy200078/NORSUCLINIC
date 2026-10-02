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
                'college_name' => 'College of Business Administration (CBA)',
            ],
            [
                'college_name' => 'College of Engineering and Architecture (CEA)',
            ],
            [
                'college_name' => 'College of Nursing, Pharmacy, and Allied Health Sciences (CNPAHS)',
            ],
            [
                'college_name' => 'College of Tourism and Hospitality Management (CTHM)',
            ],
            [
                'college_name' => 'College of Agriculture, Forestry, and Fishery (CAFF)',
            ],
            [
                'college_name' => 'College of Criminal Justice Education (CCJE)',
            ],
            [
                'college_name' => 'College of Industrial Technology (CIT)',
            ],
            [
                'college_name' => 'College of Teacher Education (CTED)',
            ],
            [
                'college_name' => 'College of Law (CL)',
            ],
            [
                'college_name' => 'Graduate School (GS)',
            ],
        ];

        // Insert only the rows that are missing (matched by name) so the seeder can be re-run safely.
        foreach ($college as $row) {
            College::firstOrCreate(['college_name' => $row['college_name']], $row);
        }
    }
}
