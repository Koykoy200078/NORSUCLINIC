<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();
        $this->call(DefaultSpecializationSeeder::class);
        $this->call(SettingTableSeeder::class);
        $this->call(CreateCountriesSeeder::class);
        $this->call(DefaultUserSeeder::class);
        $this->call(DefaultPermissionSeeder::class);
        $this->call(DefaultRoleSeeder::class);
        $this->call(DefaultStaffSeeder::class);
        $this->call(DefaultSliderSeeder::class);

        $this->call(AddFieldsSettingTableSeeder::class);
        $this->call(AddTwoFieldsSettingSeeder::class);
        $this->call(AddEmailVerifiedFieldSettingTableSeeder::class);
        $this->call(AddAboutUsImageFieldsSettingSeeder::class);
        $this->call(AddAboutExperienceFieldInSettingSeeder::class);
        $this->call(DefaultMedicinePermissionSeeder::class);
        $this->call(DefaultAssignPermissionSeeder::class);

        // Set default role permissions based on current database state
        $this->call(RolePermissionsSeeder::class);

        $this->call(CampusSeeder::class);
        $this->call(CollegeSeeder::class);
        $this->call(CourseSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(YearLevelSeeder::class);
        $this->call(OfficeSeeder::class);
        $this->call(VaccinationSeeder::class);

        $this->call(MedicineSeeder::class);
        $this->call(DiagnoseSeeder::class);
    }
}
