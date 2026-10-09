<?php

namespace Tests\Feature\Regression;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

class RoleModuleAccessTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        config(['permission.cache.store' => 'file', 'permission.cache.key' => 'phase3.test.permissions']);
        app(PermissionRegistrar::class)->initializeCache();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seedAccessControl();
    }

    protected function tearDown(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        parent::tearDown();
    }

    /** @dataProvider modulePermissions */
    public function test_roles_screen_revocation_and_grant_take_effect_on_the_next_page(string $roleName, string $permission, string $module, array $routeNames): void
    {
        $admin = $this->makeAdmin();
        // A Staff (Nurse) account as the staff form creates it now: the role only, no designation / station profile.
        $user = $roleName === 'staff' ? $this->makeNurse() : $this->makeDoctor();
        $role = Role::findByName($roleName);
        $role->givePermissionTo($permission);
        $ids = $role->permissions()->pluck('id')->all();
        $permissionId = Permission::whereName($permission)->value('id');
        $prefix = $roleName === 'staff' ? 'staff.' : 'doctors.';
        $urls = array_map(function ($name) use ($prefix, $roleName, $user) {
            if ($name === 'doctors.index' && $roleName === 'doctor') {
                return route('doctors.doctors.detail', $user->doctor->id);
            }
            return route($prefix.$name, str_contains($name, 'search-users') ? ['query' => 'ab'] : []);
        }, $routeNames);

        $this->actingAs($user->fresh());
        $this->assertTrue(canUseModule($module));
        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
        $menuUrl = $module === 'inventory' ? route($prefix.'medicine-inventory.index') : strtok($urls[0], '?');
        $this->get(route($prefix.'dashboard'))->assertSee($menuUrl, false);

        $this->actingAs($admin)->put(route('roles.update', $role), [
            'display_name' => $role->display_name,
            'permission_id' => array_values(array_diff($ids, [$permissionId])),
        ])->assertSessionHasNoErrors()->assertRedirect(route('roles.index'));

        $this->actingAs($user->fresh());
        $this->assertFalse(canUseModule($module));
        foreach ($urls as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->get(route($prefix.'dashboard'))->assertOk()->assertDontSee($menuUrl, false);

        $this->actingAs($admin)->put(route('roles.update', $role), [
            'display_name' => $role->display_name, 'permission_id' => $ids,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->actingAs($user->fresh());
        $this->assertTrue(canUseModule($module));
        $this->get($urls[0])->assertOk();
        $this->get(route($prefix.'dashboard'))->assertSee($menuUrl, false);
    }

    public static function modulePermissions(): array
    {
        $modules = [
            ['manage_patients', 'patients', ['patients.index', 'patient-queue.index']],
            ['manage_request_documents', 'consultations', ['document-issuances.index', 'prescriptions.index', 'lab-requests.index', 'document-issuances.search-users', 'lab-requests.search-users']],
            ['manage_medicines', 'inventory', ['medicines.index', 'dispense-records.index']],
            ['manage_specialties', 'specializations', ['specializations.index']],
            ['manage_doctors', 'doctors', ['doctors.index']],
        ];
        $cases = [];
        foreach (['staff', 'doctor'] as $role) {
            foreach ($modules as [$permission, $module, $routes]) {
                $cases[$role.' '.$permission] = [$role, $permission, $module, $routes];
            }
        }
        return $cases;
    }

    public function test_a_legacy_designation_and_station_no_longer_limit_a_staff_account(): void
    {
        // The owner's decision: Staff = Nurse, access comes only from the role. These two profiles used to be refused whole
        // modules (a pharmacist had no Patients / Queue / consultations, a front desk had no Inventory / Dispensing).
        $pharmacist = $this->makeStaff('pharmacist', 'pharmacy');
        $frontDesk = $this->makeStaff('clinic_staff', 'front_desk');

        foreach ([$pharmacist, $frontDesk] as $staff) {
            $this->actingAs($staff->fresh());
            foreach (['patients.index', 'patient-queue.index', 'document-issuances.index', 'prescriptions.index', 'lab-requests.index',
                'medicines.index', 'medicine-dispensing.index', 'specializations.index'] as $name) {
                $this->get(route('staff.' . $name))->assertOk();
            }
        }
    }

    public function test_a_doctor_account_without_a_doctor_record_still_gets_every_page_when_the_doctors_permission_is_granted(): void
    {
        // The Doctors menu entry of a doctor opens the doctor's own profile. A doctor-role account whose doctor record is
        // missing (bad data) must not make every page crash: the entry is simply left out.
        $orphan = User::create([
            'first_name' => 'No', 'last_name' => 'Record', 'email' => 'no.record@test.local', 'password' => 'secret1',
            'type' => User::DOCTOR, 'status' => 1, 'gender' => User::MALE, 'email_verified_at' => now(),
        ]);
        $orphan->assignRole('doctor');
        Role::findByName('doctor')->givePermissionTo('manage_doctors');

        $this->actingAs($orphan->fresh())->get(route('doctors.dashboard'))->assertOk()->assertDontSee('doctors/doctors/');
    }

    public function test_roles_filter_irrelevant_permissions_and_allow_every_checkbox_to_be_cleared(): void
    {
        $admin = $this->makeAdmin();
        $staff = $this->makeStaff();
        $role = Role::findByName('staff');
        $this->actingAs($admin)->put(route('roles.update', $role), [
            'display_name' => 'Staff (Nurse)',
            'permission_id' => [Permission::whereName('manage_roles')->value('id')],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame([], $role->fresh()->permissions->pluck('name')->all());
        $role->givePermissionTo('manage_patients');
        $this->put(route('roles.update', $role), ['display_name' => 'Staff (Nurse)'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame([], $role->fresh()->permissions->pluck('name')->all());
        $this->actingAs($staff->fresh())->get(route('staff.dashboard'))->assertOk();
        $this->get(route('staff.activity-logs.index', ['tab' => 'accomplishment']))->assertOk();
        $this->assertTrue(canUseModule('reports'));
        $this->assertTrue(canUseModule('notifications'));
        $this->assertFalse(canUseModule('unknown_module'));
    }

    public function test_the_roles_form_lists_only_the_permissions_that_apply_to_the_role(): void
    {
        $this->actingAs($this->makeAdmin());

        // Staff (Nurse) and Doctor have a panel that uses exactly these five; nothing else is offered (manage_roles,
        // manage_settings ... open no staff or doctor page, so ticking them would change nothing).
        foreach (['staff', 'doctor'] as $roleName) {
            $html = $this->get(route('roles.edit', Role::findByName($roleName)))->assertOk()->getContent();
            preg_match_all('/data-permission="([a-z_]+)"/', $html, $found);

            $this->assertEqualsCanonicalizing(\App\Support\ModuleAccess::CLINICAL_PERMISSIONS, $found[1], $roleName);
            $this->assertStringContainsString('Dashboard, Report Generation and Notifications', $html, $roleName);
        }

        // A new custom role is offered every permission a panel can use.
        $html = $this->get(route('roles.create'))->assertOk()->getContent();
        preg_match_all('/data-permission="([a-z_]+)"/', $html, $found);
        $this->assertEqualsCanonicalizing(array_values(array_unique(array_values(\App\Support\ModuleAccess::PERMISSIONS))), $found[1]);
    }

    public function test_the_explanations_on_the_roles_form_stay_on_screen(): void
    {
        // custom.js slides every .alert up after 5 seconds unless it has no-auto-hide: the notes that explain what a
        // role can and cannot do must not vanish while the administrator is still reading (found in the live check).
        $this->actingAs($this->makeAdmin());

        foreach (['staff', 'doctor', 'clinic_admin', 'patient'] as $name) {
            $html = $this->get(route('roles.edit', Role::findByName($name)))->assertOk()->getContent();
            preg_match_all('/<div class="([^"]*alert-info[^"]*)"/', $html, $found);

            $this->assertNotEmpty($found[1], "{$name}: no explanation box found, the check would prove nothing");
            foreach ($found[1] as $classes) {
                $this->assertStringContainsString('no-auto-hide', $classes, "{$name}: an explanation fades away after 5 seconds");
            }
        }
    }

    public function test_the_reports_global_search_returns_only_records_of_modules_the_role_can_use(): void
    {
        // Report Generation is open to every staff account, but its "Global search" tab is a finder for patients,
        // prescriptions and medicines: it must not return (or link to) the records of a module the role lost.
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient(['first_name' => 'Zoltan', 'last_name' => 'Zebra']);
        \App\Models\Prescription::create([
            'patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id, 'status' => 'pending', 'is_active' => 1,
        ]);
        $this->makeMedicine('Zoltanmycin');
        $nurse = $this->makeNurse();
        $role = Role::findByName('staff');

        $search = function () use ($nurse) {
            $this->actingAs($nurse->fresh());

            return \Livewire\Livewire::withoutLazyLoading()->test(\App\Livewire\ReportGeneration::class)
                ->call('setTab', 'global_search')->set('search', 'Zoltan')->html();
        };

        // The three sections that exist for the role with every permission (so the checks below prove something).
        $html = $search();
        foreach (['Patient Records', 'Prescription Records', 'Inventory Items', 'Zoltan Zebra', 'Zoltanmycin', '/staff/patients/' . $patient->id, '/staff/prescriptions/'] as $expected) {
            $this->assertStringContainsString($expected, $html, "with every permission: {$expected}");
        }

        $cases = [
            'manage_patients' => ['Patient Records', '/staff/patients/' . $patient->id],
            'manage_request_documents' => ['Prescription Records', '/staff/prescriptions/'],
            'manage_medicines' => ['Inventory Items', 'Zoltanmycin'],
        ];
        foreach ($cases as $permission => [$heading, $marker]) {
            $role->revokePermissionTo($permission);
            $html = $search();
            $this->assertStringNotContainsString($heading, $html, "without {$permission}");
            $this->assertStringNotContainsString($marker, $html, "without {$permission}");
            $role->givePermissionTo($permission);
        }
    }

    public function test_a_missing_permission_row_means_no_access_instead_of_an_error_on_every_page(): void
    {
        // The menu asks canUseModule() on every page; spatie throws PermissionDoesNotExist for a permission that is not in
        // the table (an old or hand-edited database). That must read as "not allowed", never as a 500 for the whole panel.
        $nurse = $this->makeNurse();
        Permission::whereName('manage_specialties')->firstOrFail()->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($nurse->fresh());
        $this->assertFalse(canUseModule('specializations'));
        $this->assertTrue(canUseModule('patients'), 'the other modules are not affected');
        $this->get(route('staff.dashboard'))->assertOk()->assertDontSee(route('staff.specializations.index'), false);
    }

    public function test_the_roles_list_hides_the_retired_nurse_role_and_shows_what_the_others_really_have(): void
    {
        Role::findByName('nurse')->update(['display_name' => 'Nurse']);
        $this->actingAs($this->makeAdmin());

        $rows = \Livewire\Livewire::withoutLazyLoading()->test(\App\Livewire\RoleTable::class)->instance()->getRows();
        $names = $rows->pluck('name')->all();

        $this->assertNotContains('nurse', $names);
        $this->assertEqualsCanonicalizing(['clinic_admin', 'staff', 'doctor', 'patient'], $names);

        // The table is lazy on the page, so draw it directly. The staff role's old dashboard permission does nothing for
        // anyone, so it is not listed as if it did; the permissions that matter are listed with their labels.
        $html = \Livewire\Livewire::withoutLazyLoading()->test(\App\Livewire\RoleTable::class)->html();
        $this->assertStringContainsString('Manage Patients', $html, 'the table did not render, the check would prove nothing');
        $this->assertStringContainsString('Dashboard, Reports &amp; Notifications always available', $html);
        $this->assertStringNotContainsString('Staff Dashboard', $html);
        $this->assertStringNotContainsString('Admin Dashboard', $html);
    }

    public function test_admin_and_patient_roles_are_read_only_and_retired_nurse_is_hidden(): void
    {
        $this->actingAs($this->makeAdmin());
        foreach (['clinic_admin', 'patient'] as $name) {
            $role = Role::findByName($name);
            $this->get(route('roles.edit', $role))->assertOk()->assertDontSee('name="permission_id[]"', false)->assertDontSee('type="submit"', false);
            $this->put(route('roles.update', $role), ['display_name' => 'Changed', 'permission_id' => []])->assertForbidden();
            $this->assertSame($name, $role->fresh()->name);
        }
        $nurse = Role::firstOrCreate(['name' => 'nurse', 'guard_name' => 'web'], ['display_name' => 'Nurse']);
        $this->get(route('roles.edit', $nurse))->assertNotFound();
        $this->put(route('roles.update', $nurse), ['display_name' => 'Changed'])->assertForbidden();
    }

    public function test_a_read_only_role_is_refused_by_the_form_request_and_again_by_the_repository(): void
    {
        $this->actingAs($this->makeAdmin());

        foreach (['clinic_admin', 'patient', 'nurse', 'staff', 'doctor'] as $name) {
            $role = Role::findByName($name);
            $readOnly = in_array($name, ['clinic_admin', 'patient', 'nurse'], true);

            // Layer 1: the form request refuses before anything is validated or saved.
            $request = new \App\Http\Requests\UpdateRoleRequest();
            $request->setRouteResolver(fn () => new class($role) {
                public function __construct(private Role $role)
                {
                }

                public function parameter(string $name, mixed $default = null): Role
                {
                    return $this->role;
                }
            });
            $this->assertSame(! $readOnly, $request->authorize(), "{$name}: form request");

            // Layer 2: whoever calls the repository directly is refused as well.
            if ($readOnly) {
                try {
                    app(\App\Repositories\RoleRepository::class)->update(['display_name' => 'Changed', 'permission_id' => []], $role->id);
                    $this->fail("{$name}: the repository changed a read-only role");
                } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                    $this->assertSame(403, $e->getStatusCode(), $name);
                }
                $this->assertNotSame('Changed', $role->fresh()->display_name, $name);
            }
        }
    }

    public function test_staff_form_saves_without_legacy_fields_and_cannot_reassign_a_retired_role(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->get(route('staffs.create'))->assertOk()->assertDontSee('name="role"', false)
            ->assertDontSee('role_designation_id')->assertDontSee('assigned_station_id')->assertDontSee('shift_schedule');
        $input = ['first_name' => 'New', 'last_name' => 'Nurse', 'email' => 'phase3@test.local', 'employee_id' => 'P3-001',
            'gender' => 1, 'password' => 'secret1', 'password_confirmation' => 'secret1'];
        $this->post(route('staffs.store'), $input)->assertSessionHasNoErrors()->assertRedirect();
        $user = User::whereEmail($input['email'])->firstOrFail();
        $this->assertTrue($user->hasExactRoles(['staff']));
        $this->get(route('staffs.edit', $user))->assertOk()->assertDontSee('role_designation_id')->assertDontSee('assigned_station_id')->assertDontSee('shift_schedule');
        $legacy = $this->makeStaff();
        $before = $legacy->staffProfile->getAttributes();
        $this->put(route('staffs.update', $legacy), array_merge($input, [
            'email' => $legacy->email, 'employee_id' => 'P3-002', 'password' => '', 'role_designation_id' => 999999, 'assigned_station_id' => 999999, 'shift_schedule' => 'Changed',
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($before, $legacy->fresh()->staffProfile->getAttributes());
        $nurse = Role::firstOrCreate(['name' => 'nurse', 'guard_name' => 'web'], ['display_name' => 'Nurse']);
        $this->post(route('staffs.store'), array_merge($input, ['email' => 'retired@test.local', 'employee_id' => 'P3-003', 'role' => $nurse->id]))
            ->assertSessionHasErrors();
        $this->assertNull(User::whereEmail('retired@test.local')->first());
    }

    public function test_nurse_migration_preserves_role_grants_profiles_and_archived_accounts(): void
    {
        $admin = $this->makeAdmin();
        $staff = $this->makeStaff();
        $archived = $this->makeStaff();
        $nurse = Role::firstOrCreate(['name' => 'nurse', 'guard_name' => 'web'], ['display_name' => 'Nurse']);
        $nurse->givePermissionTo('manage_medicines');
        $staffRole = Role::findByName('staff');
        $staffRole->revokePermissionTo('manage_patients');
        $before = $staffRole->permissions()->pluck('id')->all();
        $staff->assignRole($nurse);
        $archived->syncRoles($nurse);
        $archived->delete();
        $staff->givePermissionTo('manage_roles');
        $archived->givePermissionTo('manage_patients');
        $admin->givePermissionTo('manage_roles');
        $profile = $archived->staffProfile->getAttributes();

        $migration = require database_path('migrations/2026_10_09_170000_unify_staff_nurse_access.php');
        $migration->up();
        $migration->up();

        $this->assertSame($before, $staffRole->fresh()->permissions()->pluck('id')->all());
        $this->assertSame('Staff (Nurse)', $staffRole->fresh()->display_name);
        $this->assertTrue($staff->fresh()->hasExactRoles(['staff']));
        $archived = User::withTrashed()->findOrFail($archived->id);
        $this->assertTrue($archived->hasExactRoles(['staff']));
        $this->assertSame($profile, $archived->staffProfile->getAttributes());
        $this->assertNotNull($archived->archived_at);
        $this->assertSame(0, $staff->fresh()->permissions()->count());
        $this->assertSame(0, $archived->permissions()->count());
        $this->assertSame(1, $admin->fresh()->permissions()->count());
        $this->assertTrue($nurse->fresh()->hasPermissionTo('manage_medicines'));
        $this->assertSame(0, \DB::table('model_has_roles')->where('role_id', $nurse->id)->count());
    }

    public function test_module_routes_include_their_permission_on_helpers_and_read_aliases(): void
    {
        foreach (['staff', 'doctors'] as $panel) {
            $routes = [
                'patient-queue.index' => 'manage_patients', 'patient-queue.refresh' => 'manage_patients',
                'document-issuances.search-users' => 'manage_request_documents',
                'document-issuances.get-last-consultation' => 'manage_request_documents',
                'document-issuances.get-last-medical-certificate' => 'manage_request_documents',
                'lab-requests.search-users' => 'manage_request_documents',
                'medicines.by.category' => 'manage_request_documents|manage_medicines',
                // The controller checks the module as well; the route must say it too (two independent layers).
                'prescriptions.dispense' => 'manage_medicines',
            ];
            $routes[$panel === 'staff' ? 'doctors.show' : 'doctors.detail'] = 'manage_doctors';
            foreach ($routes as $name => $permission) {
                $route = \Route::getRoutes()->getByName($panel.'.'.$name);
                $this->assertContains('permission:'.$permission, $route->gatherMiddleware(), $panel.'.'.$name);
                foreach ($route->gatherMiddleware() as $middleware) {
                    $this->assertStringNotContainsString('staff.module', $middleware);
                }
            }
        }
    }

    /** @dataProvider clinicalRoles */
    public function test_prescription_dispensing_requires_the_medicine_permission(string $roleName): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();
        $prescription = \App\Models\Prescription::create([
            'patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id, 'status' => 'pending', 'is_active' => 1,
        ]);
        $user = $roleName === 'doctor' ? $doctor : $this->makeStaff();
        Role::findByName($roleName)->revokePermissionTo('manage_medicines');
        $this->actingAs($user->fresh())->post(route(($roleName === 'doctor' ? 'doctors.' : 'staff.').'prescriptions.dispense', $prescription))
            ->assertForbidden();
        $this->assertSame('pending', $prescription->fresh()->status);
    }

    public static function clinicalRoles(): array
    {
        return [['staff'], ['doctor']];
    }
}
