<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class AddFieldsSettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $termsAndConditions = view('fronts.cms.terms_conditions')->render();
        Setting::firstOrCreate(['key' => 'terms_conditions'], ['value' => $termsAndConditions]);

        $privacyPolicy = view('fronts.cms.privacy_policy')->render();
        Setting::firstOrCreate(['key' => 'privacy_policy'], ['value' => $privacyPolicy]);
    }
}
