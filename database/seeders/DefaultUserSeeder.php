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
                'time_zone' => '0',
            ],
            // Doctor
            [
                'first_name' => 'Adam',
                'last_name' => 'Diaz',
                'contact' => '1234567890',
                'gender' => User::MALE,
                'type' => User::DOCTOR,
                'email' => 'doctor@norsuclinic.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('123456'),
                'country_code' => '63',
                'time_zone' => '0'
            ],
        ];

        // Add 9 doctors
        for ($i = 0; $i < 9; $i++) {
            $users[] = [
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'contact' => fake()->numerify('09#########'),
                'gender' => fake()->randomElement([User::MALE, User::FEMALE]),
                'type' => User::DOCTOR,
                'email' => fake()->unique()->safeEmail(),
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('123456'),
                'country_code' => '63',
                'time_zone' => '0'
            ];
        }

        // Add 999 students (patients)
        for ($i = 0; $i < 999; $i++) {
            $users[] = [
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'contact' => fake()->numerify('09#########'),
                'gender' => fake()->randomElement([User::MALE, User::FEMALE]),
                'type' => User::PATIENT,
                'email' => fake()->unique()->safeEmail(),
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('123456'),
                'country_code' => '63',
                'time_zone' => '0'
            ];
        }

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
