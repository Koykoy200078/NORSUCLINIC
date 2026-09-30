<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class AddTwoFieldsSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::firstOrCreate(['key' => 'about_title'], ['value' => 'What We do Actually']);
        Setting::firstOrCreate(['key' => 'about_short_description'], [
            'value' => '',
        ]);
    }
}
