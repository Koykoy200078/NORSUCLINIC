<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class DefaultCurrenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'currency_name' => 'Philippine peso',
                'currency_icon' => '₱',
                'currency_code' => 'PHP',
                'is_default' => '1'
            ],
        ];

        foreach ($currencies as $currency) {
            // Check if the currency already exists
            Currency::updateOrCreate(
                ['currency_code' => $currency['currency_code']],
                $currency
            );
        }
    }
}
