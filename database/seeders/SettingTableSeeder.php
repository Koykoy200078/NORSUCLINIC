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
        Setting::create(['key' => 'contact_no', 'value' => '9123456789']);
        Setting::create(['key' => 'email', 'value' => 'norsumedicalclinic@gmail.com']);
        Setting::create(['key' => 'specialties', 'value' => '1']);
        Setting::create(['key' => 'currency', 'value' => '1']);
        Setting::create([
            'key' => 'address_one',
            'value' => 'Kagawasan Avenue, Capitol Area, Dumaguete City, Negros Oriental, Philippines 6200',
        ]);
        Setting::create([
            'key' => 'address_two',
            'value' => '',
        ]);
        Setting::create(['key' => 'country_id', 'value' => '1']);
        Setting::create(['key' => 'state_id', 'value' => '54']);
        Setting::create(['key' => 'city_id', 'value' => '66']);
        Setting::create(['key' => 'postal_code', 'value' => '6200']);
        Setting::create(['key' => 'logo', 'value' => $logoUrl]);
        Setting::create(['key' => 'favicon', 'value' => $favicon]);
        Setting::create(['key' => 'country_code', 'value' => '63']);
    }
}
