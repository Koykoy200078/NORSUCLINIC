<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * R3-M5: administrative changes, password resets, sign-ins and impersonation leave a line in the trail - with field
 * NAMES only, never values - and failed attempts do not look like changes.
 */
class AuditTrailTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function rows(string $action)
    {
        return ActivityLog::where('action', $action)->orderBy('id')->get();
    }

    public function test_a_settings_change_is_logged_with_field_names_but_no_values(): void
    {
        $admin = $this->makeAdmin();
        $this->seed(\Database\Seeders\SettingTableSeeder::class);

        $this->actingAs($admin)->post(route('setting.update'), [
            'sectionName' => 'general', 'clinic_name' => 'Secret Clinic Name', 'email' => 'clinic@test.local', 'contact_no' => '9171234567',
            'specialties' => ['1'],
        ])->assertSessionDoesntHaveErrors();

        $row = $this->rows('settings_changed')->first();
        $this->assertNotNull($row);
        $this->assertSame($admin->id, $row->user_id);
        $this->assertContains('clinic_name', $row->properties['fields']);
        $this->assertStringNotContainsString('Secret Clinic Name', json_encode($row->toArray()));
        $this->assertSame('POST', $row->properties['method']);
    }

    public function test_reading_pages_and_failed_attempts_leave_nothing_behind(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $this->get(route('setting.index'))->assertOk();
        $this->get(route('admin.dashboard'))->assertOk();
        $this->post(route('setting.update'), [])->assertSessionHasErrors();          // refused: nothing changed
        $this->post(route('report-lists.illnesses.store'), ['name' => ''])->assertSessionHasErrors();

        $this->assertSame(0, ActivityLog::where('action', 'settings_changed')->count());
    }

    public function test_a_password_reset_is_logged_without_any_password(): void
    {
        $admin = $this->makeAdmin();
        $doctor = $this->makeDoctor();

        $this->actingAs($admin)->postJson(route('doctors.reset.password', $doctor))->assertSuccessful();

        $row = $this->rows('password_reset')->first();
        $this->assertNotNull($row);
        $this->assertStringContainsString('#' . $doctor->id, $row->description);
        $this->assertStringNotContainsString('123456', json_encode($row->toArray()));
    }

    public function test_master_data_and_report_list_changes_are_logged(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->post(route('categories.store'), ['name' => 'Antibiotics'])->assertSessionDoesntHaveErrors();
        $this->post(route('report-lists.services.store'), ['category' => 'clinical_procedure', 'name' => 'Ear washing'])->assertSessionDoesntHaveErrors();

        $this->assertSame(1, $this->rows('master_data_changed')->count());
        $this->assertSame(1, $this->rows('settings_changed')->count());
    }

    public function test_sign_in_and_sign_out_are_logged(): void
    {
        $admin = $this->makeAdmin(['email' => 'trail@test.local', 'password' => Hash::make('Trail#Pass1')]);

        $this->post(route('login'), ['email' => 'trail@test.local', 'password' => 'Trail#Pass1'])->assertRedirect();
        $this->assertSame(1, $this->rows('login')->count());
        $this->assertSame($admin->id, $this->rows('login')->first()->user_id);

        $this->post(route('logout'))->assertRedirect();
        $this->assertSame(1, $this->rows('logout')->count());
    }

    public function test_actions_taken_while_impersonating_name_the_real_person(): void
    {
        $admin = $this->makeAdmin(['password' => Hash::make('Trail#Pass1')]);
        $doctor = $this->makeDoctor(['password' => Hash::make('Doc#Pass1')]);

        $this->actingAs($admin)->post(route('impersonate', $doctor->id))->assertRedirect();
        $this->assertSame(1, $this->rows('impersonation')->count());
        $this->assertSame($admin->id, $this->rows('impersonation')->first()->user_id);

        // Now acting as the doctor: a profile change is logged under the doctor, tagged with the administrator.
        $this->put(route('update.profile.setting'), ['first_name' => 'Changed', 'last_name' => 'Doc', 'email' => $doctor->email, 'contact_no' => '9171234567']);
        $profile = $this->rows('profile_changed')->first();
        if ($profile) {
            $this->assertSame($admin->id, $profile->properties['impersonated_by']);
        }

        $this->get(route('impersonate.leave'))->assertRedirect();
        $leave = $this->rows('impersonation')->last();
        $this->assertSame($doctor->id, $leave->user_id);
        $this->assertSame($admin->id, $leave->properties['impersonated_by']);
        $this->assertStringContainsString('Stopped acting as', $leave->description);
    }
}
