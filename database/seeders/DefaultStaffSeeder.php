<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DefaultStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the staff role if it doesn't exist
        $staffRole = Role::firstOrCreate(['name' => 'staff', 'display_name' => 'Staff']);

        // Generate 5 staff members
        $staffMembers = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'contact' => '1234567890',
                'gender' => User::MALE,
                'type' => User::STAFF,
                'email' => 'john.doe@gmail.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'country_code' => '63',
                'time_zone' => '0'
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'contact' => '0987654321',
                'gender' => User::FEMALE,
                'type' => User::STAFF,
                'email' => 'jane.smith@gmail.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'country_code' => '63',
                'time_zone' => '0'
            ],
            [
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'contact' => '1122334455',
                'gender' => User::FEMALE,
                'type' => User::STAFF,
                'email' => 'alice.johnson@gmail.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'country_code' => '63',
                'time_zone' => '0'
            ],
            [
                'first_name' => 'Bob',
                'last_name' => 'Brown',
                'contact' => '2233445566',
                'gender' => User::MALE,
                'type' => User::STAFF,
                'email' => 'bob.brown@gmail.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'country_code' => '63',
                'time_zone' => '0'
            ],
            [
                'first_name' => 'Charlie',
                'last_name' => 'Davis',
                'contact' => '3344556677',
                'gender' => User::MALE,
                'type' => User::STAFF,
                'email' => 'charlie.davis@gmail.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'country_code' => '63',
                'time_zone' => '0'
            ],
        ];

        foreach ($staffMembers as $staffData) {
            $user = User::create($staffData);
            $user->assignRole($staffRole);
        }

        // Assign all permissions to the staff role
        $allPermission = Permission::pluck('id');
        $staffRole->givePermissionTo($allPermission);
    }
}
