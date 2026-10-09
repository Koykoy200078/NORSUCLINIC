<?php

namespace Tests\Concerns;

use App\Models\ClinicStation;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\PatientType;
use App\Models\StaffDesignation;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\MedicineInventoryService;
use Database\Seeders\ClinicStationSeeder;
use Database\Seeders\DefaultAssignPermissionSeeder;
use Database\Seeders\DefaultMedicinePermissionSeeder;
use Database\Seeders\DefaultPermissionSeeder;
use Database\Seeders\DefaultRoleSeeder;
use Database\Seeders\PatientTypeSeeder;
use Database\Seeders\RolePermissionsSeeder;
use Database\Seeders\StaffDesignationSeeder;

/**
 * Small builders that create VALID rows (every NOT NULL column filled) - the schema runs in strict SQL
 * mode, so partial fixtures that relied on MySQL coercing missing values no longer work.
 */
trait BuildsClinicData
{
    protected function seedAccessControl(): void
    {
        $this->seed([
            DefaultPermissionSeeder::class,
            DefaultMedicinePermissionSeeder::class,
            DefaultRoleSeeder::class,
            DefaultAssignPermissionSeeder::class,
            RolePermissionsSeeder::class,
            StaffDesignationSeeder::class,
            ClinicStationSeeder::class,
            PatientTypeSeeder::class,
        ]);
    }

    protected function makeAdmin(array $attributes = []): User
    {
        $user = User::create(array_merge($this->baseUser('Admin', 'Tester', User::ADMIN), $attributes));
        $user->assignRole('clinic_admin');

        return $user;
    }

    protected function makeDoctor(array $attributes = []): User
    {
        $user = User::create(array_merge($this->baseUser('Doc', 'Tester', User::DOCTOR), $attributes));
        $user->assignRole('doctor');
        Doctor::create(['user_id' => $user->id, 'prc_license_number' => 'PRC-' . $user->id]);

        return $user->fresh();
    }

    /**
     * A "Staff (Nurse)" account the way the staff form creates it since Phase 3: the `staff` role only, no designation /
     * station / shift profile. What it may do comes from the permissions of the role (Manage User roles).
     */
    protected function makeNurse(array $attributes = []): User
    {
        $user = User::create(array_merge($this->baseUser('Nurse', 'Tester', User::STAFF), $attributes));
        $user->assignRole('staff');

        return $user->fresh();
    }

    /**
     * A staff account from before Phase 3: it also carries the hidden designation / station / shift columns. They are
     * kept for history only and no longer decide anything, so every account is allowed exactly what the `staff` role
     * allows, whatever these arguments are. Prefer {@see makeNurse()} in new tests.
     */
    protected function makeStaff(string $designation = 'clinic_head', string $station = 'front_desk', array $attributes = []): User
    {
        $user = User::create(array_merge($this->baseUser('Staff', 'Tester', User::STAFF), $attributes));
        $user->assignRole('staff');

        StaffProfile::create([
            'user_id' => $user->id,
            'role_designation_id' => StaffDesignation::where('code', $designation)->value('id'),
            'assigned_station_id' => ClinicStation::where('code', $station)->value('id'),
            'shift_schedule' => 'Mon-Fri',
        ]);

        return $user->fresh();
    }

    protected function makePatient(array $userAttributes = [], array $patientAttributes = []): Patient
    {
        $user = User::create(array_merge($this->baseUser('Juan', 'Dela Cruz', User::PATIENT), [
            'dob' => '2003-05-01',
        ], $userAttributes));
        $user->assignRole('patient');

        return Patient::create(array_merge([
            'user_id' => $user->id,
            'patient_unique_id' => 'TEST-' . $user->id,
            'patient_type_id' => PatientType::query()->value('id'),
        ], $patientAttributes));
    }

    protected function makeMedicine(string $name = 'Testmed', array $attributes = []): Medicine
    {
        return Medicine::create(array_merge([
            'name' => $name,
            'category' => 'General',
            'quantity' => 0,
            'available_quantity' => 0,
            'dosage' => '500mg',
        ], $attributes));
    }

    protected function stockIn(Medicine $medicine, int $quantity, string $expiry, string $dosage = '500mg', ?string $batchNumber = null): MedicineBatch
    {
        return app(MedicineInventoryService::class)->recordStockIn([
            'medicine_id' => $medicine->id,
            'quantity' => $quantity,
            'dosage' => $dosage,
            'batch_number' => $batchNumber,
            'expiration_date' => $expiry,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function baseUser(string $first, string $last, int $type): array
    {
        static $counter = 0;
        $counter++;

        return [
            'first_name' => $first,
            'last_name' => $last,
            'email' => strtolower($first) . $counter . '@test.local',
            'password' => 'password',
            'type' => $type,
            'status' => 1,
            'gender' => User::MALE,
            'email_verified_at' => now(),
        ];
    }
}
