<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Database\Seeder;

class DefaultServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $input = [
            [
                'category_id' => '1',
                'name' => 'General Checkup',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'Routine medical checkup for students.',
                'icon' => asset('assets/front/images/services_images/GeneralCheckup.png'),
            ],
            [
                'category_id' => '2',
                'name' => 'Dental Cleaning',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'Professional dental cleaning service.',
                'icon' => asset('assets/front/images/services_images/DentalCleaning.png'),
            ],
            [
                'category_id' => '1',
                'name' => 'Medication Dispensing Request',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'Request for medication dispensing.',
                'icon' => asset('assets/front/images/services_images/MedicationDispensing.png'),
            ],
            [
                'category_id' => '1',
                'name' => 'General Consultation',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'General medical consultation.',
                'icon' => asset('assets/front/images/services_images/GeneralConsultation.png'),
            ],
            [
                'category_id' => '1',
                'name' => 'Emergency Consultation',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'Emergency medical consultation.',
                'icon' => asset('assets/front/images/services_images/EmergencyConsultation.png'),
            ],
            [
                'category_id' => '1',
                'name' => 'Specialist Consultation',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'Consultation with a specialist.',
                'icon' => asset('assets/front/images/services_images/SpecialistConsultation.png'),
            ],
            [
                'category_id' => '1',
                'name' => 'Dressing Consultation (Wound Care)',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'Consultation for wound care and dressing.',
                'icon' => asset('assets/front/images/services_images/DressingConsultation.png'),
            ],
            [
                'category_id' => '1',
                'name' => 'Chronic Disease Management Consultation',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'Consultation for managing chronic diseases.',
                'icon' => asset('assets/front/images/services_images/ChronicDiseaseManagement.png'),
            ],
            [
                'category_id' => '1',
                'name' => 'Psychiatric Consultation',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'Consultation for psychiatric care.',
                'icon' => asset('assets/front/images/services_images/PsychiatricConsultation.png'),
            ],
            [
                'category_id' => '1',
                'name' => 'Nutritional Consultation',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'Consultation for nutritional advice.',
                'icon' => asset('assets/front/images/services_images/NutritionalConsultation.png'),
            ],
            [
                'category_id' => '1',
                'name' => 'Pediatric Consultation',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'Consultation for pediatric care.',
                'icon' => asset('assets/front/images/services_images/PediatricConsultation.png'),
            ],
            [
                'category_id' => '1',
                'name' => 'Rehabilitation Consultation',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'Consultation for rehabilitation services.',
                'icon' => asset('assets/front/images/services_images/RehabilitationConsultation.png'),
            ],
            [
                'category_id' => '2',
                'name' => 'Dental Consultation',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'Consultation for dental care.',
                'icon' => asset('assets/front/images/services_images/DentalConsultation.png'),
            ],
            [
                'category_id' => '1',
                'name' => 'Preoperative and Postoperative Consultation',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'Consultation before and after surgery.',
                'icon' => asset('assets/front/images/services_images/PrePostOperativeConsultation.png'),
            ],
            [
                'category_id' => '1',
                'name' => 'Immunization Consultation',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'Consultation for immunization services.',
                'icon' => asset('assets/front/images/services_images/ImmunizationConsultation.png'),
            ],
            [
                'category_id' => '1',
                'name' => 'Laboratory and Diagnostic Consultation',
                'charges' => '0',
                'status' => Service::ACTIVE,
                'short_description' => 'Consultation for laboratory and diagnostic services.',
                'icon' => asset('assets/front/images/services_images/LaboratoryDiagnosticConsultation.png'),
            ],
        ];

        // $doctor = Doctor::firstOrfail();

        foreach ($input as $data) {
            $image = $data['icon'];
            unset($data['icon']);
            $service = Service::create($data);
            // $service->serviceDoctors()->sync($doctor->id);
        }
    }
}
