<?php

namespace Tests\Feature\Regression;

use App\Models\Setting;
use Database\Seeders\RolePermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * R3-M7: re-running `php artisan db:seed` used to wipe every role and hand each user their own copy of the defaults,
 * undoing what the clinic administrator set in the Roles screen. Defaults are now applied once and remembered.
 */
class RolePermissionsSeederTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function permissionsOf(string $role): array
    {
        return Role::findByName($role, 'web')->permissions()->pluck('name')->sort()->values()->all();
    }

    public function test_a_new_install_gets_the_default_permissions(): void
    {
        $this->assertSame(
            ['manage_doctors', 'manage_medicines', 'manage_patients', 'manage_request_documents', 'manage_specialties', 'manage_staff_dashboard'],
            $this->permissionsOf('staff')
        );
        $this->assertSame(
            ['manage_medicines', 'manage_patients', 'manage_request_documents', 'manage_specialties'],
            $this->permissionsOf('doctor')
        );
        $this->assertSame(Permission::count(), count($this->permissionsOf('clinic_admin')));
    }

    public function test_running_the_seeder_again_keeps_what_the_administrator_changed(): void
    {
        Role::findByName('staff', 'web')->revokePermissionTo('manage_medicines');
        Role::findByName('doctor', 'web')->givePermissionTo('manage_doctors');

        $this->seed(RolePermissionsSeeder::class);
        $this->seed(RolePermissionsSeeder::class);

        $this->assertNotContains('manage_medicines', $this->permissionsOf('staff'), 'a revoked permission must stay revoked');
        $this->assertContains('manage_doctors', $this->permissionsOf('doctor'), 'a permission the administrator added must stay');
    }

    public function test_the_seeder_gives_nobody_a_private_copy_of_the_defaults(): void
    {
        $staff = $this->makeStaff('nurse', 'triage_area');
        $doctor = $this->makeDoctor();

        $this->seed(RolePermissionsSeeder::class);

        $this->assertCount(0, $staff->fresh()->getDirectPermissions());
        $this->assertCount(0, $doctor->fresh()->getDirectPermissions());

        // So revoking from the role really takes it away from them.
        Role::findByName('staff', 'web')->revokePermissionTo('manage_patients');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->assertFalse($staff->fresh()->can('manage_patients'));
    }

    public function test_a_clinic_with_accounts_but_no_record_of_the_defaults_is_left_exactly_as_configured(): void
    {
        $this->makeStaff('nurse', 'triage_area');
        Setting::where('key', 'default_role_permissions_applied')->delete();
        Role::findByName('staff', 'web')->revokePermissionTo(['manage_medicines', 'manage_doctors']);

        $this->seed(RolePermissionsSeeder::class);

        $this->assertSame(['manage_patients', 'manage_request_documents', 'manage_specialties', 'manage_staff_dashboard'], $this->permissionsOf('staff'));
    }

    public function test_a_permission_added_later_reaches_the_default_roles_once_and_the_administrator_can_remove_it(): void
    {
        // Pretend an older release only knew four of the staff defaults.
        $record = json_decode(Setting::where('key', 'default_role_permissions_applied')->value('value'), true);
        $record['staff'] = array_values(array_diff($record['staff'], ['manage_staff_dashboard']));
        Setting::where('key', 'default_role_permissions_applied')->update(['value' => json_encode($record)]);
        Role::findByName('staff', 'web')->revokePermissionTo('manage_staff_dashboard');

        $this->seed(RolePermissionsSeeder::class);
        $this->assertContains('manage_staff_dashboard', $this->permissionsOf('staff'), 'a new default is added the first time');

        Role::findByName('staff', 'web')->revokePermissionTo('manage_staff_dashboard');
        $this->seed(RolePermissionsSeeder::class);
        $this->assertNotContains('manage_staff_dashboard', $this->permissionsOf('staff'), 'and not again after it was removed');
    }
}
