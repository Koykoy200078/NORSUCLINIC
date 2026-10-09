<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Plan Phase 3.1: Staff = Nurse, one role.
 *
 *  - the Staff role is labelled "Staff (Nurse)";
 *  - every account holding the retired `nurse` role (from `2026_04_22_094900_add_nurse_role_and_permissions`) moves to
 *    `staff`; the `nurse` role itself stays in the table, marked as a default role so the Roles screen can never delete
 *    it, and the screen and the staff form hide it;
 *  - role permissions, staff profiles (the hidden designation / station / shift columns) and archived accounts are left
 *    exactly as they are;
 *  - a permission given straight to a non-admin user is removed: the role is the only source of access, otherwise
 *    taking a permission away in Manage User roles would silently not apply to that person;
 *  - a permission without a label (manage_patients and manage_request_documents at the clinic) gets one, so the Roles
 *    screen is not blank.
 *
 * A fresh install has no `staff` role yet (DefaultRoleSeeder creates it, already labelled): nothing is created here,
 * otherwise the seeder and every test that creates the role would fail with "already exists".
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $staffId = $this->roleId('staff');
            $nurseId = $this->roleId('nurse');
            $nurseAccounts = $nurseId
                ? DB::table('model_has_roles')->where('role_id', $nurseId)->get()
                : collect();

            // Nurse accounts need a role to move to (a database that lost its staff role).
            if (! $staffId && $nurseAccounts->isNotEmpty()) {
                $staffId = DB::table('roles')->insertGetId([
                    'name' => 'staff', 'display_name' => 'Staff (Nurse)', 'guard_name' => 'web',
                    'is_default' => true, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            if ($staffId) {
                DB::table('roles')->where('id', $staffId)->update(['display_name' => 'Staff (Nurse)']);
            }

            if ($nurseId) {
                foreach ($nurseAccounts as $assignment) {
                    DB::table('model_has_roles')->insertOrIgnore([
                        'role_id' => $staffId, 'model_type' => $assignment->model_type, 'model_id' => $assignment->model_id,
                    ]);
                }
                DB::table('model_has_roles')->where('role_id', $nurseId)->delete();
                DB::table('roles')->where('id', $nurseId)->update(['is_default' => true]);
            }

            // Preserve role grants and historical staff profiles; only retire direct user grants.
            $adminId = $this->roleId('clinic_admin');
            $userType = (new User())->getMorphClass();
            DB::table('model_has_permissions')->where('model_type', $userType)
                ->whereNotIn('model_id', DB::table('model_has_roles')->select('model_id')
                    ->where('model_type', $userType)->where('role_id', $adminId))
                ->delete();
        });

        // The clinic database has a few permissions with an empty label (an older migration created them without one):
        // the Roles screen showed empty badges and check boxes. A label somebody chose is never touched.
        foreach (DB::table('permissions')->where(fn ($query) => $query->whereNull('display_name')->orWhere('display_name', ''))->get(['id', 'name']) as $permission) {
            DB::table('permissions')->where('id', $permission->id)
                ->update(['display_name' => ucwords(str_replace('_', ' ', $permission->name))]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Retiring an access policy cannot safely recreate former account grants.
    }

    private function roleId(string $name): ?int
    {
        $id = DB::table('roles')->where('name', $name)->where('guard_name', 'web')->value('id');

        return $id === null ? null : (int) $id;
    }
};
