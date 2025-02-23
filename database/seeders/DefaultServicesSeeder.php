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
                'charges' => '400',
                'status' => Service::ACTIVE,
                'short_description' => 'Professional dental cleaning service.',
                'icon' => asset('assets/front/images/services_images/DentalCleaning.png'),
            ]
        ];

        $doctor = Doctor::firstOrfail();

        foreach ($input as $data) {
            $image = $data['icon'];
            unset($data['icon']);
            $service = Service::create($data);
            $service->serviceDoctors()->sync($doctor->id);
        }
    }
}
