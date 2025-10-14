<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DefaultPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'manage_doctors',
                'display_name' => 'Manage Doctors',
            ],
            [
                'name' => 'manage_patients',
                'display_name' => 'Manage Patients',
            ],
            [
                'name' => 'manage_patient_queues',
                'display_name' => 'Manage Patient Queue',
            ],
            [
                'name' => 'manage_patient_visits',
                'display_name' => 'Manage Patient Visits',
            ],
            [
                'name' => 'manage_staff',
                'display_name' => 'Manage Staff',
            ],
            [
                'name' => 'manage_doctor_sessions',
                'display_name' => 'Manage Doctor Sessions',
            ],
            [
                'name' => 'manage_settings',
                'display_name' => 'Manage Settings',
            ],
            [
                'name' => 'manage_services',
                'display_name' => 'Manage Services',
            ],
            [
                'name' => 'manage_specialties',
                'display_name' => 'Manage Specialties',
            ],
            [
                'name' => 'manage_countries',
                'display_name' => 'Manage Countries',
            ],
            [
                'name' => 'manage_states',
                'display_name' => 'Manage States',
            ],
            [
                'name' => 'manage_cities',
                'display_name' => 'Manage Cities',
            ],
            [
                'name' => 'manage_roles',
                'display_name' => 'Manage Roles',
            ],
            [
                'name' => 'manage_currencies',
                'display_name' => 'Manage Currencies',
            ],
            [
                'name' => 'manage_admin_dashboard',
                'display_name' => 'Manage Admin Dashboard',
            ],
            [
                'name' => 'manage_staff_dashboard',
                'display_name' => 'Manage Staff Dashboard',
            ],
            [
                'name' => 'manage_front_cms',
                'display_name' => 'Manage Front CMS',
            ],
            [
                'name' => 'manage_transactions',
                'display_name' => 'Manage Transactions',
            ],
            [
                'name' => 'manage_request_documents',
                'display_name' => 'Manage Request Documents',
            ]
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
    }
}
