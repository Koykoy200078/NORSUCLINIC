<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
                'contact' => '1234567890',
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
            $user = User::create($user);
            if ($user->type == User::DOCTOR) {
                $doctor = Doctor::create(['user_id' => $user->id]);
                $user->address()->create(['owner_id' => $user->id]);
                $specializationIds = Specialization::pluck('id');
                $doctor->specializations()->sync($specializationIds);
            }
            if ($user->type == User::PATIENT) {
                $patient = Patient::create(['user_id' => $user->id, 'patient_unique_id' => 'UNIQUE' . $user->id]);
                $patient->address()->create(['owner_id' => $patient['user_id']]);
            }
        }
    }
}
