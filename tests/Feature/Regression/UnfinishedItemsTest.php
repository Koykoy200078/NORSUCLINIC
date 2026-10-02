<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\DocumentIssuance;
use App\Models\Illness;
use App\Models\PatientType;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * The "unfinished" rows of docs/STATUS.md section 4: patient-type lookups by code (R3-L14), a clear message for an
 * archived account (pass-1 M-13), unique numbers that survive a race (pass-1 L-05), failed sign-ins in the trail
 * (R3-M5) and a way from the report to the consultations that still need an illness (old consultations).
 */
class UnfinishedItemsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function patientPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Maria', 'last_name' => 'Santos', 'email' => 'maria.santos@test.local',
            'gender' => User::FEMALE, 'dob' => '2004-02-02',
            'patient_type_id' => PatientType::where('code', 'student')->value('id'),
            'university_id_number' => 'S-2026-001', 'nationality_citizenship' => 'Filipino', 'immunization_record' => 'Complete',
        ], $overrides);
    }

    // ---- R3-L14 ------------------------------------------------------------------------------------------------

    public function test_the_lab_request_patient_search_reads_the_affiliation_from_the_patient_type_code(): void
    {
        $nurse = $this->makeStaff('nurse', 'medical_consultation');
        $patient = $this->makePatient(['first_name' => 'Affil', 'last_name' => 'Iation', 'email' => 'affil@test.local']);
        $faculty = PatientType::where('code', 'faculty')->firstOrFail();
        $patient->update(['patient_type_id' => $faculty->id]);

        // The seeder's ids are not a rule: give the type another id (patients follow through the foreign key).
        DB::table('patient_types')->where('id', $faculty->id)->update(['id' => 77]);
        $this->assertSame(77, (int) $patient->fresh()->patient_type_id);

        $this->actingAs($nurse)->getJson(route('staff.lab-requests.search-users', ['query' => 'Affil']))
            ->assertOk()
            ->assertJsonPath('0.status_affiliation', 'faculty');
    }

    public function test_the_guest_exemption_from_the_university_id_follows_the_type_code_not_the_id(): void
    {
        $staff = $this->makeStaff('clinic_staff', 'front_desk');
        $guest = PatientType::where('code', 'guest')->firstOrFail();
        DB::table('patient_types')->where('id', $guest->id)->update(['id' => 88]);

        // a guest needs no university id ...
        $this->actingAs($staff)->post(route('staff.patients.store'), $this->patientPayload([
            'email' => 'guest.one@test.local', 'patient_type_id' => 88, 'university_id_number' => '',
        ]))->assertSessionDoesntHaveErrors()->assertRedirect();

        // ... a student does
        $this->post(route('staff.patients.store'), $this->patientPayload([
            'email' => 'student.one@test.local', 'university_id_number' => '',
        ]))->assertSessionHasErrors();
    }

    // ---- pass-1 M-13 --------------------------------------------------------------------------------------------

    public function test_registering_a_patient_with_the_email_of_an_archived_patient_says_so(): void
    {
        $staff = $this->makeStaff('clinic_staff', 'front_desk');
        $this->actingAs($staff)->post(route('staff.patients.store'), $this->patientPayload())->assertRedirect();
        $old = User::where('email', 'maria.santos@test.local')->firstOrFail();
        $this->deleteJson(route('staff.patients.destroy', $old->patient))->assertOk();

        $this->post(route('staff.patients.store'), $this->patientPayload(['first_name' => 'Marie', 'university_id_number' => 'S-2026-002']))
            ->assertSessionHasErrors();
        $this->assertStringContainsString('belongs to an archived account', (string) collect(session('errors')->getBag('default')->all())->first());

        // the university id of the archived patient is reported the same way
        $this->post(route('staff.patients.store'), $this->patientPayload(['email' => 'other@test.local']))->assertSessionHasErrors();
        $this->assertStringContainsString('archived account', (string) collect(session('errors')->getBag('default')->all())->first());

        // a brand new email and id still work
        $this->post(route('staff.patients.store'), $this->patientPayload(['email' => 'fresh@test.local', 'university_id_number' => 'S-2026-009']))
            ->assertSessionDoesntHaveErrors();
    }

    public function test_editing_a_patient_to_an_archived_patients_email_says_so_but_keeping_ones_own_is_fine(): void
    {
        $staff = $this->makeStaff('clinic_staff', 'front_desk');
        $this->actingAs($staff)->post(route('staff.patients.store'), $this->patientPayload())->assertRedirect();
        $this->post(route('staff.patients.store'), $this->patientPayload(['email' => 'second@test.local', 'university_id_number' => 'S-2026-002', 'first_name' => 'Second']))->assertRedirect();

        $archived = User::where('email', 'maria.santos@test.local')->firstOrFail();
        $second = User::where('email', 'second@test.local')->firstOrFail();
        $this->deleteJson(route('staff.patients.destroy', $archived->patient))->assertOk();

        $payload = fn (array $o) => $this->patientPayload(array_merge([
            'first_name' => 'Second', 'email' => 'second@test.local', 'university_id_number' => 'S-2026-002',
        ], $o));

        $this->put(route('staff.patients.update', $second->patient), $payload(['email' => 'maria.santos@test.local']))->assertSessionHasErrors();
        $this->assertStringContainsString('archived account', (string) collect(session('errors')->getBag('default')->all())->first());

        $this->put(route('staff.patients.update', $second->patient), $payload(['last_name' => 'Renamed']))->assertSessionDoesntHaveErrors();
    }

    // ---- pass-1 L-05 ---------------------------------------------------------------------------------------------

    public function test_a_save_that_loses_the_race_for_a_number_is_retried_and_other_errors_are_not(): void
    {
        $calls = 0;
        $result = retryOnDuplicateKey(function (int $try) use (&$calls) {
            $calls++;
            if ($try < 3) {
                throw $this->duplicateKey();
            }

            return "saved on try {$try}";
        });
        $this->assertSame('saved on try 3', $result);
        $this->assertSame(3, $calls);

        // gives up after the limit and tells the real error
        $this->expectException(QueryException::class);
        retryOnDuplicateKey(fn () => throw $this->duplicateKey(), 2);
    }

    public function test_another_database_error_is_thrown_at_once_without_retrying(): void
    {
        $calls = 0;
        try {
            retryOnDuplicateKey(function () use (&$calls) {
                $calls++;
                throw new QueryException('mysql', 'select 1', [], $this->pdo(1146, 'Table missing'));
            });
            $this->fail('the error should have been thrown');
        } catch (QueryException $e) {
            $this->assertSame(1, $calls);
        }
    }

    public function test_the_database_refuses_two_stock_ins_with_the_same_number(): void
    {
        $insert = fn () => DB::table('medicine_availabilities')->insert(['availability_no' => '555001', 'created_at' => now(), 'updated_at' => now()]);
        $insert();

        $this->expectException(QueryException::class);
        $insert();
    }

    public function test_a_deleted_lab_request_number_is_never_drawn_again(): void
    {
        // the unique index also covers deleted requests, so the generator must skip them too
        $this->assertTrue(
            str_contains(file_get_contents(app_path('helpers.php')), "LabRequest::withTrashed()->where('request_number', \$code)"),
            'generateUniqueLabRequestNumber() must look at deleted requests as well'
        );
    }

    private function duplicateKey(): QueryException
    {
        return new QueryException('mysql', 'insert into x', [], $this->pdo(1062, "Duplicate entry '1' for key 'x'"));
    }

    private function pdo(int $code, string $message): \PDOException
    {
        $e = new \PDOException($message);
        $e->errorInfo = ['23000', $code, $message];

        return $e;
    }

    // ---- R3-M5: failed sign-ins ------------------------------------------------------------------------------------

    public function test_failed_sign_ins_are_logged_with_the_email_but_never_the_password(): void
    {
        $doctor = $this->makeDoctor(['email' => 'real.doc@test.local', 'password' => bcrypt('Right#Pass1')]);

        $this->post(route('login'), ['email' => 'real.doc@test.local', 'password' => 'Wrong#Guess9']);
        $this->post(route('login'), ['email' => 'nobody@test.local', 'password' => 'Wrong#Guess9']);

        $rows = ActivityLog::where('action', 'login_failed')->orderBy('id')->get();
        $this->assertCount(2, $rows);
        $this->assertSame($doctor->id, $rows[0]->user_id, 'a wrong password for a real account is tied to it');
        $this->assertSame('real.doc@test.local', $rows[0]->properties['email']);
        $this->assertNull($rows[1]->user_id);
        $this->assertSame('nobody@test.local', $rows[1]->properties['email']);
        $this->assertStringNotContainsString('Wrong#Guess9', json_encode(ActivityLog::all()->toArray()));
        $this->assertNotNull($rows[0]->ip_address);
    }

    public function test_too_many_attempts_leave_a_blocked_line(): void
    {
        $this->makeDoctor(['email' => 'brute@test.local', 'password' => bcrypt('Right#Pass1')]);

        foreach (range(1, 6) as $i) {
            $this->post(route('login'), ['email' => 'brute@test.local', 'password' => 'Wrong#Guess' . $i]);
        }

        $this->assertSame(5, ActivityLog::where('action', 'login_failed')->count());
        $this->assertGreaterThanOrEqual(1, ActivityLog::where('action', 'login_locked_out')->count());
    }

    // ---- old consultations ---------------------------------------------------------------------------------------

    public function test_the_report_points_at_the_consultations_with_no_illness_and_the_visits_list_offers_to_classify_them(): void
    {
        $admin = $this->makeAdmin();
        $patient = $this->makePatient();
        $headache = Illness::where('name', 'Headache')->firstOrFail();

        $old = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $admin->id, 'user_id' => $patient->user_id,
            'name' => 'Old Visit', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => '2026-03-10', 'consult_mode' => 'physical',
        ]);
        $done = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $admin->id, 'user_id' => $patient->user_id,
            'name' => 'Done Visit', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => '2026-03-11', 'consult_mode' => 'physical',
        ]);
        $done->illnesses()->sync([$headache->id]);

        $this->actingAs($admin);

        // the report links to the visits that are not classified yet ...
        \Livewire\Livewire::withQueryParams(['tab' => 'accomplishment', 'date_from' => '2026-03-01', 'date_to' => '2026-03-31'])
            ->test(\App\Livewire\ReportGeneration::class)
            ->assertSee('Show them and classify')
            ->assertSee('illness_id=none', false);

        // ... where only those are listed, each with a Classify button that opens its edit page
        \Livewire\Livewire::test(\App\Livewire\ReportGeneration::class)
            ->call('setTab', 'visits')
            ->set('illness_id', 'none')
            ->assertSee('Old Visit')
            ->assertDontSee('Done Visit')
            ->assertSee('Classify')
            ->assertSee(route('document-issuances.edit', $old->id), false);
    }
}
