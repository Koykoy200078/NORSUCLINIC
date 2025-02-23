<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class DefaultServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $input = [
            [
                'name' => 'Medical',
            ],
            [
                'name' => 'Dental',
            ],
        ];

        foreach ($input as $data) {
            ServiceCategory::create($data);
        }
    }
}
