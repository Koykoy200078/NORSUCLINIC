<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $logoUrl = ('assets/image/norsu_logo.png');
        $favicon = ('assets/image/norsu_favicon.ico');

        Setting::create(['key' => 'clinic_name', 'value' => 'Norsu Clinic']);
        Setting::create(['key' => 'contact_no', 'value' => '1234567890']);
        Setting::create(['key' => 'email', 'value' => 'infycare@email.com']);
        Setting::create(['key' => 'specialities', 'value' => '1']);
        Setting::create(['key' => 'currency', 'value' => '1']);
        Setting::create([
            'key' => 'address_one',
            'value' => 'Capitol Area, Kagawasan Avenue, Dumaguete, 6200 Negros Oriental',
        ]);
        Setting::create([
            'key' => 'address_two',
            'value' => 'Capitol Area, Kagawasan Avenue, Dumaguete, 6200 Negros Oriental',
        ]);
        Setting::create(['key' => 'country_id', 'value' => '1']);
        Setting::create(['key' => 'state_id', 'value' => '25']);
        Setting::create(['key' => 'city_id', 'value' => '87']);
        Setting::create(['key' => 'postal_code', 'value' => '6200']);
        Setting::create(['key' => 'logo', 'value' => $logoUrl]);
        Setting::create(['key' => 'favicon', 'value' => $favicon]);
        Setting::create(['key' => 'country_code', 'value' => '63']);
    }
}
