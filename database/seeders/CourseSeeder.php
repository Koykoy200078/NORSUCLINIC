<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course = [
            // CAS
            [
                'course_name' => 'Bachelor of Science in Biology',
            ],
            [
                'course_name' => 'Bachelor of Science in Chemistry',
            ],
            [
                'course_name' => 'Bachelor of Science in Computer Science',
            ],
            [
                'course_name' => 'Bachelor of Science in Geology',
            ],
            [
                'course_name' => 'Bachelor of Science in Information Technology',
            ],
            [
                'course_name' => 'Bachelor of Mass Communication',
            ],
            [
                'course_name' => 'Bachelor of Science in Mathematics',
            ],
            [
                'course_name' => 'Bachelor of Science in Psychology',
            ],
            // CBA
            [
                'course_name' => 'Bachelor of Science in Accountancy',
            ],
            [
                'course_name' => 'Bachelor of Science in Business Administration Major in Human Resource Development Management',
            ],
            [
                'course_name' => 'Bachelor of Science in Business Administration Major in Financial Management',
            ],
            [
                'course_name' => 'Bachelor of Science in Office Systems Management',
            ],
            // CEA
            [
                'course_name' => 'Bachelor of Science in Architecture',
            ],
            [
                'course_name' => 'Bachelor of Science in Civil Engineering',
            ],
            [
                'course_name' => 'Bachelor of Science in Computer Engineering',
            ],
            [
                'course_name' => 'Bachelor of Science in Electrical Engineering',
            ],
            [
                'course_name' => 'Bachelor of Science in Electronics and Communication Engineering',
            ],
            [
                'course_name' => 'Bachelor of Science in Geodetic Engineering',
            ],
            [
                'course_name' => 'Bachelor of Science in Geothermal Engineering',
            ],
            [
                'course_name' => 'Bachelor of Science in Mechanical Engineering',
            ],
            // CNPAHS
            [
                'course_name' => 'Bachelor of Science in Nursing',
            ],
            [
                'course_name' => 'Bachelor of Science in Pharmacy',
            ],
            [
                'course_name' => 'Midwifery',
            ],
            [
                'course_name' => 'Associate in Medical Dental Nursing Assistant (AMDNA)',
            ],
            // CTHM
            [
                'course_name' => 'Bachelor of Science in Hospitality Management',
            ],
            [
                'course_name' => 'Bachelor of Science in Tourism',
            ],
            // CAFF
            [
                'course_name' => 'Bachelor of Science in Forestry',
            ],
            [
                'course_name' => 'Bachelor of Science in Agriculture Major in Agronomy',
            ],
            [
                'course_name' => 'Bachelor of Science in Agriculture Major in Horticulture',
            ],
            [
                'course_name' => 'Bachelor of Science in Agriculture Major in Animal Science',
            ],
            [
                'course_name' => 'Bachelor of Science in Agriculture Major in Agricultural Extension',
            ],
            // CCJE
            [
                'course_name' => 'Bachelor of Science in Criminology',
            ],
            // CIT
            [
                'course_name' => 'Bachelor of Science in Automotive Technology',
            ],
            [
                'course_name' => 'Bachelor of Science in Aviation Maintenance',
            ],
            [
                'course_name' => 'Bachelor of Science in Civil Technology',
            ],
            [
                'course_name' => 'Bachelor of Science in Computer and Electronics Technology',
            ],
            [
                'course_name' => 'Bachelor of Science in Electrical Technology',
            ],
            [
                'course_name' => 'Bachelor of Science in Food Technology',
            ],
            [
                'course_name' => 'Bachelor of Science in Industrial Technology',
            ],
            [
                'course_name' => 'Bachelor of Science in Mechanical Technology',
            ],
            [
                'course_name' => 'Bachelor of Science in Refrigeration and Air Conditioning Technology',
            ],
            // CTED
            [
                'course_name' => 'Bachelor of Elementary Education',
            ],
            [
                'course_name' => 'Bachelor of Secondary Education',
            ]
        ];

        // Insert only the rows that are missing (matched by name) so the seeder can be re-run safely.
        foreach ($course as $row) {
            Course::firstOrCreate(['course_name' => $row['course_name']], $row);
        }
    }
}
