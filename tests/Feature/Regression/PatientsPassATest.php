<?php

namespace Tests\Feature\Regression;

use App\Livewire\PatientTable;
use App\Models\ActivityLog;
use App\Models\Address;
use App\Models\ConsultationMedicine;
use App\Models\DocumentIssuance;
use App\Models\Patient;
use App\Models\PatientType;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 5, row 5.2 (Patients, Pass A): the list's date filter, the registration save and its welcome e-mail, the
 * archive / restore round trip, the resend-verification endpoint, and the query count of the history page.
 * (Who may do what is in PatientModuleTest; field limits in FormRulesFitColumnsTest; the sort key in ListPagingTest.)
 */
class PatientsPassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        config(['mail.default' => 'array']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Maria', 'last_name' => 'Santos', 'email' => 'maria.santos@test.local',
            'gender' => User::FEMALE, 'dob' => '2004-02-02',
            'patient_type_id' => PatientType::where('code', 'student')->value('id'),
            'university_id_number' => 'S-2026-001', 'nationality_citizenship' => 'Filipino', 'immunization_record' => 'Complete',
        ], $overrides);
    }

    private function registeredAt(string $when): Patient
    {
        $patient = $this->makePatient();
        $patient->forceFill(['created_at' => $when])->saveQuietly();

        return $patient->fresh();
    }

    /** @return array<int, int> ids on the first page of the list after the date filter was set */
    private function idsFor(string $filter): array
    {
        $component = Livewire::withoutLazyLoading()->test(PatientTable::class)->call('changeDateFilter', $filter);

        return $component->instance()->getRows()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
    }

    // ------------------------------------------------------------------ the list's date filter

    public function test_the_date_filter_includes_the_whole_last_day_of_the_range(): void
    {
        $this->actingAs($this->makeAdmin());
        $first = $this->registeredAt('2026-10-01 00:00:00');
        $middle = $this->registeredAt('2026-10-03 12:00:00');
        $lastDay = $this->registeredAt('2026-10-05 15:30:00');
        $lastSecond = $this->registeredAt('2026-10-05 23:59:59');
        $this->registeredAt('2026-10-06 00:00:00');
        $this->registeredAt('2026-09-30 23:59:59');

        $this->assertSame(
            collect([$first, $middle, $lastDay, $lastSecond])->pluck('id')->sort()->values()->all(),
            $this->idsFor('01/10/2026 - 05/10/2026'),
            'from 1 October 00:00 to 5 October 23:59:59'
        );
        $this->assertSame([$lastDay->id, $lastSecond->id], $this->idsFor('05/10/2026 - 05/10/2026'), 'one day');
        $this->assertSame($this->idsFor('01/10/2026 - 05/10/2026'), $this->idsFor('05/10/2026 - 01/10/2026'), 'a range typed backwards is turned round');
    }

    public function test_a_date_filter_that_is_not_a_date_range_is_ignored_instead_of_breaking_the_list(): void
    {
        $this->actingAs($this->makeAdmin());
        $patient = $this->registeredAt('2026-10-03 12:00:00');

        foreach (['not a date', '31/02/2026 - 05/10/2026', '01/10/2026', '01/10/2026 - ', '2026-10-01 - 2026-10-05', "'; drop table patients; --"] as $junk) {
            $this->assertContains($patient->id, $this->idsFor($junk), "filter: {$junk}");
        }
    }

    // ------------------------------------------------------------------ registration

    public function test_the_welcome_mail_is_sent_after_the_patient_is_committed_and_only_when_there_is_an_address(): void
    {
        $levels = [];
        Event::listen(MessageSending::class, function () use (&$levels) {
            $levels[] = DB::transactionLevel();
        });

        $staff = $this->makeNurse();
        $baseline = DB::transactionLevel(); // the test's own transaction

        $this->actingAs($staff)->post(route('staff.patients.store'), $this->payload())->assertRedirect();
        $this->assertSame([$baseline], $levels, 'one mail, sent when the registration is already committed (not inside its transaction)');

        $levels = [];
        $this->actingAs($staff)->post(route('staff.patients.store'), $this->payload(['email' => '', 'university_id_number' => 'S-2026-002']))->assertRedirect();
        $this->assertSame([], $levels, 'no e-mail address, no attempt to send');
        $this->assertSame(2, Patient::count());
    }

    public function test_registration_ignores_fields_that_the_form_never_sends(): void
    {
        $this->actingAs($this->makeNurse())->post(route('staff.patients.store'), $this->payload([
            'type' => User::ADMIN, 'status' => 0, 'archived_at' => '2020-01-01 00:00:00',
            'email_verified_at' => null, 'is_admin' => 1, 'remember_token' => 'x',
        ]))->assertRedirect();

        $user = User::where('email', 'maria.santos@test.local')->firstOrFail();
        $this->assertSame(User::PATIENT, (int) $user->type);
        $this->assertSame(1, (int) $user->status);
        $this->assertNull($user->archived_at);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('patient'));
        $this->assertFalse($user->hasRole('clinic_admin'));
    }

    public function test_a_registration_that_fails_part_way_saves_nothing(): void
    {
        $nurse = $this->makeNurse();
        $users = User::count();
        $this->actingAs($nurse)->post(route('staff.patients.store'), $this->payload(['state_id' => 987654, 'country_id' => 987654]));

        $this->assertSame($users, User::count(), 'no account was left behind');
        $this->assertSame(0, Patient::count());
        $this->assertSame(0, Address::count());
        $this->assertSame(0, User::where('email', 'maria.santos@test.local')->count());
    }

    public function test_a_failed_update_leaves_the_patient_as_it_was(): void
    {
        $patient = $this->makePatient(['first_name' => 'Before']);
        Patient::updating(fn () => throw new \RuntimeException('boom'));

        $this->actingAs($this->makeNurse())->put(route('staff.patients.update', $patient), $this->payload([
            'first_name' => 'After', 'email' => 'before@test.local', 'university_id_number' => 'S-9',
        ]));

        $this->assertSame('Before', $patient->user->fresh()->first_name, 'the account part of the save was rolled back too');
    }

    // ------------------------------------------------------------------ archive / restore

    public function test_archiving_and_restoring_a_patient_round_trips_the_account_the_list_and_the_audit_trail(): void
    {
        $nurse = $this->makeNurse();
        $patient = $this->makePatient(['first_name' => 'Round']);

        $this->actingAs($nurse)->deleteJson(route('staff.patients.destroy', $patient))->assertOk();
        $this->assertNotNull(Patient::withTrashed()->find($patient->id)->archived_at);
        $this->assertNotNull(User::withTrashed()->find($patient->user_id)->archived_at);
        $this->actingAs($nurse)->get(route('staff.patients.showMyHistory', $patient))->assertNotFound();

        $this->actingAs($nurse)->postJson(route('staff.patients.restore', $patient->id))->assertOk();
        $this->assertNull(Patient::find($patient->id)->archived_at);
        $this->assertNull(User::find($patient->user_id)->archived_at);
        $this->actingAs($nurse)->get(route('staff.patients.showMyHistory', $patient))->assertOk();

        $this->assertNotNull(ActivityLog::where('action', 'deleted_patient')->where('subject_id', $patient->id)->first(), 'archiving is in the audit trail');
        $this->assertNotNull(ActivityLog::where('action', 'restored_patient')->where('subject_id', $patient->id)->first(), 'restoring is too');
    }

    // ------------------------------------------------------------------ resend verification

    public function test_resending_the_verification_mail_is_only_for_patient_and_doctor_accounts_with_an_address(): void
    {
        $nurse = $this->makeNurse();
        $this->actingAs($nurse);

        $unverified = $this->makePatient(['email' => 'unverified@test.local', 'email_verified_at' => null])->user;
        $this->postJson(route('staff.resend.email.verification', $unverified->id))->assertOk();

        $this->postJson(route('staff.resend.email.verification', $this->makeAdmin(['email_verified_at' => null])->id))->assertNotFound();
        $this->postJson(route('staff.resend.email.verification', $nurse->id))->assertNotFound();

        $noAddress = $this->makePatient(['email' => null, 'email_verified_at' => null])->user;
        $response = $this->postJson(route('staff.resend.email.verification', $noAddress->id));
        $this->assertLessThan(500, $response->status(), 'a patient without an e-mail address is a refusal, not a crash');
        $response->assertJsonPath('success', false);

        $verified = $this->makePatient(['email' => 'verified@test.local', 'email_verified_at' => now()])->user;
        $this->postJson(route('staff.resend.email.verification', $verified->id))->assertJsonPath('success', false);
    }

    // ------------------------------------------------------------------ privacy of the logs, awkward data, duplicates

    /** @return array<int, string> every message and context value written to the log while $work runs, as text */
    private function logged(callable $work): array
    {
        $lines = [];
        \Illuminate\Support\Facades\Log::listen(function ($event) use (&$lines) {
            $lines[] = $event->message . ' ' . json_encode($event->context);
        });
        $work();

        return $lines;
    }

    public function test_a_refused_or_failed_patient_save_does_not_write_the_patients_data_to_the_log(): void
    {
        $nurse = $this->makeNurse();
        $patient = $this->makePatient(['email' => 'own@test.local', 'university_id_number' => 'S-10']);
        $secret = ['allergies' => 'Zxyallergyzxy', 'comorbidities' => 'Zxycomorbidzxy', 'first_name' => 'Zxyfirstzxy', 'immunization_record' => 'Zxyimmunzxy'];

        // refused by validation (e-mail that is not one)
        $lines = $this->logged(fn () => $this->actingAs($nurse)->put(route('staff.patients.update', $patient), $this->payload($secret + ['email' => 'not-an-email', 'university_id_number' => 'S-10'])));
        $this->assertNotEmpty($lines, 'the refusal is still logged (field names)');

        // failed while saving
        Patient::updating(fn () => throw new \RuntimeException('boom'));
        $lines = array_merge($lines, $this->logged(fn () => $this->actingAs($nurse)->put(route('staff.patients.update', $patient), $this->payload($secret + ['email' => 'own@test.local', 'university_id_number' => 'S-10']))));

        foreach ($secret as $field => $value) {
            $this->assertStringNotContainsString($value, implode("
", $lines), "the value of {$field} must not be in the log");
        }
    }

    public function test_the_pages_of_a_patient_with_awkward_data_open_and_show_it_as_text(): void
    {
        $admin = $this->makeAdmin();
        $patient = $this->makePatient(['first_name' => "O'Brien \"Q\" <b>bold</b> 😀", 'last_name' => 'Ñandú & Co', 'email' => null, 'contact' => null]);
        $patient->address()?->delete();

        $this->actingAs($admin);
        $show = $this->get(route('patients.show', $patient))->assertOk()->getContent();
        $this->get(route('patients.edit', $patient))->assertOk();
        $history = $this->get(route('patients.showMyHistory', $patient))->assertOk()->getContent();

        foreach ([$show, $history] as $html) {
            $this->assertStringNotContainsString('<b>bold</b>', $html, 'markup in a name is shown as text');
            $this->assertStringContainsString('&lt;b&gt;bold&lt;/b&gt;', $html);
        }

        $list = Livewire::withoutLazyLoading()->test(PatientTable::class)->html();
        $this->assertStringNotContainsString('<b>bold</b>', $list);
    }

    public function test_an_update_keeps_the_patients_own_e_mail_and_id_but_refuses_another_patients(): void
    {
        $nurse = $this->makeNurse();
        $mine = $this->makePatient(['email' => 'mine@test.local', 'university_id_number' => 'S-1', 'first_name' => 'Mine']);
        $other = $this->makePatient(['email' => 'other@test.local', 'university_id_number' => 'S-2']);
        $gone = $this->makePatient(['email' => 'gone@test.local', 'university_id_number' => 'S-3']);
        $gone->delete();

        $this->actingAs($nurse)->put(route('staff.patients.update', $mine), $this->payload(['first_name' => 'Renamed', 'email' => 'MINE@test.local', 'university_id_number' => 's-1']))
            ->assertSessionHasNoErrors();
        $this->assertSame('Renamed', $mine->user->fresh()->first_name);
        $this->assertSame('S-1', $mine->user->fresh()->university_id_number, 'stored in upper case');
        $this->assertSame('mine@test.local', $mine->user->fresh()->email, 'stored in lower case');

        foreach ([['other@test.local', 'S-1'], ['mine@test.local', 'S-2'], ['gone@test.local', 'S-1'], ['mine@test.local', 'S-3']] as [$email, $id]) {
            $this->actingAs($nurse)->put(route('staff.patients.update', $mine), $this->payload(['first_name' => 'Hijack', 'email' => $email, 'university_id_number' => $id]))
                ->assertSessionHasErrors();
            $this->assertSame('Renamed', $mine->user->fresh()->first_name, "{$email} / {$id}: nothing was saved");
        }
    }

    public function test_the_forms_refuse_dates_and_ids_the_database_would_not_take_or_that_point_nowhere(): void
    {
        $nurse = $this->makeNurse();
        $mine = $this->makePatient(['email' => 'mine@test.local', 'university_id_number' => 'S-1', 'first_name' => 'Mine']);
        $users = User::count();

        $bad = [
            'dob not a date' => ['dob' => 'not-a-date'],
            'dob in the future' => ['dob' => now()->addDay()->toDateString()],
            'dob before 1900' => ['dob' => '1800-01-01'],
            'dob that does not exist' => ['dob' => '2000-02-31'],
            'gender that is none of the two' => ['gender' => 7],
            'campus that does not exist' => ['campus_id' => 99999],
            'college that does not exist' => ['college_id' => 99999],
            'course that does not exist' => ['course_id' => 99999],
            'year level that does not exist' => ['year_level_id' => 99999],
            'office that does not exist' => ['office_id' => 99999],
            'department that does not exist' => ['department_id' => 99999],
            'province that does not exist' => ['state_id' => 987654],
            'country that does not exist' => ['country_id' => 987654],
            'city that does not exist' => ['city_id' => 987654],
            'barangay that does not exist' => ['barangay_id' => 987654],
        ];

        foreach ($bad as $label => $fields) {
            $this->actingAs($nurse)->post(route('staff.patients.store'), $this->payload(['email' => 'new@test.local', 'university_id_number' => 'S-NEW'] + $fields))
                ->assertSessionHasErrors();
            $this->assertSame($users, User::count(), "register, {$label}: nothing saved");

            $this->actingAs($nurse)->put(route('staff.patients.update', $mine), $this->payload(['first_name' => 'Changed', 'email' => 'mine@test.local', 'university_id_number' => 'S-1'] + $fields))
                ->assertSessionHasErrors();
            $this->assertSame('Mine', $mine->user->fresh()->first_name, "edit, {$label}: nothing saved");
        }

        // and the same form with good values is accepted (so the refusals above are about the values)
        $this->actingAs($nurse)->post(route('staff.patients.store'), $this->payload(['email' => 'new@test.local', 'university_id_number' => 'S-NEW', 'dob' => '2003-05-01', 'gender' => User::MALE]))
            ->assertSessionHasNoErrors();
        $this->assertSame($users + 1, User::count());
    }

    // ------------------------------------------------------------------ the history page

    public function test_the_history_page_does_not_query_once_per_record(): void
    {
        $admin = $this->makeAdmin();
        $nurse = $this->makeNurse();
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();

        $medicine = $this->makeMedicine('Historymed');
        $consultations = [];
        $add = function (int $i) use ($patient, $nurse, $doctor, $medicine, &$consultations) {
            $document = DocumentIssuance::create([
                'document_type' => $i % 3 ? 'consultation_form' : 'medical_certificate', 'document_creator_id' => $nurse->id,
                'user_id' => $patient->user_id, 'name' => 'Juan ' . $i, 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
                'requested_at' => now()->toDateString(), 'nursing_incharged_id' => $nurse->id,
            ]);
            $consultations[] = $document->id;
            foreach (['plan', 'nursing'] as $usedFor) {
                ConsultationMedicine::create(['request_document_id' => $document->id, 'medicine_id' => $medicine->id, 'dosage' => '500mg', 'quantity' => 1, 'used_for' => $usedFor]);
            }
            $prescription = Prescription::create(['patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id, 'status' => 'dispensed', 'is_active' => 1, 'dispensed_by' => $nurse->id]);
            PrescriptionMedicine::create(['prescription_id' => $prescription->id, 'medicine' => $medicine->id, 'dosage' => '500mg', 'total_quantity' => 3]);
        };

        // The compare table is only drawn for consultations picked in the page, so all of them are picked.
        $queries = function () use ($admin, $patient, &$consultations) {
            $url = route('patients.showMyHistory', ['patient' => $patient, 'consultations_to_compare' => $consultations]);
            $this->actingAs($admin)->get($url)->assertOk(); // warms the permission and settings caches
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($admin)->get($url)->assertOk();
            $count = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $count;
        };

        foreach (range(0, 2) as $i) {
            $add($i);
        }
        $few = $queries();
        foreach (range(3, 11) as $i) {
            $add($i);
        }
        $many = $queries();

        $this->assertSame($few, $many, "history page: {$few} queries for 3 records of each kind but {$many} for 12");
    }

    public function test_the_registration_code_no_longer_clears_cache_keys_the_dashboard_stopped_using(): void
    {
        $source = file_get_contents(base_path('app/Repositories/PatientRepository.php'));

        foreach (['admin_dashboard_data_', 'staff_dashboard_data_', 'livewire_admin_dashboard_', 'livewire_staff_dashboard_', 'admin_dashboard_today_patients_'] as $key) {
            $this->assertStringNotContainsString($key, $source, $key);
        }
    }
}
