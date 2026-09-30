<?php

namespace Database\Seeders;

use App\Models\Slider;
use Illuminate\Database\Seeder;

class DefaultSliderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inputs = [
            [
                'title' => 'Your Campus Health Care',
                'short_description' => 'Prioritizing your wellness at NORSU Clinic.',
                'image' => ('assets/front/images/home/home-page-image.png'),
                'is_default' => true,
            ],
        ];

        foreach ($inputs as $input) {
            $image = $input['image'];
            unset($input['image']);
            $slider = Slider::firstOrCreate(['title' => $input['title']], $input);
            //            $slider->addMediaFromUrl($image)->toMediaCollection(Slider::SLIDER_IMAGE, config('app.media_disc'));
        }
    }
}
