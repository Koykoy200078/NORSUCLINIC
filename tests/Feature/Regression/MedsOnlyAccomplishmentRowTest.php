<?php

namespace Tests\Feature\Regression;

use App\Livewire\ReportGeneration;
use App\Models\College;
use App\Models\Course;
use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\DocumentIssuance;
use App\Models\Illness;
use App\Models\Medicine;
use App\Models\PatientType;
use App\Models\User;
use App\Models\YearLevel;
use App\Services\MedsOnlyService;
use App\Services\Reports\AccomplishmentReportBuilder;
use App\Services\Reports\AccomplishmentReportCsv;
use App\Services\Reports\ReportFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Phase 11 C3: the ACCOMPLISHMENT REPORT gets one extra line, "Medicine only (no consultation)": the patients who only came for
 * medicine (Meds Only records), counted once per visit in the same columns as a consultation (college, F&S, Guest, Unspecified,
 * or the quarters of the yearly report) by the date of the record. It is its own table, never part of the consultation, illness
 * or service totals; it follows the filters that fit a Meds Only record and is left out when a filter that cannot match it
 * (an illness, a service, pregnancy, age, the nurse ...) is on, or when nobody took medicine only in the selection.
 *
 * Records (Biogesic given on each; the first one also Cetirizine, which must not make it count twice):
 *   m1 2026-03-05 student, CTX        m2 2026-03-07 student, CTX      m3 2026-03-10 student, OTH
 *   m4 2026-03-12 faculty, CTX        m5 2026-03-14 guest, no college m6 2026-03-16 student, no college
 *   m7 2026-04-02 student, CTX        m8 2025-03-05 student, CTX
 * and one consultation (2026-03-10, Cough/colds, CTX) and a manual dispense record that are not Meds Only.
 */
class MedsOnlyAccomplishmentRowTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private const LABEL = 'Medicine only (no consultation)';

    private User $admin;

    private User $nurse;

    private Medicine $biogesic;

    private Medicine $cetirizine;

    private College $ctx;

    private College $oth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->admin = $this->makeAdmin(['first_name' => 'Ada', 'last_name' => 'Admin']);
        $this->nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse']);

        $this->biogesic = $this->makeMedicine('Biogesic', ['dosage' => '500mg', 'uom' => 'tablet']);
        $this->cetirizine = $this->makeMedicine('Cetirizine', ['dosage' => '10mg', 'uom' => 'tablet']);
        $this->stockIn($this->biogesic, 200, now()->addYear()->toDateString(), '500mg', 'B-1');
        $this->stockIn($this->cetirizine, 200, now()->addYear()->toDateString(), '10mg', 'C-1');

        $this->ctx = College::create(['college_name' => 'College of Test (CTX)']);
        $this->oth = College::create(['college_name' => 'College of Other (OTH)']);
    }

    private function type(string $code): int
    {
        return (int) PatientType::where('code', $code)->value('id');
    }

    /** @param  array<int, array{0: int, 1: string, 2: int}>  $drugs  [medicine id, strength, quantity] */
    private function medsOnly(string $date, string $type, ?College $college, array $extra = [], ?array $drugs = null): DispenseRecord
    {
        $this->actingAs($this->nurse);
        $drugs ??= [[$this->biogesic->id, '500mg', 3]];

        return app(MedsOnlyService::class)->create(array_merge([
            'bill_date' => $date, 'patient_name' => 'Typed ' . $date . ' ' . $type, 'patient_type_id' => $this->type($type),
            'college_id' => $college?->id,
            'medicines' => array_map(fn (array $drug) => ['medicine_id' => $drug[0], 'dosage' => $drug[1], 'quantity' => $drug[2]], $drugs),
        ], $extra), $this->nurse);
    }

    private function scenario(): void
    {
        $this->medsOnly('2026-03-05', 'student', $this->ctx, drugs: [[$this->biogesic->id, '500mg', 3], [$this->cetirizine->id, '10mg', 2]]);
        $this->medsOnly('2026-03-07', 'student', $this->ctx);
        $this->medsOnly('2026-03-10', 'student', $this->oth);
        $this->medsOnly('2026-03-12', 'faculty', $this->ctx);
        $this->medsOnly('2026-03-14', 'guest', null);
        $this->medsOnly('2026-03-16', 'student', null);
        $this->medsOnly('2026-04-02', 'student', $this->ctx);
        $this->medsOnly('2025-03-05', 'student', $this->ctx);

        $visit = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $this->nurse->id, 'user_id' => $this->makePatient()->user->id,
            'name' => 'Consulted Student', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => '2026-03-10',
            'consult_mode' => 'physical', 'informant' => 'Student', 'patient_type_id' => $this->type('student'), 'college_id' => $this->ctx->id,
        ]);
        $visit->illnesses()->sync(Illness::whereIn('name', ['Cough/colds'])->pluck('id')->all());

        // A manual dispense record is not a Meds Only visit.
        $manual = DispenseRecord::create(['history_number' => 'HIS-MANUAL', 'patient_id' => $this->makePatient(['first_name' => 'Manual', 'last_name' => 'Person'])->id,
            'model_type' => DispenseRecord::class, 'bill_date' => '2026-03-11']);
        DispenseRecordItem::create(['dispense_id' => $manual->id, 'medicine_id' => $this->biogesic->id, 'dosage' => '500mg', 'quantity' => 9]);
    }

    private function build(array $query = []): array
    {
        return app(AccomplishmentReportBuilder::class)->build(ReportFilters::fromArray($query), $this->admin);
    }

    /** @return array<string, int> column key => count of the Meds Only line */
    private function counts(array $report): array
    {
        return array_filter($report['medicine_only']['counts'] ?? [], fn ($count) => $count > 0);
    }

    private const MARCH = ['date_from' => '2026-03-01', 'date_to' => '2026-03-31'];

    // ------------------------------------------------------------------ the numbers

    public function test_the_line_counts_one_per_visit_in_the_columns_of_a_consultation(): void
    {
        $this->scenario();
        $report = $this->build(self::MARCH);

        $this->assertSame(self::LABEL, $report['medicine_only']['name']);
        $this->assertSame([
            'c' . $this->ctx->id => 2,
            'c' . $this->oth->id => 1,
            AccomplishmentReportBuilder::FACULTY_STAFF => 1,
            AccomplishmentReportBuilder::GUEST => 1,
            AccomplishmentReportBuilder::UNSPECIFIED => 1,
        ], $this->counts($report), 'a visit with two drugs is one count; the faculty member is F&S; the guest is Guest; no college is Unspecified');
        $this->assertSame(6, $report['medicine_only']['total']);

        // The Unspecified column exists because this line uses it, although no consultation does.
        $this->assertContains(AccomplishmentReportBuilder::UNSPECIFIED, array_column($report['columns'], 'key'));
    }

    public function test_the_line_is_not_part_of_the_consultation_illness_or_service_totals(): void
    {
        $this->scenario();
        $report = $this->build(self::MARCH);

        $this->assertSame(1, $report['consultations'], 'only the consultation');
        $this->assertSame(1, $report['illness_total']['total']);
        $this->assertSame(0, $report['unclassified'], 'the consultation has an illness, and the Meds Only visits are not consultations to classify');
        $this->assertSame(0, $report['service_total']['total'], 'Medicine assistance is recognised from consultations only');
        $this->assertArrayNotHasKey(AccomplishmentReportBuilder::UNSPECIFIED, $report['illness_total']['counts']);
    }

    public function test_the_dates_decide_and_the_line_is_left_out_when_nobody_took_medicine_only(): void
    {
        $this->scenario();

        $this->assertSame(1, $this->build(['date_from' => '2026-04-01', 'date_to' => '2026-04-30'])['medicine_only']['total']);
        $this->assertSame(7, $this->build(['date_from' => '2026-03-01', 'date_to' => '2026-04-30'])['medicine_only']['total']);
        $this->assertSame(1, $this->build(['date_from' => '2026-03-10', 'date_to' => '2026-03-10'])['medicine_only']['total'], 'both ends of the period are in');
        $this->assertNull($this->build(['date_from' => '2026-06-01', 'date_to' => '2026-06-30'])['medicine_only'], 'no row of zeros');
    }

    public function test_staff_are_counted_under_faculty_and_staff_whatever_college_is_on_the_record(): void
    {
        $this->medsOnly('2026-05-04', 'staff', null);
        $this->medsOnly('2026-05-05', 'staff', $this->ctx);
        $this->medsOnly('2026-05-06', 'faculty', $this->oth);

        $report = $this->build(['date_from' => '2026-05-01', 'date_to' => '2026-05-31']);

        $this->assertSame([AccomplishmentReportBuilder::FACULTY_STAFF => 3], $this->counts($report));
        $this->assertNotContains(AccomplishmentReportBuilder::UNSPECIFIED, array_column($report['columns'], 'key'), 'nobody is unspecified, so the column is not added');
    }

    public function test_a_report_without_any_meds_only_record_is_what_it_was(): void
    {
        $this->scenario();
        DispenseRecord::medsOnly()->get()->each->delete();

        $report = $this->build(self::MARCH);
        $this->assertNull($report['medicine_only']);
        $this->assertArrayNotHasKey(AccomplishmentReportBuilder::UNSPECIFIED, array_flip(array_column($report['columns'], 'key')), 'no Unspecified column from a line that is not there');
    }

    public function test_the_filters_that_fit_a_meds_only_record_narrow_the_line(): void
    {
        $this->scenario();
        $course = Course::create(['course_name' => 'BS Biology']);
        $year = YearLevel::create(['year_level_name' => '2nd Year']);
        $this->medsOnly('2026-03-20', 'student', $this->ctx, ['course_id' => $course->id, 'year_level_id' => $year->id], [[$this->cetirizine->id, '10mg', 1]]);

        $this->assertSame(4, $this->build(self::MARCH + ['college_id' => $this->ctx->id])['medicine_only']['total'], 'm1, m2, m4 and the new one: the faculty member is in CTX too');
        $this->assertSame(1, $this->build(self::MARCH + ['course_id' => $course->id])['medicine_only']['total']);
        $this->assertSame(1, $this->build(self::MARCH + ['year_level_id' => $year->id])['medicine_only']['total']);
        $this->assertSame(1, $this->build(self::MARCH + ['patient_type_id' => $this->type('guest')])['medicine_only']['total']);
        $this->assertSame(1, $this->build(self::MARCH + ['patient_type_id' => $this->type('faculty')])['medicine_only']['total']);
        // The medicine filter: the visits that were given that medicine (m1 has both drugs; the new record has Cetirizine only).
        $this->assertSame(2, $this->build(self::MARCH + ['medicine_id' => $this->cetirizine->id])['medicine_only']['total']);
        $this->assertSame(1, $this->build(self::MARCH + ['college_id' => $this->ctx->id, 'medicine_id' => $this->cetirizine->id, 'course_id' => $course->id])['medicine_only']['total'], 'filters are combined');
        $this->assertSame(7, $this->build(self::MARCH)['medicine_only']['total'], 'the new record is in March');
    }

    public function test_the_word_search_of_the_screen_does_not_hide_or_narrow_the_line(): void
    {
        $this->scenario();

        $this->assertSame(6, $this->build(self::MARCH + ['search' => 'nobody has this name'])['medicine_only']['total'], 'the report does not use the search box');
    }

    public function test_a_filter_that_cannot_match_a_meds_only_record_leaves_the_line_out(): void
    {
        $this->scenario();
        $illness = Illness::where('name', 'Cough/colds')->first();

        foreach ([
            'illness' => ['illness_id' => $illness->id],
            'body system' => ['illness_system_id' => $illness->illness_system_id],
            'not classified' => ['illness_id' => 'none'],
            'service' => ['service_id' => \App\Models\ServiceType::first()->id],
            'gender' => ['gender' => 'Male'],
            'age group' => ['age_group' => 'young_adult'],
            'nurse' => ['staff_id' => $this->nurse->id],
            'pregnancy' => ['pregnancy' => 'pregnant'],
            'not pregnant' => ['pregnancy' => 'not_pregnant'],
            'chronic condition' => ['chronic' => 'none'],
            'chronic condition text' => ['chronic_text' => 'asthma'],
            'department' => ['department_id' => 1],
            'office' => ['office_id' => 1],
            'virtual visits' => ['consult_mode' => 'virtual'],
        ] as $name => $filter) {
            $this->assertNull($this->build(self::MARCH + $filter)['medicine_only'], "the line is left out with the {$name} filter");
        }

        // Walk-in is how a person who comes for medicine is seen, and the screen starts the report on it.
        $this->assertSame(6, $this->build(self::MARCH + ['consult_mode' => 'physical'])['medicine_only']['total']);
        $this->assertSame(6, $this->build(self::MARCH + ['consult_mode' => 'all'])['medicine_only']['total']);
    }

    public function test_a_college_that_is_no_longer_in_the_list_is_counted_under_unspecified(): void
    {
        $this->scenario();
        $record = $this->medsOnly('2026-03-21', 'student', $this->oth);
        $this->oth->delete();

        $counts = $this->counts($this->build(self::MARCH));
        $this->assertArrayNotHasKey('c' . $this->oth->id, $counts);
        $this->assertSame(3, $counts[AccomplishmentReportBuilder::UNSPECIFIED], 'the two records of the removed college and the one that never had a college');
        $this->assertSame($record->id, DispenseRecord::medsOnly()->where('college_id', $this->oth->id)->latest('id')->value('id'));
    }

    // ------------------------------------------------------------------ the yearly report

    public function test_the_yearly_report_counts_each_visit_in_the_quarter_of_its_date_and_ignores_the_typed_dates(): void
    {
        $this->scenario();
        $this->medsOnly('2026-12-31', 'student', $this->ctx);
        $this->medsOnly('2026-07-01', 'guest', null);

        $report = $this->build(['year' => '2026', 'date_from' => '2026-03-01', 'date_to' => '2026-03-02']);

        $this->assertSame(['q1' => 6, 'q2' => 1, 'q3' => 1, 'q4' => 1], $this->counts($report));
        $this->assertSame(9, $report['medicine_only']['total']);
        $this->assertSame(['q1', 'q2', 'q3', 'q4'], array_column($report['columns'], 'key'), 'no college columns, no Unspecified column');
        $this->assertSame(['q1' => 2, 'q2' => 1, 'q4' => 1], $this->counts($this->build(['year' => '2026', 'college_id' => $this->ctx->id, 'patient_type_id' => $this->type('student')])), 'the filters of the college report apply to the yearly one too');
        $this->assertNull($this->build(['year' => '2026', 'illness_id' => Illness::first()->id])['medicine_only']);
    }

    // ------------------------------------------------------------------ screen, PDF, Excel, CSV

    public function test_the_screen_the_pdf_the_excel_sheet_and_the_csv_show_the_same_line(): void
    {
        $this->scenario();
        $this->actingAs($this->admin);
        $report = $this->build(self::MARCH);
        $expected = array_map(fn (array $column) => (int) ($report['medicine_only']['counts'][$column['key']] ?? 0), $report['columns']);
        $expected[] = 6;

        // Screen
        $screen = Livewire::withQueryParams(['tab' => 'accomplishment'] + self::MARCH)->test(ReportGeneration::class)
            ->assertSee('MEDICINE ONLY')
            ->assertSee(self::LABEL);
        $html = $screen->html();
        $this->assertSame(3, substr_count($html, '<th class="num">TOTAL</th>'), 'illness table, services table and the Meds Only table');

        // PDF and Excel are rendered from the same Blade tables as the screen.
        $pdfHtml = view('activity_logs.reports.accomplishment_pdf', ['report' => $report])->render();
        $this->assertStringContainsString(self::LABEL, $pdfHtml);
        $this->assertSame(3, substr_count($pdfHtml, '<th class="num">TOTAL</th>'));
        $pdf = $this->get(route('activity-logs.accomplishment', ['format' => 'pdf'] + self::MARCH))->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $excel = $this->get(route('activity-logs.accomplishment', ['format' => 'xlsx'] + self::MARCH))->assertOk();
        $sheet = IOFactory::load($excel->baseResponse->getFile()->getPathname())->getActiveSheet();
        $row = collect($sheet->toArray())->first(fn ($line) => ($line[0] ?? null) === self::LABEL);
        $this->assertNotNull($row, 'the line is in the Excel sheet');
        $this->assertSame($expected, array_map('intval', array_slice($row, 1, count($expected))));

        // CSV: after the services, before the summary; the same numbers; the header is the one of the other lines.
        $csv = $this->get(route('activity-logs.accomplishment', ['format' => 'csv'] + self::MARCH))->assertOk()->streamedContent();
        $lines = array_map('str_getcsv', preg_split('/\r\n|\n/', trim(substr($csv, 3))));
        $line = collect($lines)->first(fn ($candidate) => ($candidate[3] ?? null) === self::LABEL);
        $this->assertSame(['Medicine only', 'No consultation', ''], array_slice($line, 0, 3));
        $this->assertSame($expected, array_map('intval', array_slice($line, 4)));
        $index = array_search($line, $lines, true);
        $this->assertSame('Other services', $lines[$index - 1][0], 'right after the services total');
        $this->assertSame('Summary', $lines[$index + 1][0]);

        // The same rows come out of the CSV class itself.
        $this->assertContains(self::LABEL, array_column(AccomplishmentReportCsv::rows($report), 3));
    }

    public function test_the_yearly_screen_the_pdf_the_excel_sheet_and_the_csv_show_the_quarters_of_the_line(): void
    {
        $this->scenario();
        $this->actingAs($this->admin);
        $report = $this->build(['year' => '2026']);
        $this->assertSame(['q1' => 6, 'q2' => 1], $this->counts($report));

        $screen = Livewire::withQueryParams(['tab' => 'accomplishment'])->test(ReportGeneration::class)->set('year', '2026')->assertSee(self::LABEL);
        $this->assertSame(3, substr_count($screen->html(), '<th class="num">YEAR TOTAL</th>'));

        $excel = $this->get(route('activity-logs.accomplishment', ['format' => 'xlsx', 'year' => 2026]))->assertOk();
        $sheet = IOFactory::load($excel->baseResponse->getFile()->getPathname())->getActiveSheet();
        $row = collect($sheet->toArray())->first(fn ($line) => ($line[0] ?? null) === self::LABEL);
        $this->assertSame([6, 1, 0, 0, 7], array_map('intval', array_slice($row, 1, 5)));

        $csv = $this->get(route('activity-logs.accomplishment', ['format' => 'csv', 'year' => 2026]))->assertOk()->streamedContent();
        $lines = array_map('str_getcsv', preg_split('/\r\n|\n/', trim(substr($csv, 3))));
        $line = collect($lines)->first(fn ($candidate) => ($candidate[3] ?? null) === self::LABEL);
        $this->assertSame(['6', '1', '0', '0', '7'], array_slice($line, 4));
    }

    public function test_a_name_of_a_college_does_not_matter_and_a_report_without_the_line_has_no_table_for_it(): void
    {
        $this->scenario();
        $this->actingAs($this->admin);

        $html = Livewire::withQueryParams(['tab' => 'accomplishment', 'date_from' => '2026-06-01', 'date_to' => '2026-06-30'])->test(ReportGeneration::class)
            ->assertDontSee(self::LABEL)->html();
        $this->assertSame(2, substr_count($html, '<th class="num">TOTAL</th>'), 'illness table and services table only');
    }
}
