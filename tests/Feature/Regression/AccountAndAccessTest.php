<?php

namespace Tests\Feature\Regression;

use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Regression tests for the account-management and access-control fixes of the 2026-09 re-audit
 * (H-01, H-15, H-08, H-14, M-13, C-05, H-10, P2-H1, M-08, P2-C1, P2-H2, M-06, N-01).
 */
class AccountAndAccessTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    /** H-01 */
    public function test_changing_a_password_cannot_rewrite_other_account_fields(): void
    {
        $doctor = $this->makeDoctor(['email' => 'doc@test.local']);

        $this->actingAs($doctor)->putJson(route('user.changePassword'), [
            'current_password' => 'password',
            'new_password' => 'Doc#67890x',
            'confirm_password' => 'Doc#67890x',
            'type' => User::ADMIN,
            'status' => 0,
            'email' => 'evil@test.local',
            'first_name' => 'Hacked',
        ])->assertOk();

        $doctor->refresh();
        $this->assertSame(User::DOCTOR, (int) $doctor->type);
        $this->assertSame(1, (int) $doctor->status);
        $this->assertSame('doc@test.local', $doctor->email);
        $this->assertSame('Doc', $doctor->first_name);
        $this->assertTrue(\Hash::check('Doc#67890x', $doctor->password));
    }

    /** C-06: a password with symbols is no longer mangled by a purifier */
    public function test_passwords_containing_symbols_work_after_they_are_changed(): void
    {
        $staff = $this->makeStaff();

        $this->actingAs($staff)->putJson(route('user.changePassword'), [
            'current_password' => 'password',
            'new_password' => 'My&Pass<1>x',
            'confirm_password' => 'My&Pass<1>x',
        ])->assertOk();

        $this->assertTrue(\Hash::check('My&Pass<1>x', $staff->fresh()->password));
    }

    /** H-15 */
    public function test_staff_management_only_ever_touches_staff_accounts(): void
    {
        $admin = $this->makeAdmin();
        $doctor = $this->makeDoctor();
        $staff = $this->makeStaff();

        $this->actingAs($admin);

        foreach ([$doctor, $admin] as $notStaff) {
            $this->get(route('staffs.edit', $notStaff->id))->assertNotFound();
            $this->get(route('staffs.show', $notStaff->id))->assertNotFound();
            $this->deleteJson(route('staffs.destroy', $notStaff->id))->assertNotFound();
        }

        $this->assertNull($admin->fresh()->archived_at);
        $this->assertSame(User::DOCTOR, (int) $doctor->fresh()->type);

        // A genuine staff reset works (the route parameter used to be named wrongly).
        $this->postJson(route('staffs.reset.password', $staff->id))->assertOk();
        $this->assertTrue(\Hash::check('123456', $staff->fresh()->password));
    }

    /** H-15 */
    public function test_a_staff_account_cannot_be_created_with_the_doctor_role(): void
    {
        $admin = $this->makeAdmin();
        $doctorRoleId = Role::where('name', 'doctor')->value('id');
        $before = User::count();

        $this->actingAs($admin)->post(route('staffs.store'), [
            'first_name' => 'Bad', 'last_name' => 'Role', 'email' => 'badrole@test.local', 'employee_id' => 'E-BAD',
            'password' => 'secret1', 'password_confirmation' => 'secret1', 'gender' => 1,
            'role' => $doctorRoleId, 'role_designation_id' => 1, 'assigned_station_id' => 1, 'shift_schedule' => 'Mon',
        ]);

        $this->assertSame($before, User::count());
    }

    /** H-08 */
    public function test_a_doctor_with_prescriptions_cannot_be_deleted(): void
    {
        $admin = $this->makeAdmin();
        $doctorUser = $this->makeDoctor();
        $patient = $this->makePatient();
        Prescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctorUser->doctor->id,
            'status' => 'pending',
            'is_active' => 1,
        ]);

        $this->actingAs($admin)->deleteJson(route('doctors.destroy', $doctorUser->doctor->id))->assertStatus(422);

        $this->assertNotNull(Doctor::find($doctorUser->doctor->id));
        $this->assertSame(1, Prescription::count());

        // ...and the database refuses too.
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('doctors')->where('id', $doctorUser->doctor->id)->delete();
    }

    /** M-13 */
    public function test_an_archived_staff_email_is_explained_and_can_be_restored(): void
    {
        $admin = $this->makeAdmin();
        $staff = $this->makeStaff('clinic_head', 'front_desk', ['email' => 'front@test.local']);

        $this->actingAs($admin)->deleteJson(route('staffs.destroy', $staff->id))->assertOk();
        $this->assertNotNull($staff->fresh()->archived_at ?? User::withTrashed()->find($staff->id)->archived_at);

        $response = $this->post(route('staffs.store'), [
            'first_name' => 'New', 'last_name' => 'Person', 'email' => 'front@test.local', 'employee_id' => 'E-NEW',
            'password' => 'secret1', 'password_confirmation' => 'secret1', 'gender' => 1,
            'role_designation_id' => \App\Models\StaffDesignation::where('code', 'clinic_head')->value('id'),
            'assigned_station_id' => \App\Models\ClinicStation::where('code', 'front_desk')->value('id'),
            'shift_schedule' => 'Mon',
        ]);
        $response->assertSessionHasErrors();
        $this->assertStringContainsString('archived account', (string) collect(session('errors')->getBag('default')->all())->first());

        $this->artisan('user:restore', ['email' => 'front@test.local'])->assertExitCode(0);
        $restored = User::where('email', 'front@test.local')->firstOrFail();
        $this->assertNull($restored->archived_at);
        $this->assertNotNull($restored->staffProfile, 'designation and station survive the archive');
    }

    /** C-05 */
    public function test_the_log_viewer_is_only_available_to_the_clinic_admin(): void
    {
        $this->getJson('/log-viewer/api/files')->assertUnauthorized();

        $this->actingAs($this->makeDoctor())->getJson('/log-viewer/api/files')->assertForbidden();
        $this->actingAs($this->makeStaff())->getJson('/log-viewer/api/files')->assertForbidden();
        $this->actingAs($this->makeAdmin())->getJson('/log-viewer/api/files')->assertOk();
    }

    /** H-10 */
    public function test_ajax_errors_keep_their_real_http_status(): void
    {
        $frontDesk = $this->makeStaff('clinic_staff', 'front_desk');

        $this->getJson('/admin/dashboard')->assertUnauthorized();

        $this->actingAs($this->makeAdmin())->getJson(route('staffs.edit', 999999))->assertNotFound();

        // Module access denied for an AJAX request is a 403, not a 500.
        $consultation = \App\Models\DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $frontDesk->id, 'user_id' => $frontDesk->id,
            'name' => 'X', 'age' => 1, 'gender' => 'Male', 'address' => 'N/A',
        ]);
        $this->actingAs($frontDesk)->getJson(route('staff.document-issuances.show', $consultation))->assertForbidden();
    }

    /** P2-H1 */
    public function test_renaming_a_role_label_does_not_change_its_internal_name(): void
    {
        $admin = $this->makeAdmin();
        $role = Role::where('name', 'clinic_admin')->firstOrFail();
        $permissionIds = $role->permissions()->pluck('id')->all();

        $this->actingAs($admin)->put(route('roles.update', $role->id), [
            'display_name' => 'Clinic Administrator',
            'permission_id' => $permissionIds,
        ]);

        $this->assertSame('clinic_admin', $role->fresh()->name);
        $this->actingAs($admin)->get(route('roles.index'))->assertOk();
    }

    /** M-08 */
    public function test_impersonation_can_be_left_and_never_targets_an_admin(): void
    {
        $admin = $this->makeAdmin();
        $otherAdmin = $this->makeAdmin();
        $doctor = $this->makeDoctor();

        $this->actingAs($admin)->get(route('impersonate', $doctor->id))->assertStatus(405);

        $this->post(route('impersonate', $otherAdmin->id))->assertRedirect();
        $this->assertSame($admin->id, auth()->id(), 'an administrator cannot be impersonated');

        $this->post(route('impersonate', $doctor->id))->assertRedirect(route('doctors.dashboard'));
        $this->assertSame($doctor->id, auth()->id());

        $this->get(route('impersonate.leave'))->assertRedirect(route('admin.dashboard'));
        $this->assertSame($admin->id, auth()->id());
    }

    /** P2-C1 */
    public function test_urls_stay_http_in_production_unless_force_https_is_switched_on(): void
    {
        config(['app.env' => 'production', 'app.force_https' => false]);
        (new \App\Providers\AppServiceProvider($this->app))->boot();
        $this->assertStringStartsWith('http://', url('/login'));

        config(['app.force_https' => true]);
        (new \App\Providers\AppServiceProvider($this->app))->boot();
        $this->assertStringStartsWith('https://', url('/login'));
    }

    /** N-01 */
    public function test_a_nurse_role_account_is_treated_as_staff_for_route_selection(): void
    {
        $nurse = $this->makeStaff();
        $nurse->syncRoles(['nurse']);

        $this->actingAs($nurse);
        $this->assertTrue(isRole('staff'));
        $this->assertTrue(isRole('nurse'));
        $this->assertFalse(isRole('doctor'));
    }

    /** M-06 */
    public function test_the_debug_route_is_gone_and_the_queue_api_needs_a_signed_in_staff_member(): void
    {
        $this->actingAs($this->makeAdmin())->get('/debug-profile-data')->assertNotFound();

        $patient = $this->makePatient();
        auth()->logout();
        $this->getJson("/api/patient/{$patient->id}/latest-consultation")->assertUnauthorized();

        $this->actingAs($this->makeStaff('clinic_head', 'front_desk'))
            ->getJson("/api/patient/{$patient->id}/latest-consultation")
            ->assertOk();
    }

    /** P2-H2 */
    public function test_seeders_can_be_run_again_without_duplicating_or_overwriting_anything(): void
    {
        $this->seed();
        DB::table('users')->where('email', 'admin@norsuclinic.com')->update(['password' => 'CUSTOM-HASH']);
        DB::table('settings')->where('key', 'clinic_name')->update(['value' => 'My Edited Clinic']);

        $tables = ['settings', 'users', 'specializations', 'roles', 'permissions', 'campuses', 'colleges', 'courses',
            'vaccinations', 'diagnoses', 'lab_tests', 'departments', 'offices', 'sliders', 'patient_types'];
        $before = collect($tables)->mapWithKeys(fn ($t) => [$t => DB::table($t)->count()])->all();

        $this->seed();

        $after = collect($tables)->mapWithKeys(fn ($t) => [$t => DB::table($t)->count()])->all();
        $this->assertSame($before, $after);
        $this->assertSame('CUSTOM-HASH', DB::table('users')->where('email', 'admin@norsuclinic.com')->value('password'));
        $this->assertSame('My Edited Clinic', DB::table('settings')->where('key', 'clinic_name')->value('value'));
    }
}
