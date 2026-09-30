<?php

namespace Tests\Feature\Regression;

use App\Models\Doctor;
use App\Models\PatientType;
use App\Models\Setting;
use App\Models\Specialization;
use App\Models\StaffDesignation;
use App\Models\User;
use Database\Seeders\DefaultCountryCode;
use Database\Seeders\SettingTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Philippine-only (+63) phone numbers, replacing intl-tel-input (Phase E of the 2026-09 remediation).
 */
class PhilippinePhoneTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function staffPayload(array $overrides = []): array
    {
        static $n = 0;
        $n++;

        return array_merge([
            'first_name' => 'New', 'last_name' => 'Person' . $n, 'email' => "newstaff{$n}@test.local", 'employee_id' => "E-PH-{$n}",
            'password' => 'secret1', 'password_confirmation' => 'secret1', 'gender' => 1,
            'role_designation_id' => StaffDesignation::where('code', 'clinic_head')->value('id'),
            'assigned_station_id' => \App\Models\ClinicStation::where('code', 'front_desk')->value('id'),
            'shift_schedule' => 'Mon',
        ], $overrides);
    }

    /** The app's handler flattens validation failures to their first message (shown as a flash). */
    private function firstError(): string
    {
        return (string) session('errors')?->getBag('default')->first();
    }

    public function test_a_staff_number_is_stored_as_national_digits_with_country_code_63(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('staffs.store'), $this->staffPayload(['contact' => '+63 (917) 123-4567', 'country_code' => '1']))
            ->assertSessionDoesntHaveErrors();

        $staff = User::where('employee_id', 'like', 'E-PH-%')->firstOrFail();
        $this->assertSame('9171234567', $staff->contact);
        $this->assertSame('63', $staff->country_code, 'the country is fixed - a client-supplied code is ignored');
    }

    public function test_local_and_international_spellings_are_the_same_number_for_the_unique_check(): void
    {
        $admin = $this->makeAdmin();
        $this->makeStaff('clinic_head', 'front_desk', ['contact' => '9171234567', 'country_code' => '63']);

        foreach (['0917 123 4567', '+63 917 123 4567', '63-917-1234567'] as $spelling) {
            $this->actingAs($admin)
                ->post(route('staffs.store'), $this->staffPayload(['contact' => $spelling]))
                ->assertSessionHasErrors();
            $this->assertStringContainsString('already been taken', $this->firstError(), $spelling);
        }
    }

    public function test_numbers_that_are_not_philippine_are_rejected_with_a_clear_message(): void
    {
        $admin = $this->makeAdmin();
        $before = User::count();

        foreach (['+1 415 555 2671', '12345', 'call me', '+44 20 7946 0958'] as $bad) {
            $this->actingAs($admin)->post(route('staffs.store'), $this->staffPayload(['contact' => $bad]))->assertSessionHasErrors();
            $this->assertStringContainsString('Philippine number', $this->firstError(), $bad);
        }

        $this->assertSame($before, User::count());
    }

    public function test_the_contact_number_stays_optional_for_staff(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('staffs.store'), $this->staffPayload(['contact' => '']))
            ->assertSessionDoesntHaveErrors();

        $this->assertNull(User::where('employee_id', 'like', 'E-PH-%')->firstOrFail()->contact);
    }

    public function test_doctor_accounts_use_the_same_number_format(): void
    {
        $admin = $this->makeAdmin();
        $specialization = Specialization::firstOrCreate(['name' => 'General Medicine']);

        $payload = [
            'first_name' => 'Docu', 'last_name' => 'Phone', 'email' => 'docphone@test.local', 'employee_id' => 'E-DOC-PH',
            'password' => 'secret1', 'password_confirmation' => 'secret1', 'gender' => 1,
            'prc_license_number' => 'PRC-PH-1', 'ptr_number' => 'PTR-1', 'consultation_hours' => '8-5',
            'specializations' => [$specialization->id],
            'contact' => '0918 222 3333',
        ];

        $this->actingAs($admin)->post(route('doctors.store'), $payload)->assertSessionDoesntHaveErrors();

        $doctorUser = User::where('email', 'docphone@test.local')->firstOrFail();
        $this->assertSame('9182223333', $doctorUser->contact);
        $this->assertSame('63', $doctorUser->country_code);

        // Editing keeps the number valid and normalised.
        $doctor = Doctor::where('user_id', $doctorUser->id)->firstOrFail();
        $this->actingAs($admin)->put(route('doctors.update', $doctor->id), array_merge($payload, [
            'password' => null, 'password_confirmation' => null, 'contact' => '+63 2 8123 4567',
        ]))->assertSessionDoesntHaveErrors();
        $this->assertSame('281234567', $doctorUser->fresh()->contact);

        $this->actingAs($admin)->put(route('doctors.update', $doctor->id), array_merge($payload, [
            'password' => null, 'password_confirmation' => null, 'contact' => '555-0100',
        ]))->assertSessionHasErrors();
        $this->assertStringContainsString('Philippine number', $this->firstError());
        $this->assertSame('281234567', $doctorUser->fresh()->contact);
    }

    public function test_patient_and_emergency_numbers_are_normalised_and_the_quick_add_uses_the_same_rules(): void
    {
        $admin = $this->makeAdmin();

        $payload = [
            'first_name' => 'Pat', 'last_name' => 'Phone', 'gender' => 1, 'dob' => '2000-01-01',
            'patient_type_id' => PatientType::query()->value('id'),
            'nationality_citizenship' => 'Filipino', 'immunization_record' => 'complete', 'university_id_number' => 'PH-2026-001',
            'contact' => '09171234567', 'emergency_contact_name' => 'Mama', 'emergency_contact_no' => '0918-111-2222',
            'emergency_relationship' => 'Mother',
        ];

        $this->actingAs($admin)->post(route('patients.store'), $payload)->assertSessionDoesntHaveErrors();

        $patient = User::where('last_name', 'Phone')->firstOrFail();
        $this->assertSame('9171234567', $patient->contact);
        $this->assertSame('63', $patient->country_code);
        $this->assertSame('+639181112222', $patient->emergency_contact_no);

        $this->actingAs($admin)->post(route('patients.store'), array_merge($payload, [
            'first_name' => 'Bad', 'last_name' => 'Number', 'university_id_number' => 'PH-2026-002', 'emergency_contact_no' => 'mother',
        ]))->assertSessionHasErrors();
        $this->assertStringContainsString('Philippine number', $this->firstError());
        $this->assertNull(User::where('last_name', 'Number')->first());

        $this->postJson(route('dispense-records.store-patient'), array_merge($payload, [
            'first_name' => 'Quick', 'last_name' => 'Add', 'email' => 'quick@test.com', 'university_id_number' => 'PH-2026-003',
            'contact' => '+1 212 555 0100',
        ]))->assertStatus(422)->assertJsonPath('message', __('messages.invalid_ph_number'));
    }

    public function test_the_profile_page_saves_the_number_in_the_same_format(): void
    {
        $doctor = $this->makeDoctor(['email' => 'doc.profile@test.com']);

        $payload = ['first_name' => 'Doc', 'last_name' => 'Tester', 'email' => $doctor->email, 'time_zone' => 'Asia/Manila'];

        $this->actingAs($doctor)->put(route('update.profile.setting'), $payload + [
            'contact' => '+63 917 555 0101', 'emergency_contact_no' => '0919 000 1111',
        ]);

        $doctor->refresh();
        $this->assertSame('9175550101', $doctor->contact);
        $this->assertSame('63', $doctor->country_code);
        $this->assertSame('+639190001111', $doctor->emergency_contact_no);

        $this->put(route('update.profile.setting'), $payload + ['contact' => '12345'])->assertSessionHasErrors();
        $this->assertStringContainsString('Philippine number', $this->firstError());
        $this->assertSame('9175550101', $doctor->fresh()->contact);
    }

    public function test_the_clinic_contact_number_and_country_are_fixed_to_the_philippines(): void
    {
        $this->seed([SettingTableSeeder::class, DefaultCountryCode::class]);
        Setting::where('key', 'default_country_code')->update(['value' => 'us']);
        Setting::where('key', 'country_code')->update(['value' => '1']);

        $payload = [
            'sectionName' => 'general', 'email' => 'clinic@test.local', 'specialties' => [1], 'clinic_name' => 'NORSU Clinic',
            'language' => 'en',
        ];

        $this->actingAs($this->makeAdmin())->post(route('setting.update'), $payload + [
            'contact_no' => '0917 123 4567', 'country_code' => '1', 'default_country_code' => 'us',
        ])->assertSessionDoesntHaveErrors();

        $this->assertSame('9171234567', Setting::where('key', 'contact_no')->value('value'));
        $this->assertSame('63', Setting::where('key', 'country_code')->value('value'));
        $this->assertSame('ph', Setting::where('key', 'default_country_code')->value('value'));

        $this->post(route('setting.update'), $payload + ['contact_no' => '+1 212 555 0100'])->assertSessionHasErrors();
        $this->assertStringContainsString('Philippine number', $this->firstError());
        $this->assertSame('9171234567', Setting::where('key', 'contact_no')->value('value'));
    }

    public function test_consultation_forms_keep_philippine_numbers_tidy_but_never_reject_free_text(): void
    {
        $doctor = $this->makeDoctor();
        $nurse = $this->makeStaff('nurse', 'triage_area');

        $payload = fn (array $extra) => array_merge([
            'document_type' => 'consultation_form', 'requested_at' => now()->toDateString(), 'consult_mode' => 'physical',
            'complaints' => 'Cough', 'name' => 'Ana Reyes', 'date_of_birth' => '2001-02-03', 'gender' => 'Female',
            'nursing_incharged' => $nurse->id,
        ], $extra);

        // A new walk-in patient typed with a local number: the record shows +63 form, the account gets canonical digits.
        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $payload([
            'patient_contact' => '0917 555 0199', 'emergency_contact' => 'Maria Reyes / 0918 000 1234 (Mother)',
        ]))->assertRedirect();

        $document = \App\Models\DocumentIssuance::firstOrFail();
        $this->assertSame('+63 917 555 0199', $document->patient_contact);

        $account = User::where('first_name', 'Ana')->where('last_name', 'Reyes')->firstOrFail();
        $this->assertSame('9175550199', $account->contact);
        $this->assertSame('63', $account->country_code);
        $this->assertSame('+639180001234', $account->emergency_contact_no);

        // A walk-in without a phone: what the doctor typed is kept, and nothing is rejected.
        $this->post(route('doctors.document-issuances.store'), $payload([
            'name' => 'Ben Cruz', 'patient_contact' => 'N/A',
        ]))->assertRedirect()->assertSessionDoesntHaveErrors();

        $this->assertSame('N/A', \App\Models\DocumentIssuance::where('name', 'Ben Cruz')->value('patient_contact'));
    }

    public function test_the_normalise_command_repairs_old_numbers_and_can_be_repeated(): void
    {
        $legacy = $this->makePatient(['contact' => '09171234567', 'country_code' => null, 'emergency_contact_no' => '09170000000']);
        $keep = $this->makePatient([], []);
        $foreign = $this->makeStaff('clinic_head', 'front_desk', ['contact' => 'N/A']);

        // Dry run changes nothing.
        $this->artisan('phone:normalize')->assertExitCode(0);
        $this->assertSame('09171234567', DB::table('users')->where('id', $legacy->user_id)->value('contact'));

        $this->artisan('phone:normalize', ['--apply' => true])->assertExitCode(0);

        $row = DB::table('users')->where('id', $legacy->user_id)->first();
        $this->assertSame('9171234567', $row->contact);
        $this->assertSame('63', $row->country_code);
        $this->assertSame('+639170000000', $row->emergency_contact_no);
        $this->assertSame('N/A', DB::table('users')->where('id', $foreign->id)->value('contact'), 'unreadable values are reported, never guessed');

        $this->artisan('phone:normalize', ['--apply' => true])->expectsOutputToContain('Updated 0 record(s)')->assertExitCode(0);
    }
}
