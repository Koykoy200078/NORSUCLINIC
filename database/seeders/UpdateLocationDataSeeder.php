<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Barangay;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateLocationDataSeeder extends Seeder
{
    /**
     * Update location data with complete Philippines data including barangays.
     * This seeder will:
     * 1. Map existing address city/province references to the new data
     * 2. Clear and reseed cities, provinces, and barangays
     * 3. Update existing addresses with new IDs
     */
    public function run(): void
    {
        // Step 1: Get existing addresses with city/province references
        $existingAddresses = Address::whereNotNull('city_id')
            ->orWhereNotNull('state_id')
            ->get();

        // Step 2: Build a mapping of old city/province names to their IDs
        $addressMappings = [];
        foreach ($existingAddresses as $address) {
            $cityName = null;
            $stateName = null;

            if ($address->city_id) {
                $city = City::find($address->city_id);
                $cityName = $city ? $city->name : null;
            }

            if ($address->state_id) {
                $state = Province::find($address->state_id);
                $stateName = $state ? $state->name : null;
            }

            $addressMappings[$address->id] = [
                'city_name' => $cityName,
                'state_name' => $stateName,
            ];
        }

        $this->command->info('Found ' . count($addressMappings) . ' addresses to remap.');

        // Step 3: Disable foreign key checks and clear tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Barangay::truncate();
        City::truncate();
        Province::truncate();

        // Clear the addresses' city/state/barangay references temporarily
        Address::query()->update([
            'city_id' => null,
            'state_id' => null,
            'barangay_id' => null,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Step 4: Reseed countries if needed
        if (Country::count() === 0) {
            $countries = file_get_contents(storage_path('countries/countries.json'));
            $countries = json_decode($countries, true)['countries'];
            Country::insert($countries);
            $this->command->info('Seeded ' . count($countries) . ' countries.');
        }

        // Step 5: Seed provinces
        $states = file_get_contents(storage_path('countries/states.json'));
        $states = json_decode($states, true)['states'];
        Province::insert($states);
        $this->command->info('Seeded ' . count($states) . ' provinces.');

        // Step 6: Seed cities
        $cities = file_get_contents(storage_path('countries/cities.json'));
        $cities = json_decode($cities, true)['cities'];
        collect($cities)
            ->chunk(500)
            ->each(function ($chunk) {
                City::insert($chunk->toArray());
            });
        $this->command->info('Seeded ' . count($cities) . ' cities/municipalities.');

        // Step 7: Seed barangays
        $barangaysPath = storage_path('countries/barangays.json');
        if (file_exists($barangaysPath)) {
            $barangays = file_get_contents($barangaysPath);
            $barangays = json_decode($barangays, true)['barangays'];
            $count = 0;
            collect($barangays)
                ->chunk(500)
                ->each(function ($chunk) use (&$count) {
                    Barangay::insert($chunk->toArray());
                    $count += $chunk->count();
                });
            $this->command->info('Seeded ' . $count . ' barangays.');
        }

        // Step 8: Remap addresses to new IDs
        foreach ($addressMappings as $addressId => $mapping) {
            $address = Address::find($addressId);
            if (!$address) continue;

            $newStateId = null;
            $newCityId = null;

            // Find new province by name
            if ($mapping['state_name']) {
                $newState = Province::where('name', $mapping['state_name'])->first();
                if ($newState) {
                    $newStateId = $newState->id;
                }
            }

            // Find new city by name (within the state if possible)
            if ($mapping['city_name']) {
                $cityQuery = City::where(function ($q) use ($mapping) {
                    $q->where('name', $mapping['city_name'])
                        ->orWhere('name', 'like', '%' . str_replace('City', '', $mapping['city_name']) . '%');
                });

                if ($newStateId) {
                    $cityQuery->where('state_id', $newStateId);
                }

                $newCity = $cityQuery->first();
                if ($newCity) {
                    $newCityId = $newCity->id;
                    $newStateId = $newCity->state_id; // Ensure state matches
                }
            }

            $address->update([
                'state_id' => $newStateId,
                'city_id' => $newCityId,
            ]);

            $this->command->info("Remapped address {$addressId}: state={$newStateId}, city={$newCityId}");
        }

        $this->command->info('Location data update complete!');
    }
}
