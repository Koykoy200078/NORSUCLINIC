<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $offices = [
            // Office of the University President
            [
                'office_name' => 'Office of the University President',
                'description' => 'Executive office overseeing overall university operations and administration'
            ],

            // Vice Presidents Offices
            [
                'office_name' => 'VP Administrative and Finance Office',
                'description' => 'Office of the Vice President for Administrative and Finance Affairs'
            ],
            [
                'office_name' => 'VP Academic Affairs Office',
                'description' => 'Office of the Vice President for Academic Affairs'
            ],
            [
                'office_name' => 'VP Research Development and Extension Office',
                'description' => 'Office of the Vice President for Research, Development and Extension'
            ],

            // Administrative Offices
            [
                'office_name' => 'Human Resource Management Office',
                'description' => 'Oversees employee recruitment, development, benefits, and personnel management'
            ],
            [
                'office_name' => 'Gender and Development Office',
                'description' => 'Promotes gender equality, development programs, and advocacy initiatives'
            ],
            [
                'office_name' => 'Planning and Development Office',
                'description' => 'Handles institutional planning, development programs, and strategic initiatives'
            ],
            [
                'office_name' => 'Supply and Property Management Office',
                'description' => 'Manages university supplies, procurement, property inventory and assets'
            ],
            [
                'office_name' => 'Project Management Office',
                'description' => 'Oversees university projects, infrastructure development and implementation'
            ],
            [
                'office_name' => 'Security and Safety Management Office',
                'description' => 'Ensures campus safety, security operations, and emergency response'
            ],
            [
                'office_name' => 'Enterprise Development Office',
                'description' => 'Manages income-generating projects and entrepreneurial initiatives'
            ],
            [
                'office_name' => 'Public Information Office',
                'description' => 'Handles public relations, communications, media, and information dissemination'
            ],

            // Finance-Related Offices
            [
                'office_name' => 'Budget Office',
                'description' => 'Manages university budget planning, allocation, and financial control'
            ],
            [
                'office_name' => 'Accounting Office',
                'description' => 'Handles accounting operations, financial records, and fiscal reporting'
            ],
            [
                'office_name' => 'Cashier Office',
                'description' => 'Manages cash transactions, payments, and financial collections'
            ],

            // Academic Support Offices
            [
                'office_name' => 'Registrar Office',
                'description' => 'Handles student records, enrollment, grades, and academic documentation'
            ],
            [
                'office_name' => 'Library Services',
                'description' => 'Manages library resources, research materials, and information services'
            ],
            [
                'office_name' => 'Admissions Office',
                'description' => 'Handles student admissions, applications, and entrance requirements'
            ],
            [
                'office_name' => 'Student Affairs and Services Office',
                'description' => 'Manages student services, activities, welfare programs, and student development'
            ],
            [
                'office_name' => 'Medical and Dental Office',
                'description' => 'Provides medical, dental, and health services to students, faculty and staff'
            ],
            [
                'office_name' => 'CARE Center',
                'description' => 'Counseling, Advocacy, Referral, and Education Center for student wellness'
            ],
            [
                'office_name' => 'Graduate Studies Office',
                'description' => 'Manages graduate programs, thesis supervision, and advanced degree requirements'
            ],

            // Technical and Support Offices
            [
                'office_name' => 'Management Information System Office',
                'description' => 'Provides IT support, manages information systems, and technology infrastructure'
            ],
            [
                'office_name' => 'Quality Assurance and Management Office',
                'description' => 'Ensures quality standards, institutional compliance, and continuous improvement'
            ],
            [
                'office_name' => 'Procurement Office',
                'description' => 'Handles procurement processes, bidding, and supplier management'
            ],
            [
                'office_name' => 'Office of University International Relations',
                'description' => 'Manages international partnerships, exchanges, and global collaborations'
            ],
            [
                'office_name' => 'Alumni Affairs Office',
                'description' => 'Maintains alumni relations, networking, and engagement programs'
            ],
            [
                'office_name' => 'Physical Plant and Facilities Office',
                'description' => 'Handles facility maintenance, campus infrastructure, and physical plant operations'
            ],
        ];

        foreach ($offices as $office) {
            Office::create($office);
        }
    }
}
