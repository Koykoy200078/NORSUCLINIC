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
        $input = [
            [
                'currency_name' => 'Philippines Peso',
                'currency_icon' => '₱',
                'currency_code' => 'PHP',
                'is_default' => '1'
            ]
        ];

        foreach ($input as $data) {
            Currency::create($data);
        }
    }
}
