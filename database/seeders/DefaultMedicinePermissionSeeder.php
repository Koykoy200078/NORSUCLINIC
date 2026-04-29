<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DefaultMedicinePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Note: this seeder only CREATES the permission.
     * Role-permission assignments are handled centrally in
     * RolePermissionsSeeder so they stay in one place.
     */
    public function run(): void
    {
        Permission::firstOrCreate(
            ['name' => 'manage_medicines'],
            ['display_name' => 'Manage Medicines']
        );
    }
}
