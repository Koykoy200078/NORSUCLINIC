<?php

namespace Tests\Feature\Regression;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * R3-M11: an account that still has the default password ("123456") can reach nothing but its dashboard - for the
 * clinic administrator, doctors and staff/nurse alike (it used to be the administrator only).
 */
class DefaultPasswordGateTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    public function test_a_doctor_with_the_default_password_is_kept_on_the_dashboard(): void
    {
        $doctor = $this->makeDoctor(['password' => Hash::make('123456')]);

        $this->actingAs($doctor)->get(route('doctors.patients.index'))->assertRedirect(route('doctors.dashboard'));
        $this->get(route('doctors.document-issuances.index'))->assertRedirect(route('doctors.dashboard'));
        $this->get(route('doctors.dashboard'))->assertOk();
    }

    public function test_a_nurse_and_other_staff_with_the_default_password_are_kept_on_the_dashboard(): void
    {
        foreach ([$this->makeStaff('nurse', 'triage_area'), $this->makeStaff('clinic_head', 'front_desk')] as $staff) {
            $staff->forceFill(['password' => Hash::make('123456')])->save();

            $this->actingAs($staff)->get(route('staff.patients.index'))->assertRedirect(route('staff.dashboard'));
            $this->get(route('staff.dashboard'))->assertOk();
        }

        $nurseRole = $this->makeStaff('nurse', 'triage_area');
        $nurseRole->syncRoles(['nurse']);
        (require database_path('migrations/2026_10_09_170000_unify_staff_nurse_access.php'))->up();
        $nurseRole->forceFill(['password' => Hash::make('123456')])->save();
        $this->actingAs($nurseRole->fresh())->get(route('staff.patients.index'))->assertRedirect(route('staff.dashboard'));
    }

    public function test_the_administrator_is_still_held_on_the_admin_dashboard(): void
    {
        $admin = $this->makeAdmin(['password' => Hash::make('123456')]);

        $this->actingAs($admin)->get(route('patients.index'))->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_after_the_password_is_changed_everything_opens(): void
    {
        $doctor = $this->makeDoctor(['password' => Hash::make('123456')]);

        $this->actingAs($doctor)->get(route('doctors.patients.index'))->assertRedirect(route('doctors.dashboard'));

        $this->put(route('user.changePassword'), [
            'current_password' => '123456', 'new_password' => 'Better#Pass1', 'confirm_password' => 'Better#Pass1',
        ])->assertSuccessful();

        $this->get(route('doctors.patients.index'))->assertOk();
    }

    public function test_background_requests_and_impersonation_are_not_blocked(): void
    {
        $doctor = $this->makeDoctor(['password' => Hash::make('123456')]);

        // AJAX / Livewire style calls on the dashboard itself must keep working.
        $this->actingAs($doctor)->getJson(route('doctors.patient-queue.refresh'))->assertSuccessful();

        // The administrator acting as this doctor is not stopped by the doctor's password.
        $this->withSession(['impersonated_by' => 1])->get(route('doctors.patients.index'))->assertOk();
    }

    public function test_a_good_password_never_sends_anyone_to_the_dashboard(): void
    {
        $this->actingAs($this->makeDoctor(['password' => Hash::make('Strong#Pass9')]))
            ->get(route('doctors.patients.index'))->assertOk();
        $this->actingAs($this->makeStaff('nurse', 'triage_area'))->get(route('staff.patients.index'))->assertOk();
    }
}
