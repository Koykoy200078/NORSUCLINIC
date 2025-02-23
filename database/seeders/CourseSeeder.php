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
            [
                'course_name' => 'BACHELOR OF AGRICULTURAL TECHNOLOGY Major in Animal Husbandry',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN AGRICULTURE Major in Animal Science',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN AGRICULTURE Major in Agribusiness',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN AGRICULTURE Major in Agronomy',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN FISHERIES',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN FORESTRY',
            ],
            [
                'course_name' => 'BACHELOR OF ARTS Major in General Curriculum',
            ],
            [
                'course_name' => 'BACHELOR OF ARTS Major in Social Science',
            ],
            [
                'course_name' => 'BACHELOR OF MASS COMMUNICATION',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN BIOLOGY',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN CHEMISTRY',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN COMPUTER SCIENCE',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN MATHEMATICS',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN PSYCHOLOGY',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN SOCIAL SCIENCE',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN ACCOUNTANCY',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION Major in Financial Management',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION Major in Human Resource Management',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION Major in Marketing Management',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN HOSPITALITY MANAGEMENT',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN OFFICE ADMINISTRATION',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN TOURISM MANAGEMENT',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN CRIMINOLOGY',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN ARCHITECTURE',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN CIVIL ENGINEERING',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN COMPUTER ENGINEERING',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN ELECTRICAL ENGINEERING',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN ELECTRONICS ENGINEERING',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN GEODETIC ENGINEERING',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN ELECTRONICS ENGINEERING',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN ELECTRONICS ENGINEERING',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN AVIATION MAINTENANCE Major in Airframe and Maintenance',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION Major in Avionics (Aviation Electronics)',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN INDUSTRIAL TECHNOLOGY Major in Architectural Drafting Technology',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN INDUSTRIAL TECHNOLOGY Major in Automotive Technology',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN INDUSTRIAL TECHNOLOGY Major in Civil Technology',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN INDUSTRIAL TECHNOLOGY Major in Computer Technology',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN INDUSTRIAL TECHNOLOGY Major in Electrical Technology',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN INDUSTRIAL TECHNOLOGY Major in Food Technology',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN INDUSTRIAL TECHNOLOGY Major in Garments Technology',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN INDUSTRIAL TECHNOLOGY Major in Mechanical Technology',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN INDUSTRIAL TECHNOLOGY Major in Refrigeration and Air Conditioning Technology',
            ],
            [
                'course_name' => 'BACHELOR OF TECHNOLOGICAL TECHNOLOGY Major in Computer Technology',
            ],
            [
                'course_name' => 'ASSOCIATE IN MEDICAL-DENTAL-NURSING ASSISTANT',
            ],
            [
                'course_name' => 'MIDWIFERY',
            ],
            [
                'course_name' => 'ASSOCIATE IN MEDICAL-DENTAL-NURSING ASSISTANT',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN NURSING',
            ],
            [
                'course_name' => 'BACHELOR OF SCIENCE IN PHARMACY',
            ],
            [
                'course_name' => 'BACHELOR OF CULTURE & ARTS EDUCATION',
            ],
            [
                'course_name' => 'BACHELOR OF EARLY CHILDHOOD EDUCATION',
            ],
            [
                'course_name' => 'BACHELOR OF ELEMENTARY EDUCATION Major in General Curriculum',
            ],
            [
                'course_name' => 'BACHELOR OF PHYSICAL EDUCATION',
            ],
            [
                'course_name' => 'BACHELOR OF SECONDARY EDUCATION Major English',
            ],
            [
                'course_name' => 'BACHELOR OF SPECIAL NEEDS EDUCATION Specialization Deaf and Hard-of-Hearing Learners',
            ],
            [
                'course_name' => 'BACHELOR OF SPECIAL NEEDS EDUCATION Specialization Early Childhood Education',
            ],
            [
                'course_name' => 'BACHELOR OF SPECIAL NEEDS EDUCATION Specialization Elementary School Teaching',
            ],
            [
                'course_name' => 'BACHELOR OF SPECIAL NEEDS EDUCATION Specialization General',
            ],
            [
                'course_name' => 'BACHELOR OF SPECIAL NEEDS EDUCATION Specialization Teaching Learners with Visual Impairment',
            ],
            [
                'course_name' => 'BACHELOR OF TECHNOLOGY & LIVELIHOOD EDUCATION Specialization Agri-Fishery Arts',
            ],
            [
                'course_name' => 'BACHELOR OF TECHNOLOGY & LIVELIHOOD EDUCATION Specialization Home Economics',
            ],
            [
                'course_name' => 'BACHELOR OF TECHNOLOGY & LIVELIHOOD EDUCATION Specialization Industrial Arts',
            ],
            [
                'course_name' => 'BACHELOR OF SECONDARY EDUCATION Major in Values Education',
            ],
            [
                'course_name' => 'BACHELOR OF SECONDARY EDUCATION Major in Filipino',
            ],
            [
                'course_name' => 'BACHELOR OF SECONDARY EDUCATION Major in Mathematics',
            ],
            [
                'course_name' => 'BACHELOR OF SECONDARY EDUCATION Major in Sciences',
            ],
            [
                'course_name' => 'BACHELOR OF SECONDARY EDUCATION Major in Social Studies',
            ],
            [
                'course_name' => 'BACHELOR OF TECHNOLOGY & LIVELIHOOD EDUCATION Specialization Information & Communication Technology',
            ],
        ];

        Course::insert($course);
    }
}
