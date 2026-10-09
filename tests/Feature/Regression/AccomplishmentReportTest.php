<?php

namespace Tests\Feature\Regression;

use App\Livewire\ReportGeneration;
use App\Models\College;
use App\Models\ConsultationMedicine;
use App\Models\DocumentIssuance;
use App\Models\Illness;
use App\Models\PatientType;
use App\Models\ServiceType;
use App\Models\Setting;
use App\Models\User;
use App\Services\Reports\AccomplishmentReportBuilder;
use App\Services\Reports\ReportFilters;
use App\Services\Reports\ReportQueries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * 2026-10-01 report, milestone 3: the ACCOMPLISHMENT REPORT (illness by body system and services, counted per
 * college column) and the filters of the Patient Visits tab. The screen, the PDF, the Excel file and the CSV all
 * come from one builder, so they must show the same numbers.
 */
class AccomplishmentReportTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $admin;

    private User $nurse;

    private User $patientUser;

    private College $ctx;

    private College $oth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();

        $this->admin = $this->makeAdmin(['first_name' => 'Ada', 'last_name' => 'Admin']);
        $this->nurse = $this->makeStaff('nurse', 'triage_area', ['first_name' => 'Kath', 'last_name' => 'Academia']);
        $this->patientUser = $this->makePatient()->user;

        $this->ctx = College::create(['college_name' => 'College of Test (CTX)']);
        $this->oth = College::create(['college_name' => 'College of Other (OTH)']);
    }

    private function type(string $code): int
    {
        return (int) PatientType::where('code', $code)->value('id');
    }

    /**
     * @param  array<int, string>  $illnesses  names from the clinic list
     * @param  array<int, string>  $services
     */
    private function visit(array $attributes = [], array $illnesses = [], array $services = []): DocumentIssuance
    {
        $visit = DocumentIssuance::create(array_merge([
            'document_type' => 'consultation_form', 'document_creator_id' => $this->nurse->id, 'user_id' => $this->patientUser->id,
            'name' => 'Juan Dela Cruz', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
            'requested_at' => '2026-03-10', 'consult_mode' => 'physical', 'informant' => 'Student',
            'patient_type_id' => $this->type('student'), 'college_id' => $this->ctx->id,
        ], $attributes));

        $visit->illnesses()->sync(Illness::whereIn('name', $illnesses)->pluck('id')->all());
        $visit->services()->sync(ServiceType::whereIn('name', $services)->pluck('id')->all());

        return $visit;
    }

    /** The scenario the matrix tests share: nine consultations across colleges, F&S, a guest and an unknown college. */
    private function scenario(): void
    {
        $v1 = $this->visit(['name' => 'Student One'], ['Cough/colds', 'Hypertension'], ['Tetanus injection']);
        $v2 = $this->visit(['name' => 'Student Two', 'gender' => 'Female', 'age' => 30], ['Cough/colds'], ['Medicine assistance']);
        $this->visit(['name' => 'Student Three', 'college_id' => $this->oth->id], ['Cough/colds']);
        $this->visit(['name' => 'Faculty Four', 'informant' => 'Faculty', 'patient_type_id' => $this->type('faculty')], ['Cough/colds']);
        $this->visit(['name' => 'Guest Five', 'informant' => 'Guest', 'patient_type_id' => $this->type('guest'), 'college_id' => null], ['Hypertension']);
        $this->visit(['name' => 'Student Six', 'college_id' => null], ['Asthma']);
        $this->visit(['name' => 'Student Seven', 'vital_signs_bp' => '120/80']);
        $this->visit(['name' => 'Student Eight', 'requested_at' => '2026-04-02'], ['Cough/colds']);
        $this->visit(['name' => 'Student Nine', 'consult_mode' => 'virtual'], ['Cough/colds']);

        // Student Two was given a medicine: it counts as "Medicine assistance" even though the nurse also ticked it.
        ConsultationMedicine::create([
            'request_document_id' => $v2->id, 'medicine_id' => $this->makeMedicine('Paracetamol')->id,
            'dosage' => '500mg', 'quantity' => 2, 'used_for' => 'nursing_intervention',
        ]);
    }

    /** @return array<string, array<string, int>> row name => column key => count (first match of a name) */
    private function illnessRows(array $report): array
    {
        $rows = [];
        foreach ($report['illness_sections'] as $section) {
            foreach ($section['groups'] as $group) {
                foreach ($group['rows'] as $row) {
                    $rows[$row['name']] ??= $row['counts'] + ['_total' => $row['total']];
                }
            }
        }

        return $rows;
    }

    /** @return array<string, array<string, int>> */
    private function serviceRows(array $report): array
    {
        $rows = [];
        foreach ($report['service_sections'] as $section) {
            foreach ($section['rows'] as $row) {
                $rows[$row['name']] ??= $row['counts'] + ['_total' => $row['total']];
            }
        }

        return $rows;
    }

    private function build(array $query = []): array
    {
        return app(AccomplishmentReportBuilder::class)->build(ReportFilters::fromArray($query), $this->nurse);
    }

    public function test_every_consultation_is_counted_under_its_college_faculty_and_staff_guest_or_unspecified_column(): void
    {
        $this->scenario();
        $report = $this->build();

        $ctx = 'c' . $this->ctx->id;
        $oth = 'c' . $this->oth->id;

        $this->assertSame(['CTX', 'OTH', 'GS', 'F&S', 'Guests/Others', 'Unspecified'], array_column($report['columns'], 'label'));

        $illness = $this->illnessRows($report);
        $this->assertSame([$ctx => 4, $oth => 1, 'fs' => 1, '_total' => 6], $illness['Cough/colds']);
        $this->assertSame([$ctx => 1, 'guest' => 1, '_total' => 2], $illness['Hypertension']);
        $this->assertSame(['unspecified' => 1, '_total' => 1], $illness['Asthma']);

        // Totals are the sum of the cells, and the visit without an illness is reported on its own line.
        $this->assertSame(9, $report['illness_total']['total']);
        $this->assertSame(9, $report['consultations']);
        $this->assertSame(1, $report['unclassified']);

        $respiratory = collect($report['illness_sections'])->firstWhere('name', 'Respiratory System');
        $this->assertSame(7, $respiratory['total']);
    }

    public function test_services_count_a_visit_once_whether_ticked_recognised_by_its_rule_or_both(): void
    {
        $this->scenario();
        $ctx = 'c' . $this->ctx->id;
        $services = $this->serviceRows($this->build());

        $this->assertSame([$ctx => 1, '_total' => 1], $services['Tetanus injection']);
        $this->assertSame([$ctx => 1, '_total' => 1], $services['Medicine assistance'], 'ticked and medicine given = one count');
        // Only a blood pressure was taken and the visit was not for an illness.
        $this->assertSame([$ctx => 1, '_total' => 1], $services['BP taking only']);
        $this->assertSame(0, $services['Nebulization only']['_total']);
    }

    public function test_certificates_and_lab_requests_made_for_the_patient_on_the_day_of_the_visit_are_recognised(): void
    {
        $this->visit(['name' => 'Cert Patient'], ['Cough/colds']);
        DocumentIssuance::create([
            'document_type' => 'medical_certificate', 'document_creator_id' => $this->nurse->id, 'user_id' => $this->patientUser->id,
            'name' => 'Juan Dela Cruz', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => '2026-03-10',
        ]);
        \DB::table('lab_requests')->insert([
            'request_number' => '000001', 'document_creator_id' => $this->nurse->id, 'patient_user_id' => $this->patientUser->id,
            'patient_name' => 'Juan Dela Cruz', 'requested_at' => '2026-03-10', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);
        // A certificate on another day does not belong to this visit.
        DocumentIssuance::create([
            'document_type' => 'medical_certificate', 'document_creator_id' => $this->nurse->id, 'user_id' => $this->patientUser->id,
            'name' => 'Juan Dela Cruz', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => '2026-03-20',
        ]);

        $services = $this->serviceRows($this->build());
        $ctx = 'c' . $this->ctx->id;

        $this->assertSame(1, $services['Medical certificate issuance'][$ctx]);
        $this->assertSame(1, $services['Request for laboratory/diagnostic exam / workup request only'][$ctx]);
    }

    public function test_the_filters_narrow_the_matrix_and_the_visit_list_the_same_way(): void
    {
        $this->scenario();
        $ctx = 'c' . $this->ctx->id;

        $cases = [
            'march only' => [['date_from' => '2026-03-01', 'date_to' => '2026-03-31'], 8],
            'walk-in only' => [['consult_mode' => 'physical'], 8],
            'virtual only' => [['consult_mode' => 'virtual'], 1],
            'one college' => [['college_id' => $this->ctx->id], 6],
            'faculty' => [['patient_type_id' => $this->type('faculty')], 1],
            'female' => [['gender' => 'Female'], 1],
            'age 25-34' => [['age_group' => 'adult'], 1],
            'has hypertension' => [['illness_id' => Illness::where('name', 'Hypertension')->value('id')], 2],
            'not classified yet' => [['illness_id' => 'none'], 1],
            'medicine assistance' => [['service_id' => ServiceType::where('name', 'Medicine assistance')->value('id')], 1],
            'given paracetamol' => [['medicine_id' => \App\Models\Medicine::where('name', 'Paracetamol')->value('id')], 1],
            'nurse in charge / encoder' => [['staff_id' => $this->nurse->id], 9],
            'another nurse' => [['staff_id' => $this->admin->id], 0],
        ];

        foreach ($cases as $label => [$query, $expected]) {
            $filters = ReportFilters::fromArray($query);
            $this->assertSame($expected, ReportQueries::visits($filters)->count(), "visit list, {$label}");
            $this->assertSame($expected, app(AccomplishmentReportBuilder::class)->build($filters)['consultations'], "report, {$label}");
        }

        // An illness filter also narrows the lines that are listed.
        $report = $this->build(['illness_id' => Illness::where('name', 'Hypertension')->value('id')]);
        $this->assertSame(['Hypertension'], array_keys($this->illnessRows($report)));
        $this->assertSame([$ctx => 1, 'guest' => 1, '_total' => 2], $this->illnessRows($report)['Hypertension']);

        // A body-system filter lists only that system.
        $report = $this->build(['illness_system_id' => Illness::where('name', 'Hypertension')->value('illness_system_id')]);
        $this->assertSame(['Circulatory System'], array_column($report['illness_sections'], 'name'));
    }

    public function test_pregnancy_chronic_condition_campus_course_year_level_department_and_office_filters(): void
    {
        $campus = \App\Models\Campus::create(['campus_name' => 'Main Campus 1']);
        $course = \App\Models\Course::create(['course_name' => 'BS Testing']);
        $year = \App\Models\YearLevel::create(['year_level_name' => 'Test Year']);

        $this->visit(['name' => 'Pregnant One', 'gender' => 'Female', 'pregnancy_status' => 'Yes, 12 weeks', 'comorbidities' => 'Hypertension, Asthma',
            'campus_id' => $campus->id, 'course_id' => $course->id, 'year_level_id' => $year->id], ['Headache']);
        $this->visit(['name' => 'Not Pregnant', 'gender' => 'Female', 'pregnancy_status' => 'N/A', 'comorbidities' => 'None', 'department_id' => 3, 'office_id' => 4], ['Headache']);
        $this->visit(['name' => 'Blank Fields', 'pregnancy_status' => null, 'comorbidities' => null], ['Headache']);

        $count = fn (array $query) => ReportQueries::visits(ReportFilters::fromArray($query))->count();

        $this->assertSame(1, $count(['pregnancy' => 'pregnant']));
        $this->assertSame(2, $count(['pregnancy' => 'not_pregnant']));
        $this->assertSame(1, $count(['chronic' => 'with']));
        $this->assertSame(2, $count(['chronic' => 'none']));
        $this->assertSame(1, $count(['chronic_text' => 'asthma']));
        $this->assertSame(0, $count(['chronic_text' => 'diabetes']));
        $this->assertSame(1, $count(['campus_id' => $campus->id]));
        $this->assertSame(1, $count(['course_id' => $course->id]));
        $this->assertSame(1, $count(['year_level_id' => $year->id]));
        $this->assertSame(1, $count(['department_id' => 3]));
        $this->assertSame(1, $count(['office_id' => 4]));
    }

    public function test_the_visit_list_filters_are_combined_and_junk_values_are_ignored(): void
    {
        $this->scenario();

        $this->assertSame(1, ReportQueries::visits(ReportFilters::fromArray(['college_id' => $this->ctx->id, 'gender' => 'Female', 'consult_mode' => 'physical']))->count());

        $junk = ReportFilters::fromArray(['college_id' => "1; DROP TABLE users", 'gender' => 'alien', 'age_group' => 'x', 'consult_mode' => 'z', 'pregnancy' => 'q', 'date_from' => '2026-13-45']);
        $this->assertSame(0, $junk->activeConsultationFilters());
        $this->assertSame(9, ReportQueries::visits($junk)->count());
    }

    public function test_the_signatories_are_the_signed_in_user_and_the_university_physician_from_settings(): void
    {
        $this->seed(\Database\Seeders\SettingTableSeeder::class);
        Setting::where('key', 'university_physician_name')->update(['value' => 'Dr. Michael S. Oliveros']);
        \App\Services\SettingsService::clearCache();

        $report = $this->build();

        $this->assertSame('Kath Academia', $report['prepared_by']['name']);
        $this->assertSame('Staff (Nurse)', $report['prepared_by']['title'], 'the signed-in role');
        $this->assertSame('Dr. Michael S. Oliveros', $report['noted_by']['name']);
        $this->assertSame('University Physician', $report['noted_by']['title']);
    }

    public function test_the_screen_the_csv_the_excel_and_the_pdf_show_the_same_numbers(): void
    {
        $this->scenario();
        $this->actingAs($this->admin);
        $query = ['date_from' => '2026-03-01', 'date_to' => '2026-03-31', 'consult_mode' => 'physical'];
        $report = $this->build($query);

        // Screen
        Livewire::withQueryParams(['tab' => 'accomplishment'] + $query)
            ->test(ReportGeneration::class)
            ->assertSee('ACCOMPLISHMENT REPORT')
            ->assertSee('Respiratory System')
            ->assertSee('Cough/colds')
            ->assertSee('Unclassified consultations');

        // CSV: one line per illness / service, columns as on the screen, names of the columns in the header.
        $csv = $this->get(route('activity-logs.accomplishment', ['format' => 'csv'] + $query))->assertOk()->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $lines = array_map('str_getcsv', preg_split('/\r\n|\n/', trim(substr($csv, 3))));
        $header = $lines[0];
        $this->assertSame(['Section', 'Body system / category', 'Group', 'Item'], array_slice($header, 0, 4));
        $this->assertSame(['CTX', 'OTH', 'GS', 'F&S', 'Guests/Others', 'Unspecified', 'Total'], array_slice($header, 4));
        $cough = collect($lines)->first(fn ($line) => ($line[3] ?? null) === 'Cough/colds');
        $ctxIndex = 4;
        $this->assertSame(2, (int) $cough[$ctxIndex], 'Cough/colds under CTX in March, walk-in');
        $this->assertSame((string) $this->illnessRows($report)['Cough/colds']['_total'], $cough[count($header) - 1]);

        // Excel
        $excel = $this->get(route('activity-logs.accomplishment', ['format' => 'xlsx'] + $query))->assertOk();
        $sheet = IOFactory::load($excel->baseResponse->getFile()->getPathname())->getActiveSheet();
        $text = collect($sheet->toArray())->flatten()->filter()->implode('|');
        $this->assertStringContainsString('ACCOMPLISHMENT REPORT', $text);
        $this->assertStringContainsString('Cough/colds', $text);
        $this->assertStringContainsString('Ada Admin', $text, 'Prepared by is whoever downloads it');
        $this->assertStringContainsString('Clinic Admin', $text);

        // PDF
        $pdf = $this->get(route('activity-logs.accomplishment', ['format' => 'pdf'] + $query))->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $this->assertStringContainsString('application/pdf', $pdf->headers->get('Content-Type'));
    }

    public function test_a_name_that_looks_like_a_formula_is_not_run_by_excel(): void
    {
        $this->scenario();
        $sneaky = $this->makeStaff('nurse', 'triage_area', ['first_name' => '=HYPERLINK("http://x","y")', 'last_name' => 'Nurse']);

        $this->actingAs($sneaky);
        $excel = $this->get(route('staff.activity-logs.accomplishment', ['format' => 'xlsx']))->assertOk();
        $sheet = IOFactory::load($excel->baseResponse->getFile()->getPathname())->getActiveSheet();

        foreach ($sheet->getCellCollection()->getCoordinates() as $coordinate) {
            $this->assertNotSame('f', $sheet->getCell($coordinate)->getDataType(), "{$coordinate} must not be a formula");
        }
        $this->assertStringContainsString("'=HYPERLINK", collect($sheet->toArray())->flatten()->filter()->implode('|'));
    }

    public function test_only_people_who_may_see_reports_can_download_it_and_unknown_formats_are_refused(): void
    {
        $this->scenario();

        $this->actingAs($this->admin)->get(route('activity-logs.accomplishment', ['format' => 'docx']))->assertNotFound();

        // Reports remain available when every optional role permission has been revoked.
        $deskStaff = $this->makeStaff('clinic_head', 'front_desk', ['first_name' => 'Desk', 'last_name' => 'Staff']);
        \App\Models\Role::findByName('staff')->syncPermissions([]);
        $this->assertTrue(canViewActivityLogTab('accomplishment', $deskStaff->fresh()));

        $response = $this->actingAs($deskStaff)->get(route('staff.activity-logs.accomplishment', ['format' => 'csv']));
        $response->assertOk();

        $this->actingAs($this->makeDoctor())->get(route('doctors.activity-logs.accomplishment', ['format' => 'csv']))->assertOk();
    }

    public function test_the_patient_visits_csv_carries_names_the_classification_and_follows_every_filter(): void
    {
        $this->scenario();
        $this->actingAs($this->admin);

        $query = ['tab' => 'visits', 'college_id' => $this->ctx->id, 'consult_mode' => 'physical'];
        $csv = $this->get(route('activity-logs.export', $query))->assertOk()->streamedContent();
        $rows = array_map('str_getcsv', preg_split('/\r\n|\n/', trim(substr($csv, 3))));

        $header = array_shift($rows);
        foreach (['Date', 'Patient Name', 'College', 'Patient Type', 'Illness', 'Services', 'Consult Mode', 'Nurse in Charge', 'Encoder'] as $heading) {
            $this->assertContains($heading, $header);
        }

        $names = array_column($rows, array_search('Patient Name', $header, true));
        // College CTX, walk-in: the faculty member shares the college record too.
        $this->assertEqualsCanonicalizing(['Student One', 'Student Two', 'Student Seven', 'Student Eight', 'Faculty Four'], $names);

        $one = collect($rows)->first(fn ($row) => $row[array_search('Patient Name', $header, true)] === 'Student One');
        $this->assertSame('Cough/colds; Hypertension', $one[array_search('Illness', $header, true)]);
        $this->assertSame('Tetanus injection', $one[array_search('Services', $header, true)]);
    }

    public function test_the_patient_visits_tab_has_the_filters_and_applies_them(): void
    {
        $this->scenario();
        $this->actingAs($this->admin);

        Livewire::test(ReportGeneration::class)
            ->call('setTab', 'visits')
            ->assertSee('Student One')->assertSee('Guest Five')
            ->set('patient_type_id', (string) $this->type('guest'))
            ->assertSee('Guest Five')->assertDontSee('Student One')
            ->call('resetFilters')
            ->assertSee('Student One')
            ->set('college_id', (string) $this->oth->id)
            ->assertSee('Student Three')->assertDontSee('Student One')
            ->call('resetFilters')
            ->set('illness_id', (string) Illness::where('name', 'Asthma')->value('id'))
            ->assertSee('Student Six')->assertDontSee('Student One');
    }

    public function test_the_period_buttons_set_the_dates_and_the_accomplishment_tab_starts_on_walk_in(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(ReportGeneration::class)->call('setTab', 'accomplishment');
        $this->assertSame('physical', $component->get('consult_mode'), 'the report counts walk-in consultations unless changed');

        $component->call('setPeriod', 'this_month');
        $this->assertSame(now()->startOfMonth()->toDateString(), $component->get('date_from'));
        $this->assertSame(now()->endOfMonth()->toDateString(), $component->get('date_to'));

        $component->call('setPeriod', 'last_year');
        $this->assertSame(now()->subYear()->startOfYear()->toDateString(), $component->get('date_from'));
        $this->assertSame(now()->subYear()->endOfYear()->toDateString(), $component->get('date_to'));

        // Leaving the report puts "All" back, but a mode picked by hand is kept.
        $component->call('setTab', 'visits');
        $this->assertSame('all', $component->get('consult_mode'));
        $component->set('consult_mode', 'virtual')->call('setTab', 'accomplishment')->call('setTab', 'visits');
        $this->assertSame('virtual', $component->get('consult_mode'));

        $component->set('month', '2026-03');
        $this->assertSame('2026-03-01', $component->get('date_from'));
        $this->assertSame('2026-03-31', $component->get('date_to'));
    }
}
