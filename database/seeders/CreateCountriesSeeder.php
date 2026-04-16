<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use Illuminate\Database\Seeder;

class CreateCountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed countries if not exists
        if (Country::count() === 0) {
            $countries = file_get_contents(storage_path('countries/countries.json'));
            $countries = json_decode($countries, true)['countries'];
            Country::insert($countries);
        }

        // Seed provinces if not exists
        if (Province::count() === 0) {
            $states = file_get_contents(storage_path('countries/states.json'));
            $states = json_decode($states, true)['states'];
            Province::insert($states);
        }

        // Seed cities if not exists
        if (City::count() === 0) {
            $cities = file_get_contents(storage_path('countries/cities.json'));
            $cities = json_decode($cities, true)['cities'];
            collect($cities)
                ->chunk(500)
                ->each(function ($city) {
                    City::insert($city->toArray());
                });
        }

        // Seed barangays if not exists
        if (Barangay::count() === 0) {
            $barangaysPath = storage_path('countries/barangays.json');
            if (file_exists($barangaysPath)) {
                $barangays = file_get_contents($barangaysPath);
                $barangays = json_decode($barangays, true)['barangays'];
                collect($barangays)
                    ->chunk(500)
                    ->each(function ($barangay) {
                        Barangay::insert($barangay->toArray());
                    });
            }
        }
    }
}
