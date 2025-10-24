<?php

namespace Database\Seeders;

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
            'Pain Relievers',
            'Cold and Flu Relief',
            'Antacids',
            'Motion Sickness',
            'Topical Treatments',
        ];

        // foreach ($categories as $category) {
        //     \App\Models\Category::create([
        //         'name' => $category,
        //         'is_active' => 1,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }

        // // Medicine Brands
        // $brands = [
        //     'Amoxil, Augmentin, Moxatag', // Antibiotics
        //     'Zyrtec, Claritin, Allerta', // Allergy Relief
        //     'Biogesic, Calpol, Tylenol', // Pain Relievers
        //     'Bioflu, Neozep', // Cold and Flu Relief
        //     'Prilosec, Losec', // Antacids
        //     'Bonamine, Dizitab', // Motion Sickness
        //     'Cortaid, Betadine', // Topical Treatments
        // ];

        // foreach ($brands as $brand) {
        //     \App\Models\Brand::create([
        //         'name' => $brand,
        //         'email' => fake()->unique()->safeEmail(),
        //         'phone' => fake()->regexify('\+639[0-9]{2}[0-9]{3}[0-9]{4}'), // Philippine phone number without spaces
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }

        // // Medicines
        // $medicines = [
        //     [
        //         'name' => 'Amoxicillin',
        //         'category' => 'Antibiotics',
        //         'brand' => 'Amoxil, Augmentin, Moxatag',
        //         'description' => 'Antibiotic used to treat bacterial infections.',
        //     ],
        //     [
        //         'name' => 'Cetirizine',
        //         'category' => 'Allergy Relief',
        //         'brand' => 'Zyrtec, Claritin, Allerta',
        //         'description' => 'Antihistamine for relieving allergy symptoms.',
        //     ],
        //     [
        //         'name' => 'Paracetamol',
        //         'category' => 'Pain Relievers',
        //         'brand' => 'Biogesic, Calpol, Tylenol',
        //         'description' => 'Pain reliever and fever reducer.',
        //     ],
        //     [
        //         'name' => 'Phenylephrine',
        //         'category' => 'Cold and Flu Relief',
        //         'brand' => 'Bioflu, Neozep',
        //         'description' => 'Relieves nasal congestion and other cold symptoms.',
        //     ],
        //     [
        //         'name' => 'Omeprazole',
        //         'category' => 'Antacids',
        //         'brand' => 'Prilosec, Losec',
        //         'description' => 'Reduces stomach acid to treat reflux and ulcers.',
        //     ],
        //     [
        //         'name' => 'Meclizine',
        //         'category' => 'Motion Sickness',
        //         'brand' => 'Bonamine, Dizitab',
        //         'description' => 'Prevents and treats nausea, vomiting, and dizziness caused by motion sickness.',
        //     ],
        //     [
        //         'name' => 'Hydrocortisone Cream',
        //         'category' => 'Topical Treatments',
        //         'brand' => 'Cortaid, Betadine',
        //         'description' => 'Treats skin inflammation, redness, and itching.',
        //     ],
        //     [
        //         'name' => 'Dextromethorphan',
        //         'category' => 'Cold and Flu Relief',
        //         'brand' => 'Robitussin',
        //         'description' => 'Suppresses cough caused by colds or flu.',
        //     ],
        // ];

        // foreach ($medicines as $medicine) {
        //     $category = \App\Models\Category::where('name', $medicine['category'])->first();
        //     $brand = \App\Models\Brand::where('name', $medicine['brand'])->first();

        //     if (!$category) {
        //         // Log or handle missing category
        //         echo "Category not found: {$medicine['category']}\n";
        //         continue;
        //     }

        //     if (!$brand) {
        //         // Log or handle missing brand
        //         echo "Brand not found: {$medicine['brand']}\n";
        //         continue;
        //     }

        //     \App\Models\Medicine::create([
        //         'name' => $medicine['name'],
        //         'category_id' => $category->id,
        //         'brand_id' => $brand->id,
        //         'description' => $medicine['description'],
        //         'selling_price' => 0, // Default selling price
        //         'buying_price' => fake()->randomFloat(2, 50, 500), // Random buying price between 50 and 500
        //         'quantity' => 0, // Default quantity
        //         'available_quantity' => 0, // Default available quantity
        //         'salt_composition' => '',
        //         'side_effects' => '',
        //         'currency_symbol' => 1, // Default currency symbol
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }
    }
}
