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

        Setting::firstOrCreate(['key' => 'clinic_name'], ['value' => 'Norsu Clinic']);
        Setting::firstOrCreate(['key' => 'landline_no'], ['value' => '522-5050 then local 1149']);
        Setting::firstOrCreate(['key' => 'contact_no'], ['value' => '9123456789']);
        Setting::firstOrCreate(['key' => 'email'], ['value' => 'norsumedicalclinic@gmail.com']);
        Setting::firstOrCreate(['key' => 'specialties'], ['value' => '1']);
        Setting::firstOrCreate(['key' => 'currency'], ['value' => '1']);
        Setting::firstOrCreate(['key' => 'address_one'], [
            'value' => 'Kagawasan Avenue, Capitol Area, Dumaguete City, Negros Oriental, Philippines 6200',
        ]);
        Setting::firstOrCreate(['key' => 'address_two'], [
            'value' => '',
        ]);
        Setting::firstOrCreate(['key' => 'country_id'], ['value' => '1']);
        Setting::firstOrCreate(['key' => 'state_id'], ['value' => '54']);
        Setting::firstOrCreate(['key' => 'city_id'], ['value' => '66']);
        Setting::firstOrCreate(['key' => 'postal_code'], ['value' => '6200']);
        Setting::firstOrCreate(['key' => 'logo'], ['value' => $logoUrl]);
        Setting::firstOrCreate(['key' => 'favicon'], ['value' => $favicon]);
        Setting::firstOrCreate(['key' => 'country_code'], ['value' => '63']);
        // Signs the ACCOMPLISHMENT REPORT ("Noted by"); filled in Settings > General.
        Setting::firstOrCreate(['key' => 'university_physician_name'], ['value' => '']);
        Setting::firstOrCreate(['key' => 'university_physician_title'], ['value' => 'University Physician']);
    }
}
