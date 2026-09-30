<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Doctor;
use App\Models\Specialization;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Admin
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'contact' => null,
                'gender' => User::MALE,
                'type' => User::ADMIN,
                'email' => 'admin@norsuclinic.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('123456'),
                'country_code' => '63',
                'time_zone' => 'Asia/Manila',
            ]
        ];

        foreach ($users as $user) {
            // Create the default administrator only when it does not exist yet. An existing account (with
            // whatever password the clinic has since set) must be left alone: re-running the seeders used to
            // crash with a duplicate-e-mail error and would otherwise have reset the password to 123456.
            $existing = User::where('email', $user['email'])->first();
            if ($existing) {
                continue;
            }

            $user = User::create($user);
            if ($user->type == User::DOCTOR) {
                $doctor = Doctor::create(['user_id' => $user->id]);
                $user->address()->create(['owner_id' => $user->id]);
                $specializationIds = Specialization::pluck('id');
                $doctor->specializations()->sync($specializationIds);
            }
            if ($user->type == User::PATIENT) {
                $patientIdentifier = strtoupper((string) ($user->university_id_number ?: ('UNIQUE' . $user->id)));
                $patient = Patient::create(['user_id' => $user->id, 'patient_unique_id' => $patientIdentifier]);
                $patient->address()->create(['owner_id' => $patient['user_id']]);
            }
        }
    }
}
