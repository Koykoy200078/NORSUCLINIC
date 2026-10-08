<?php

namespace Tests\Feature\Regression;

use App\Models\DocumentIssuance;
use App\Models\PatientQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * 2026-10-08 link crawl: every page was opened as every kind of user and every link on it followed. These are the
 * links / pages that answered 403 or crashed for the user they were shown to.
 */
class RoleAwareLinksTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function path(string $url): string
    {
        return (string) parse_url($url, PHP_URL_PATH);
    }

    /** "Cancel" on the queue edit page was hard-wired to the staff queue, so the administrator got a 403 from it. */
    public function test_the_queue_edit_page_cancel_button_goes_to_the_users_own_queue(): void
    {
        $admin = $this->makeAdmin();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $entry = PatientQueue::create([
            'patient_id' => $this->makePatient()->id, 'added_by' => $admin->id,
            'status' => PatientQueue::STATUS_WAITING, 'scheduled_at' => now(),
        ]);

        $html = $this->actingAs($admin)->get(route('patient-queue.edit', $entry))->assertOk()->getContent();
        $this->assertStringContainsString('href="' . route('patient-queue.index') . '"', $html);
        $this->assertStringNotContainsString($this->path(route('staff.patient-queue.index')) . '"', $html, 'admin was offered the staff queue');

        $html = $this->actingAs($nurse)->get(route('staff.patient-queue.edit', $entry))->assertOk()->getContent();
        $this->assertStringContainsString('href="' . route('staff.patient-queue.index') . '"', $html);
        $this->assertStringNotContainsString($this->path(route('patient-queue.index')) . '"', $html, 'nurse was offered the admin queue');
    }

    /**
     * The edit page's script used a variable that is only defined for consultation forms, so editing a medical
     * certificate or an excuse slip crashed with "Undefined variable $initialYearLevelId" for every role.
     */
    public function test_a_certificate_and_an_excuse_slip_can_be_opened_for_editing(): void
    {
        $doctor = $this->makeDoctor();
        $head = $this->makeStaff('clinic_head', 'front_desk');
        $patient = $this->makePatient();

        $documents = [];
        foreach (['consultation_form', 'medical_certificate', 'excuse_slip'] as $type) {
            $documents[$type] = DocumentIssuance::create([
                'document_type' => $type, 'document_creator_id' => $doctor->id, 'user_id' => $patient->user_id,
                'name' => 'Edit Page Patient', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
                'requested_at' => now()->toDateString(),
            ]);
        }

        $failures = [];
        foreach ($documents as $type => $document) {
            $module = $type === 'consultation_form' ? 'consultation' : 'certificate';
            $pages = [
                'admin' => [$this->makeAdmin(), route('document-issuances.edit', [$document, 'module' => $module])],
                'doctor' => [$doctor, route('doctors.document-issuances.edit', [$document, 'module' => $module])],
                'staff clinic_head' => [$head, route('staff.document-issuances.edit', [$document, 'module' => $module])],
            ];

            foreach ($pages as $who => [$user, $url]) {
                $status = $this->actingAs($user)->get($url)->getStatusCode();
                if ($status !== 200) {
                    $failures[] = "{$who} editing a {$type}: {$status}";
                }
            }
        }

        $this->assertSame([], $failures);
    }

    /** The "Discard" button of the add-doctor form was hard-wired to the administrator's doctor list. */
    public function test_the_add_doctor_form_discard_button_stays_in_the_staff_panel(): void
    {
        $head = $this->makeStaff('clinic_head', 'front_desk');

        $html = $this->actingAs($head)->get(route('staff.doctors.create'))->assertOk()->getContent();
        $this->assertStringContainsString('href="' . route('staff.doctors.index') . '"', $html);
        $this->assertStringNotContainsString('href="' . route('doctors.index') . '"', $html, 'staff was offered the admin doctor list');

        $html = $this->actingAs($this->makeAdmin())->get(route('doctors.create'))->assertOk()->getContent();
        $this->assertStringContainsString('href="' . route('doctors.index') . '"', $html);
    }

    /**
     * A pharmacist has no patients / doctors module, so the prescription and dispensing lists must show names as
     * plain text and no "New Prescription" button - those links answered 403. The clinic head (all modules) keeps them.
     */
    public function test_staff_are_only_shown_patient_and_doctor_links_their_modules_can_open(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient(['first_name' => 'Linkpatient']);
        $prescription = \App\Models\Prescription::create([
            'patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id, 'status' => 'pending', 'is_active' => 1,
        ]);
        $medicine = $this->makeMedicine('Linkmed');
        $record = \App\Models\DispenseRecord::create([
            'history_number' => 'HIS' . generateUniqueHistoryNumber(), 'patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id,
            'model_type' => \App\Models\DispenseRecord::class, 'model_id' => $patient->id, 'bill_date' => now(),
        ]);
        \App\Models\DispenseRecordItem::create(['dispense_id' => $record->id, 'medicine_id' => $medicine->id, 'quantity' => 1]);

        $pharmacist = $this->makeStaff('pharmacist', 'pharmacy');
        $head = $this->makeStaff('clinic_head', 'front_desk');
        // The two lists, drawn as the staff panel draws them. The dispensing table is lazy (a placeholder in the page), so it
        // is rendered directly. Each entry: [how to draw it for a user, text that proves the row of that table was drawn].
        $pages = [
            'prescriptions' => [
                fn ($user) => $this->actingAs($user)->get(route('staff.prescriptions.index'))->assertOk()->getContent(),
                'Linkpatient',
            ],
            'dispense records' => [
                function ($user) {
                    $this->actingAs($user);

                    return \Livewire\Livewire::withoutLazyLoading()->test(\App\Livewire\MedicineDispenseTable::class)->html();
                },
                $record->history_number,
            ],
        ];

        foreach ($pages as $label => [$draw, $marker]) {
            $html = $draw($pharmacist);
            $this->assertStringContainsString($marker, $html, "{$label}: the row did not render, the check would prove nothing");
            $this->assertSame(0, preg_match('#href="[^"]*/staff/patients/\d+#', $html), "{$label}: pharmacist offered a patient page");
            $this->assertSame(0, preg_match('#href="[^"]*/staff/doctors/\d+#', $html), "{$label}: pharmacist offered a doctor page");
            $this->assertStringNotContainsString('module=prescription', $html, "{$label}: pharmacist offered New Prescription");

            $html = $draw($head);
            $this->assertSame(1, preg_match('#href="[^"]*/staff/patients/\d+#', $html), "{$label}: clinic head lost the patient link");
            $this->assertSame(1, preg_match('#href="[^"]*/staff/doctors/\d+#', $html), "{$label}: clinic head lost the doctor link");
        }

        $this->assertStringContainsString('module=prescription', $pages['prescriptions'][0]($head), 'clinic head lost New Prescription');
    }

    /** The visits table in Activity Logs offered "View" on consultations to staff whose station cannot open them. */
    public function test_the_visit_list_offers_view_only_to_staff_who_can_open_consultations(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();
        $visit = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $doctor->id, 'user_id' => $patient->user_id,
            'name' => 'Visit Patient', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
        ]);

        $pairs = [
            ['clinic_head', 'front_desk'], ['records_officer', 'records_area'], ['clinic_staff', 'front_desk'],
            ['triage_officer', 'observation_room'], ['nurse', 'observation_room'], ['nurse', 'medical_consultation'],
        ];

        $failures = [];
        foreach ($pairs as $i => [$designation, $station]) {
            $user = $this->makeStaff($designation, $station, ['email' => "visit.staff{$i}@test.local"]);
            $html = $this->actingAs($user)->get(route('staff.activity-logs.index', ['tab' => 'visits']))->getContent();
            $offered = preg_match('#href="[^"]*/staff/document-issuances/' . $visit->id . '"#', $html) === 1;
            $allowed = canStaffAccessModule('consultations', $user);

            if ($offered !== $allowed) {
                $failures[] = "{$designation}@{$station}: View offered=" . var_export($offered, true) . ', consultations module=' . var_export($allowed, true);
            }
        }

        $this->assertSame([], $failures);
    }

    /** The doctor list's name / photo links were hard-wired to the administrator's /admin/doctors/{id} (403 for staff). */
    public function test_the_doctor_list_links_stay_inside_the_panel_of_the_user_who_sees_it(): void
    {
        $this->makeDoctor();
        $head = $this->makeStaff('clinic_head', 'front_desk');
        $admin = $this->makeAdmin();

        $this->actingAs($head);
        $html = \Livewire\Livewire::withoutLazyLoading()->test(\App\Livewire\DoctorTable::class)->html();
        $this->assertSame(1, preg_match('#href="[^"]*/staff/doctors/\d+"#', $html), 'staff doctor list lost its links');
        $this->assertSame(0, preg_match('#href="[^"]*/admin/doctors/\d+"#', $html), 'staff were offered the admin doctor page');

        $this->actingAs($admin);
        $html = \Livewire\Livewire::withoutLazyLoading()->test(\App\Livewire\DoctorTable::class)->html();
        $this->assertSame(1, preg_match('#href="[^"]*/admin/doctors/\d+"#', $html), 'admin doctor list lost its links');
    }

    /**
     * The patient list ("Create Prescription", the consultation / certificate counts) and the patient history page
     * (Edit / PDF on consultations, certificates, excuse slips; View on prescriptions) showed their buttons to every
     * staff member, so nurses, triage, front desk and records staff got a 403 from most of them. Each button must be
     * offered exactly when the staff member's module policy lets them open it.
     */
    public function test_patient_list_and_history_buttons_follow_the_staff_module_policy(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();

        $documents = [];
        foreach (['consultation_form', 'medical_certificate', 'excuse_slip'] as $type) {
            $documents[$type] = DocumentIssuance::create([
                'document_type' => $type, 'document_creator_id' => $doctor->id, 'user_id' => $patient->user_id,
                'name' => 'Policy Patient', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
            ]);
        }
        $prescription = \App\Models\Prescription::create([
            'patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id, 'status' => 'pending', 'is_active' => 1,
        ]);

        $pairs = [
            ['clinic_head', 'front_desk'], ['pharmacist', 'pharmacy'], ['records_officer', 'records_area'],
            ['clinic_staff', 'front_desk'], ['clinic_staff', 'records_area'],
            ['triage_officer', 'triage_area'], ['triage_officer', 'isolation_room'], ['triage_officer', 'observation_room'],
            ['nurse', 'triage_area'], ['nurse', 'medical_consultation'], ['nurse', 'isolation_room'], ['nurse', 'observation_room'],
        ];

        $has = fn (string $html, string $pattern): bool => preg_match('#href="[^"]*' . $pattern . '#', $html) === 1;

        $failures = [];
        foreach ($pairs as $i => [$designation, $station]) {
            $user = $this->makeStaff($designation, $station, ['email' => "policy.staff{$i}@test.local"]);
            $who = "{$designation}@{$station}";
            $can = fn (string $module) => canStaffAccessModule($module, $user);

            // ---- patient list (lazy table, drawn directly) ----
            $this->actingAs($user);
            $list = \Livewire\Livewire::withoutLazyLoading()->test(\App\Livewire\PatientTable::class)->html();
            $expectations = [
                'list: Create Prescription' => [$has($list, '/staff/patients/' . $patient->id . '/prescription-create'), $can('prescriptions')],
                'list: consultation count link' => [$has($list, 'module=consultation'), $can('consultations')],
            ];

            // ---- a patient's consultation list (reached from the count link, so only where that opens) ----
            if ($can('consultations')) {
                $visits = $this->actingAs($user)->get(route('staff.document-issuances.index', ['patient_id' => $patient->user_id, 'module' => 'consultation']))->assertOk()->getContent();
                $expectations['visits: Create Prescription'] = [$has($visits, '/staff/patients/' . $patient->id . '/prescription-create'), $can('prescriptions')];
            }

            // ---- patient history page (needs the patients module to open at all) ----
            if ($can('patients')) {
                $history = $this->actingAs($user)->get(route('staff.patients.showMyHistory', ['patient' => $patient->id]))->assertOk()->getContent();
                $expectations += [
                    'history: consultation Edit' => [$has($history, '/staff/document-issuances/' . $documents['consultation_form']->id . '/edit'), $can('consultations')],
                    'history: certificate Edit' => [$has($history, '/staff/document-issuances/' . $documents['medical_certificate']->id . '/edit'), $can('certificates')],
                    'history: excuse slip Edit' => [$has($history, '/staff/document-issuances/' . $documents['excuse_slip']->id . '/edit'), $can('certificates')],
                    'history: prescription View' => [$has($history, '/staff/prescriptions/' . $prescription->id . '"'), $can('prescriptions')],
                ];
            }

            foreach ($expectations as $what => [$offered, $allowed]) {
                if ($offered !== $allowed) {
                    $failures[] = "{$who} - {$what}: offered=" . var_export($offered, true) . ', module allows=' . var_export($allowed, true);
                }
            }
        }

        $this->assertSame([], $failures);
    }

    /** The staff dashboard's "recent patients" linked every name, though a pharmacist has no patients module. */
    public function test_the_staff_dashboard_links_patient_names_only_for_staff_who_can_open_patients(): void
    {
        $this->makePatient(['first_name' => 'Dashpatient']);
        $pharmacist = $this->makeStaff('pharmacist', 'pharmacy');
        $head = $this->makeStaff('clinic_head', 'front_desk');

        $this->actingAs($pharmacist);
        $html = \Livewire\Livewire::withoutLazyLoading()->test(\App\Livewire\StaffDashBoardTable::class)->html();
        $this->assertStringContainsString('Dashpatient', $html, 'the dashboard row did not render, the check would prove nothing');
        $this->assertSame(0, preg_match('#href="[^"]*/staff/patients/\d+"#', $html), 'pharmacist was offered a patient page');

        $this->actingAs($head);
        $html = \Livewire\Livewire::withoutLazyLoading()->test(\App\Livewire\StaffDashBoardTable::class)->html();
        $this->assertSame(1, preg_match('#href="[^"]*/staff/patients/\d+"#', $html), 'clinic head lost the patient link');
    }
}
