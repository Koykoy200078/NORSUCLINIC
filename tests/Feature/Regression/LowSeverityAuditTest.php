<?php

namespace Tests\Feature\Regression;

use App\Models\Qualification;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/** The low-severity items of the 2026-10-01 re-audit that were still open. */
class LowSeverityAuditTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    /** R3-L1 */
    public function test_updating_a_doctor_changes_only_the_doctor_form_fields_and_only_their_own_qualifications(): void
    {
        $doctor = $this->makeDoctor(['password' => Hash::make('Original#1'), 'email_verified_at' => now()]);
        $otherDoctor = $this->makeDoctor(['email' => 'other.doctor@test.local']);
        $mine = Qualification::create(['user_id' => $doctor->id, 'degree' => 'MD', 'university' => 'NORSU', 'year' => '2010']);
        $theirs = Qualification::create(['user_id' => $otherDoctor->id, 'degree' => 'MD', 'university' => 'Other U', 'year' => '2011']);
        $oldHash = $doctor->password;

        app(UserRepository::class)->update([
            'first_name' => 'Renamed', 'last_name' => 'Doc', 'email' => $doctor->email, 'gender' => 1, 'status' => 1,
            'specializations' => [], 'qualifications' => json_encode([['id' => $mine->id, 'degree' => 'MD, FPCP', 'university' => 'NORSU', 'year' => '2010', 'user_id' => $otherDoctor->id]]),
            'deletedQualifications' => $theirs->id . ',' . $mine->id,
            // not on the doctor form:
            'password' => 'hacked-plain', 'email_verified_at' => null, 'dark_mode' => 1,
        ], $doctor->doctor);

        $doctor->refresh();
        $this->assertSame('Renamed', $doctor->first_name);
        $this->assertSame($oldHash, $doctor->password, 'the password is not a doctor-form field');
        $this->assertNotNull($doctor->email_verified_at);
        $this->assertNotSame(1, (int) $doctor->dark_mode);

        $this->assertNotNull(Qualification::find($theirs->id), 'another doctor\'s qualification must survive');
        $this->assertNull(Qualification::find($mine->id), 'this doctor\'s own one was asked to be deleted');
    }

    /** R3-L8 */
    public function test_a_patient_name_with_slashes_does_not_break_the_lab_request_pdf(): void
    {
        $nurse = $this->makeStaff('nurse', 'medical_consultation');
        $patient = $this->makePatient();
        $id = DB::table('lab_requests')->insertGetId([
            'request_number' => '777001', 'document_creator_id' => $nurse->id, 'patient_user_id' => $patient->user_id,
            'patient_name' => 'Juan/De\\La Cruz', 'requested_at' => now()->toDateString(), 'status' => 'pending',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($nurse)->get(route('staff.lab-requests.pdf', $id));

        $response->assertOk();
        $this->assertStringContainsString('LabRequest_777001_Juan_De_La_Cruz.pdf', $response->headers->get('Content-Disposition'));
    }

    /** R3-L12 */
    public function test_a_failed_password_change_does_not_flash_the_passwords_back(): void
    {
        $doctor = $this->makeDoctor(['password' => Hash::make('Original#1')]);

        $this->actingAs($doctor)->from(route('doctors.dashboard'))->put(route('user.changePassword'), [
            'current_password' => 'Original#1', 'new_password' => 'Brand#New1', 'confirm_password' => 'does-not-match',
        ])->assertSessionHasErrors();

        $old = session()->getOldInput();
        $this->assertArrayNotHasKey('current_password', $old);
        $this->assertArrayNotHasKey('new_password', $old);
        $this->assertArrayNotHasKey('confirm_password', $old);
    }

    /** R3-L13 */
    public function test_the_badge_colour_helper_works_without_a_signed_in_user(): void
    {
        Auth::logout();

        $this->assertIsString(getBadgeColor(2));
    }

    /** R3-L16 */
    public function test_an_unknown_settings_section_is_not_found_rather_than_a_server_error(): void
    {
        $admin = $this->makeAdmin();
        $this->seed(\Database\Seeders\SettingTableSeeder::class);

        $this->actingAs($admin)->get(route('setting.index', ['section' => 'does-not-exist']))->assertNotFound();
        $this->get(route('setting.index', ['section' => '../../secret']))->assertNotFound();
        $this->get(route('setting.index', ['section' => 'general']))->assertOk();
        $this->get(route('setting.index', ['section' => 'contact-information']))->assertOk();
    }
}
