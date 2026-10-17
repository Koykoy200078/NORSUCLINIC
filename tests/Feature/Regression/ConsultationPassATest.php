<?php

namespace Tests\Feature\Regression;

use App\Livewire\DocumentIssuanceTable;
use App\Models\ConsultationMedicine;
use App\Models\DocumentIssuance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 5, row 5.4 (Consultations, Pass A): "incomplete" means the same thing on every screen and the sidebar badge
 * follows at once; the form refuses dates and ids the database would not take; the pages open for records with missing or
 * awkward data and do not query once per medicine. (Who may do what: ConsultationAccessTest / ConsultationModuleTest;
 * stock: ConsultationInventoryTest; field limits: ConsultationFieldLimitsTest; PDFs: PdfNastyDataTest.)
 */
class ConsultationPassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function consultation(User $creator, array $overrides = []): DocumentIssuance
    {
        $patient = $this->makePatient();

        return DocumentIssuance::create(array_merge([
            'document_type' => 'consultation_form', 'document_creator_id' => $creator->id, 'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
            'requested_at' => now()->toDateString(), 'consult_mode' => 'physical', 'complaints' => 'Cough',
            'assessment' => 'Cough', 'plan' => 'Rest',
        ], $overrides));
    }

    /** The number the sidebar shows on "Consultations" (the "need Assessment / Plan" warning), 0 when there is none. */
    private function badge(User $user): int
    {
        $html = $this->actingAs($user)->get(route('doctors.dashboard'))->assertOk()->getContent();

        return preg_match('/title="(\d+) consultation form\(s\) need Assessment\/Plan"/', $html, $m) ? (int) $m[1] : 0;
    }

    // ------------------------------------------------------------------ "incomplete" means one thing

    public function test_incomplete_means_the_same_on_the_badge_the_list_and_the_scope_and_the_badge_is_fresh(): void
    {
        $doctor = $this->makeDoctor();
        $this->consultation($doctor);                                                   // complete
        $blankAssessment = $this->consultation($doctor, ['assessment' => "  \t "]);     // only spaces: incomplete
        $this->consultation($doctor, ['plan' => '0']);                                  // "0" is something that was written: complete
        $noPlan = $this->consultation($doctor, ['plan' => null]);                       // incomplete
        $this->consultation($doctor, ['plan' => '', 'document_type' => 'medical_certificate']); // not a consultation form: never counted

        $this->assertSame(2, DocumentIssuance::incompleteConsultations()->count(), 'the scope');
        $this->assertSame(2, $this->badge($doctor), 'the sidebar badge');

        $html = Livewire::test(DocumentIssuanceTable::class)->html();
        $this->assertSame(2, substr_count($html, 'fa-exclamation-triangle"></i> Incomplete'), 'the "Incomplete" labels of the list');

        // The badge follows a saved plan at once (it used to be cached for a minute).
        $noPlan->update(['plan' => 'Paracetamol']);
        $this->assertSame(1, $this->badge($doctor), 'after the plan was written');
        $blankAssessment->update(['assessment' => 'Viral URTI']);
        $this->assertSame(0, $this->badge($doctor), 'after the assessment was written');
    }

    public function test_the_doctors_own_alert_and_the_row_flag_use_the_same_definition(): void
    {
        $doctor = $this->makeDoctor();
        $other = $this->makeDoctor(['email' => 'other.doc@qa.test']);
        $this->consultation($doctor, ['assessment' => '   ']);
        $this->consultation($doctor, ['plan' => '0']);
        $this->consultation($other, ['plan' => null]);

        $page = $this->actingAs($doctor)->get(route('doctors.document-issuances.index', ['module' => 'consultation']))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/You have <strong>1<\/strong> consultation form\b/', $page, 'only the doctor\'s own incomplete form');
    }

    // ------------------------------------------------------------------ values the database would not take

    public function test_the_form_refuses_dates_and_ids_the_database_would_not_take(): void
    {
        $doctor = $this->makeDoctor();
        $nurse = $this->makeNurse();
        $patient = $this->makePatient();
        $this->actingAs($doctor);

        $payload = fn (array $extra) => array_merge([
            'document_type' => 'consultation_form', 'user_id' => $patient->user_id, 'name' => $patient->user->full_name,
            'date_of_birth' => '2003-05-01', 'gender' => 'Male', 'requested_at' => now()->toDateString(),
            'consult_mode' => 'physical', 'complaints' => 'Cough', 'nursing_incharged' => $nurse->id,
        ], $extra);

        $bad = [
            'visit date that is not a date' => ['requested_at' => 'not-a-date'],
            'visit date that does not exist' => ['requested_at' => '2026-02-31'],
            'visit date years ahead' => ['requested_at' => now()->addYears(3)->toDateString()],
            'visit date before 1900' => ['requested_at' => '1800-01-01'],
            'consult mode that is neither' => ['consult_mode' => 'teleport'],
            'campus that does not exist' => ['campus_id' => 99999],
            'college that does not exist' => ['college_id' => 99999],
            'course that does not exist' => ['course_id' => 99999],
            'year level that does not exist' => ['year_level_id' => 99999],
            'department that does not exist' => ['department_id' => 99999],
            'office that does not exist' => ['office_id' => 99999],
        ];

        foreach ($bad as $label => $fields) {
            $this->post(route('doctors.document-issuances.store'), $payload($fields))->assertSessionHasErrors();
            $this->assertSame(0, DocumentIssuance::count(), "{$label}: nothing may be saved");
        }

        // The edit form takes the same rules.
        $existing = $this->consultation($doctor, ['complaints' => 'Before']);
        foreach ($bad as $label => $fields) {
            $this->put(route('doctors.document-issuances.update', $existing), $payload($fields + ['complaints' => 'After']))->assertSessionHasErrors();
            $this->assertSame('Before', $existing->fresh()->complaints, "edit, {$label}: nothing may be saved");
        }
        $existing->delete();

        // The same form with good values is accepted, and so is an old visit date written in the past; the posted age is
        // ignored (it is worked out from the date of birth).
        $this->post(route('doctors.document-issuances.store'), $payload(['requested_at' => now()->subYear()->toDateString(), 'age' => 'old']))->assertSessionHasNoErrors();
        $this->assertSame(1, DocumentIssuance::count());
        $this->assertGreaterThan(15, (int) DocumentIssuance::first()->age);
    }

    public function test_the_previous_visit_lookup_carries_height_and_weight_but_never_the_vital_signs_of_the_visit(): void
    {
        $doctor = $this->makeDoctor();
        $previous = $this->consultation($doctor, [
            'vital_signs_bp' => '120/80', 'vital_signs_pr' => '72', 'vital_signs_temp' => '36.6', 'vital_signs_rr' => '16', 'vital_signs_o2_sat' => '98',
            'vital_signs_weight' => '60', 'vital_signs_height' => '165', 'allergies' => 'Penicillin', 'religion' => 'Catholic',
        ]);
        $this->actingAs($doctor);

        $data = $this->getJson(route('doctors.document-issuances.get-last-consultation', ['user_id' => $previous->user_id]))->assertOk()->json('data');

        $this->assertSame('60', $data['vital_signs_weight']);
        $this->assertSame('165', $data['vital_signs_height']);
        $this->assertSame('Penicillin', $data['allergies'], 'the history fields are still carried over');
        $this->assertSame('Catholic', $data['religion']);
        foreach (['vital_signs_bp', 'vital_signs_pr', 'vital_signs_temp', 'vital_signs_rr', 'vital_signs_o2_sat'] as $vital) {
            $this->assertNull($data[$vital], "{$vital} is measured at every visit, not copied");
        }

        // A patient without any earlier visit has nothing to carry over.
        $fresh = $this->makePatient();
        $fallback = $this->getJson(route('doctors.document-issuances.get-last-consultation', ['user_id' => $fresh->user_id]))->assertOk()->json('data');
        $this->assertNull($fallback['vital_signs_bp']);

        // The form's script no longer writes them into the boxes either (it would also blank what the nurse had typed).
        $form = file_get_contents(resource_path('views/document_issuances/forms/consultation_form.blade.php'));
        foreach (['vital_signs_bp', 'vital_signs_pr', 'vital_signs_temp', 'vital_signs_rr', 'vital_signs_o2_sat'] as $vital) {
            $this->assertStringNotContainsString("setFieldValue('{$vital}'", $form, $vital);
        }
        $this->assertStringContainsString("setFieldValue('vital_signs_weight'", $form);
    }

    public function test_the_edit_form_does_not_ask_for_more_than_the_form_that_created_the_record(): void
    {
        $doctor = $this->makeDoctor();
        $nurse = $this->makeNurse();
        // A consultation as the nurse's form saves it: no pregnancy status, no nursing intervention, no vaccination.
        $document = $this->consultation($nurse, ['assessment' => null, 'plan' => null, 'pregnancy_status' => null, 'nursing_intervention' => null, 'covid_vaccination' => null]);

        $html = $this->actingAs($doctor)->get(route('doctors.document-issuances.edit', $document))->assertOk()->getContent();
        foreach (['pregnancy_status', 'nursing_intervention', 'vaccination_id'] as $field) {
            $this->assertSame(1, preg_match('/<(?:input|textarea|select)\b[^>]*\bname="' . $field . '"[^>]*>/s', $html, $tag), "{$field} is on the edit form");
            $this->assertStringNotContainsString(' required', $tag[0], "{$field} must not be required: the doctor could not save the Assessment and Plan of a nurse's record without inventing it");
        }

        // and the server takes the doctor's Assessment and Plan without them
        $this->put(route('doctors.document-issuances.update', $document), [
            'name' => $document->name, 'complaints' => 'Cough', 'requested_at' => now()->toDateString(), 'consult_mode' => 'physical',
            'nursing_incharged' => $nurse->id, 'assessment' => 'Acute bronchitis', 'plan' => 'Rest and fluids',
        ])->assertSessionHasNoErrors();
        $document->refresh();
        $this->assertSame('Acute bronchitis', $document->assessment);
        $this->assertSame('Rest and fluids', $document->plan);
    }

    // ------------------------------------------------------------------ the patient's history page shows what the role may open

    public function test_the_history_page_shows_consultations_certificates_prescriptions_and_dispensing_only_to_roles_that_may_open_them(): void
    {
        $doctor = $this->makeDoctor();
        $nurse = $this->makeNurse();
        $patient = $this->makePatient();

        $consultation = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $nurse->id, 'user_id' => $patient->user_id, 'name' => 'Juan', 'age' => 20,
            'gender' => 'Male', 'address' => 'D', 'requested_at' => now()->toDateString(), 'complaints' => 'Zzcomplaint', 'assessment' => 'Zzassessment', 'plan' => 'Zzplan',
        ]);
        // A second visit whose medicine appears only in the dispense history (it is not picked for the compare table).
        $dispensed = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $nurse->id, 'user_id' => $patient->user_id, 'name' => 'Juan', 'age' => 20,
            'gender' => 'Male', 'address' => 'D', 'requested_at' => now()->subDay()->toDateString(), 'complaints' => 'Older',
        ]);
        ConsultationMedicine::create(['request_document_id' => $dispensed->id, 'medicine_id' => $this->makeMedicine('Zzdispensedmed')->id, 'dosage' => '500mg', 'quantity' => 2, 'used_for' => 'plan']);
        DocumentIssuance::create([
            'document_type' => 'medical_certificate', 'document_creator_id' => $nurse->id, 'user_id' => $patient->user_id, 'name' => 'Juan', 'age' => 20,
            'gender' => 'Male', 'address' => 'D', 'requested_at' => now()->toDateString(), 'request_of' => 'Zzcertificate',
        ]);
        $prescription = \App\Models\Prescription::create(['patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id, 'status' => 'pending', 'is_active' => 1]);
        \App\Models\PrescriptionMedicine::create(['prescription_id' => $prescription->id, 'medicine' => $this->makeMedicine('Zzprescription')->id, 'dosage' => '500mg', 'total_quantity' => 3]);

        $markers = ['consultation' => 'Zzcomplaint', 'assessment' => 'Zzassessment', 'certificate' => 'Zzcertificate', 'prescription' => 'Zzprescription', 'dispensing' => 'Zzdispensedmed'];
        $seen = function (User $user) use ($patient, $consultation, $markers) {
            $route = $user->hasRole('clinic_admin') ? 'patients.showMyHistory' : 'staff.patients.showMyHistory';
            $html = $this->actingAs($user)->get(route($route, ['patient' => $patient, 'consultations_to_compare' => [$consultation->id]]))->assertOk()->getContent();

            return array_keys(array_filter($markers, fn ($marker) => str_contains($html, $marker)));
        };
        $headings = function (User $user) use ($patient, $consultation) {
            $route = $user->hasRole('clinic_admin') ? 'patients.showMyHistory' : 'staff.patients.showMyHistory';
            $html = $this->actingAs($user)->get(route($route, ['patient' => $patient, 'consultations_to_compare' => [$consultation->id]]))->assertOk()->getContent();

            return array_values(array_filter(['Consultation History', 'Medical Certificate History', 'Excuse Slip History', 'Compare Consultations', 'Prescription History', 'Medicine Dispense History'], fn ($h) => str_contains($html, $h)));
        };
        $grant = function (array $permissions) {
            \App\Models\Role::findByName('staff')->syncPermissions($permissions);
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        };

        $grant(['manage_patients']);
        $this->assertSame([], $seen($nurse), "Manage Patients alone: the patient's own record, nothing from the other modules");
        $this->assertSame([], $headings($nurse), "and not even the headings of the other modules' parts");

        // Nothing of the other modules is even read for that role.
        $grant(['manage_patients']);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $seen($nurse);
        $sql = implode(' ; ', array_column(DB::getQueryLog(), 'query'));
        DB::disableQueryLog();
        foreach (['from `prescriptions`', 'from `dispense_history_view`', 'from `document_issuances`', 'from `consultation_medicines`'] as $table) {
            $this->assertStringNotContainsString($table, $sql, "Manage Patients alone must not read {$table}");
        }

        $grant(['manage_patients', 'manage_request_documents']);
        $this->assertEqualsCanonicalizing(['consultation', 'assessment', 'certificate', 'prescription'], $seen($nurse), 'with the documents permission: consultations, certificates and prescriptions, not the dispense history');
        $this->assertEqualsCanonicalizing(['Consultation History', 'Medical Certificate History', 'Excuse Slip History', 'Compare Consultations', 'Prescription History'], $headings($nurse));

        $grant(['manage_patients', 'manage_medicines']);
        $this->assertSame(['dispensing'], $seen($nurse), 'Manage Medicines: the dispense history only');
        $this->assertSame(['Medicine Dispense History'], $headings($nurse));

        $this->assertEqualsCanonicalizing(array_keys($markers), $seen($this->makeAdmin()), 'the administrator sees everything');
    }

    // ------------------------------------------------------------------ pages with missing or awkward data

    public function test_the_pages_of_records_with_missing_or_awkward_data_open(): void
    {
        $doctor = $this->makeDoctor();
        $awkward = [
            'complaints' => "O'Brien \"Q\" <script>alert(1)</script> 😀 Ñandú & Co " . str_repeat('x', 3000),
            'assessment' => '<b>bold</b>', 'plan' => "line1\nline2 <i>x</i>", 'name' => "Zoë <b>Ñ</b> O'Neil",
        ];
        $bare = $this->consultation($doctor, ['complaints' => null, 'assessment' => null, 'plan' => null, 'requested_at' => null, 'address' => '', 'age' => 0]);
        $nasty = $this->consultation($doctor, $awkward);
        $certificate = $this->consultation($doctor, ['document_type' => 'medical_certificate', 'request_of' => null, 'medical_cert_remarks' => null, 'examined_on' => null]);
        $slip = $this->consultation($doctor, ['document_type' => 'excuse_slip', 'request_of' => "O'Brien <b>x</b>"]);

        $this->actingAs($doctor);
        foreach ([$bare, $nasty, $certificate, $slip] as $document) {
            $this->get(route('doctors.document-issuances.show', $document))->assertOk();
            $this->get(route('doctors.document-issuances.edit', $document))->assertOk();
        }

        $show = $this->get(route('doctors.document-issuances.show', $nasty))->getContent();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $show, 'a complaint is text, not markup');
        $this->assertStringNotContainsString('<b>bold</b>', $show);

        $list = Livewire::test(DocumentIssuanceTable::class)->html();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $list);
    }

    public function test_the_show_and_edit_pages_do_not_query_once_per_medicine(): void
    {
        $doctor = $this->makeDoctor();
        $document = $this->consultation($doctor);

        $add = function (int $i) use ($document) {
            $medicine = $this->makeMedicine('Passmed ' . $i);
            ConsultationMedicine::create(['request_document_id' => $document->id, 'medicine_id' => $medicine->id, 'dosage' => '500mg', 'quantity' => 1, 'used_for' => $i % 2 ? 'plan' : 'nursing']);
        };

        $queries = function (string $route) use ($doctor, $document) {
            $this->actingAs($doctor)->get(route($route, $document))->assertOk(); // warms the permission and settings caches
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($doctor)->get(route($route, $document))->assertOk();
            $count = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $count;
        };

        foreach (range(1, 2) as $i) {
            $add($i);
        }
        $few = ['show' => $queries('doctors.document-issuances.show'), 'edit' => $queries('doctors.document-issuances.edit')];
        foreach (range(3, 10) as $i) {
            $add($i);
        }

        $this->assertSame($few['show'], $queries('doctors.document-issuances.show'), 'show page: queries must not grow with the number of medicines');
        $this->assertSame($few['edit'], $queries('doctors.document-issuances.edit'), 'edit page: queries must not grow with the number of medicines');
    }
}
