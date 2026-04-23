<?php

namespace Database\Seeders;

use App\Models\PatientType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PatientTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'id' => 1,
                'code' => 'student',
                'name' => 'Student',
            ],
            [
                'id' => 2,
                'code' => 'staff',
                'name' => 'Staff',
            ],
            [
                'id' => 3,
                'code' => 'faculty',
                'name' => 'Faculty',
            ],
            [
                'id' => 4,
                'code' => 'guest',
                'name' => 'Guest',
            ],
        ];

        foreach ($items as $item) {
            PatientType::updateOrCreate(['code' => $item['code']], $item);
        }

        $legacyDependent = PatientType::where('code', 'dependent')->first();
        $guestType = PatientType::where('code', 'guest')->first();

        if ($legacyDependent && $guestType && $legacyDependent->id !== $guestType->id) {
            DB::table('patients')
                ->where('patient_type_id', $legacyDependent->id)
                ->update(['patient_type_id' => $guestType->id]);

            $legacyDependent->delete();
        }
    }
}
