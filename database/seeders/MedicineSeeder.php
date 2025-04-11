<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Medicine Categories
        $categories = [
            'Antibiotics',
            'Allergy Relief',
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create([
                'name' => $category,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Medicine Brands
        $brands = [
            'Amoxil, Moxatag', // this is for antibiotics
            'Zyrtec', // this is for allergy relief
        ];

        foreach ($brands as $brand) {
            \App\Models\Brand::create([
                'name' => $brand,
                'email' => null,
                'phone' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
