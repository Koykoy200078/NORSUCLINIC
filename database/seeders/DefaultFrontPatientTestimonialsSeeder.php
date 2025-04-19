<?php

namespace Database\Seeders;

use App\Models\FrontPatientTestimonial;
use Illuminate\Database\Seeder;

class DefaultFrontPatientTestimonialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inputs = [
            [
                'name' => 'JOHN DOE',
                'designation' => 'XYZ Inc.',
                'short_description' => 'Hello world',
                'profile' => asset('assets/front/images/testimonials/testimonial-1.jpg'),
                'is_default' => true,
            ]
        ];

        foreach ($inputs as $input) {
            $profile = $input['profile'];
            unset($input['profile']);
            $slider = FrontPatientTestimonial::create($input);
            //            $slider->addMediaFromUrl($profile)->toMediaCollection(FrontPatientTestimonial::FRONT_PATIENT_PROFILE,
            //                config('app.media_disc'));
        }
    }
}
