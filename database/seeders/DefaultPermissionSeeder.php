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
                'name' => 'manage_staff',
                'display_name' => 'Manage Staff',
            ],
            [
                'name' => 'manage_settings',
                'display_name' => 'Manage Settings',
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
                'name' => 'manage_request_documents',
                'display_name' => 'Manage Request Documents',
            ]
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
    }
}
