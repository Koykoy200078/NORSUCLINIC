<?php

namespace Database\Seeders;

use App\Models\InsuranceProvider;
use Illuminate\Database\Seeder;

class InsuranceProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            ['name' => 'PhilHealth'],
            ['name' => 'Maxicare'],
            ['name' => 'Intellicare'],
            ['name' => 'Etiqa'],
            ['name' => 'Cocolife Healthcare'],
        ];

        foreach ($providers as $provider) {
            InsuranceProvider::updateOrCreate(['name' => $provider['name']], $provider);
        }
    }
}
