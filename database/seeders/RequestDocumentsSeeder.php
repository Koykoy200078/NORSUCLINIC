<?php

namespace Database\Seeders;

use App\Models\RequestDocuments;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RequestDocumentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requestDocuments = [
            [
                'user_id' => 1,
                'clinic_id' => 1,
                'document_type' => 'Medical Report',
                'description' => 'Request for a detailed medical report.',
                'requested_at' => Carbon::now()->subDays(2),
                'fulfilled_at' => Carbon::now()->subDay(),
            ],
            [
                'user_id' => 2,
                'clinic_id' => 1,
                'document_type' => 'Prescription',
                'description' => 'Request for a prescription refill.',
                'requested_at' => Carbon::now()->subDays(5),
                'fulfilled_at' => null,
            ],
        ];

        foreach ($requestDocuments as $requestDocument) {
            RequestDocuments::create($requestDocument);
        }
    }
}
