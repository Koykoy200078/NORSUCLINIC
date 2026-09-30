<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Departments are based on actual academic programs at Negros Oriental State University
     * organized by college. These departments align with the courses offered at NORSU.
     */
    public function run(): void
    {
        $departments = [
            // College of Arts and Sciences (CAS) - College ID: 1
            [
                'department_name' => 'Department of Biological Sciences',
                'college_id' => 1,
                'description' => 'Offers Biology and related life science programs'
            ],
            [
                'department_name' => 'Department of Physical Sciences',
                'college_id' => 1,
                'description' => 'Includes Chemistry, Geology, and related programs'
            ],
            [
                'department_name' => 'Department of Computer Science',
                'college_id' => 1,
                'description' => 'Offers Computer Science programs'
            ],
            [
                'department_name' => 'Department of Information Technology',
                'college_id' => 1,
                'description' => 'Offers Information Technology programs'
            ],
            [
                'department_name' => 'Department of Mathematics',
                'college_id' => 1,
                'description' => 'Handles Mathematics and Applied Mathematics programs'
            ],
            [
                'department_name' => 'Department of Social Sciences',
                'college_id' => 1,
                'description' => 'Includes Psychology and related social science programs'
            ],
            [
                'department_name' => 'Department of Mass Communication',
                'college_id' => 1,
                'description' => 'Handles Mass Communication and Media programs'
            ],

            // College of Business Administration (CBA) - College ID: 2
            [
                'department_name' => 'Department of Accountancy',
                'college_id' => 2,
                'description' => 'Offers Accountancy programs and CPA preparation'
            ],
            [
                'department_name' => 'Department of Business Administration',
                'college_id' => 2,
                'description' => 'Handles BSBA majors including HRM, Financial Management, and Marketing'
            ],
            [
                'department_name' => 'Department of Office Administration',
                'college_id' => 2,
                'description' => 'Offers Office Systems Management programs'
            ],

            // College of Engineering and Architecture (CEA) - College ID: 3
            [
                'department_name' => 'Department of Architecture',
                'college_id' => 3,
                'description' => 'Offers Bachelor of Science in Architecture'
            ],
            [
                'department_name' => 'Department of Civil Engineering',
                'college_id' => 3,
                'description' => 'Handles Civil and Geodetic Engineering programs'
            ],
            [
                'department_name' => 'Department of Electrical Engineering',
                'college_id' => 3,
                'description' => 'Includes Electrical and Electronics Engineering programs'
            ],
            [
                'department_name' => 'Department of Computer Engineering',
                'college_id' => 3,
                'description' => 'Offers Computer Engineering programs'
            ],
            [
                'department_name' => 'Department of Mechanical Engineering',
                'college_id' => 3,
                'description' => 'Handles Mechanical and Geothermal Engineering programs'
            ],

            // College of Nursing, Pharmacy and Allied Health Sciences (CNPAHS) - College ID: 4
            [
                'department_name' => 'Department of Nursing',
                'college_id' => 4,
                'description' => 'Offers BSN and related nursing programs'
            ],
            [
                'department_name' => 'Department of Pharmacy',
                'college_id' => 4,
                'description' => 'Handles Pharmacy programs'
            ],
            [
                'department_name' => 'Department of Allied Health Sciences',
                'college_id' => 4,
                'description' => 'Includes Midwifery and Medical-Dental programs'
            ],

            // College of Tourism and Hospitality Management (CTHM) - College ID: 5
            [
                'department_name' => 'Department of Hospitality Management',
                'college_id' => 5,
                'description' => 'Offers Hospitality Management programs'
            ],
            [
                'department_name' => 'Department of Tourism Management',
                'college_id' => 5,
                'description' => 'Handles Tourism programs'
            ],

            // College of Agriculture, Forestry and Fishery (CAFF) - College ID: 6
            [
                'department_name' => 'Department of Forestry',
                'college_id' => 6,
                'description' => 'Offers Forestry programs'
            ],
            [
                'department_name' => 'Department of Agriculture',
                'college_id' => 6,
                'description' => 'Handles Agriculture majors including Agronomy, Horticulture, Animal Science, and Agricultural Extension'
            ],

            // College of Criminal Justice Education (CCJE) - College ID: 7
            [
                'department_name' => 'Department of Criminology',
                'college_id' => 7,
                'description' => 'Offers Criminology programs'
            ],

            // College of Industrial Technology (CIT) - College ID: 8
            [
                'department_name' => 'Department of Automotive Technology',
                'college_id' => 8,
                'description' => 'Offers Automotive and Aviation Maintenance programs'
            ],
            [
                'department_name' => 'Department of Civil Technology',
                'college_id' => 8,
                'description' => 'Handles Civil Technology programs'
            ],
            [
                'department_name' => 'Department of Electrical Technology',
                'college_id' => 8,
                'description' => 'Includes Electrical and Electronics Technology programs'
            ],
            [
                'department_name' => 'Department of Mechanical Technology',
                'college_id' => 8,
                'description' => 'Offers Mechanical Technology and Refrigeration programs'
            ],
            [
                'department_name' => 'Department of Food Technology',
                'college_id' => 8,
                'description' => 'Handles Food Technology programs'
            ],
            [
                'department_name' => 'Department of Industrial Technology',
                'college_id' => 8,
                'description' => 'General Industrial Technology programs'
            ],

            // College of Teacher Education (CTED) - College ID: 9
            [
                'department_name' => 'Department of Elementary Education',
                'college_id' => 9,
                'description' => 'Offers Bachelor of Elementary Education'
            ],
            [
                'department_name' => 'Department of Secondary Education',
                'college_id' => 9,
                'description' => 'Handles Bachelor of Secondary Education with various specializations'
            ],

            // School of Law - College ID: 10
            [
                'department_name' => 'Department of Law',
                'college_id' => 10,
                'description' => 'Offers Juris Doctor (JD) program'
            ],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(['department_name' => $department['department_name']], $department);
        }
    }
}
