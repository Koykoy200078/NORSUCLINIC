<?php

namespace Tests\Feature\Regression;

use App\Livewire\ReportGeneration;
use App\Models\Campus;
use App\Models\College;
use App\Models\Course;
use App\Models\DispenseRecord;
use App\Models\Generic;
use App\Models\Medicine;
use App\Models\PatientType;
use App\Models\User;
use App\Models\YearLevel;
use App\Services\MedsOnlyService;
use App\Services\Reports\MedsOnlyReportBuilder;
use App\Services\Reports\ReportCsv;
use App\Services\Reports\ReportFilters;
use App\Services\Reports\ReportQueries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * The Meds Only tab of Report Generation (Phase 11 C1): the patients who only came for medicine, with their date, name, type,
 * college, course, year and the drugs, filtered like the other tabs (dates, college, course, year, patient type, campus,
 * medicine, a word search that also finds a drug by its generic or brand). The screen pages the rows, the CSV streams them, and
 * the PDF and the Excel sheet are built from the same rows with the units given per medicine.
 *
 * Records (all typed names; Biogesic 500mg = Acetaminophen / Brandix, Cetirizine 10mg):
 *   r1 2026-03-05 Juan Typed, Student, College of Arts, BS Biology, 2nd Year: Biogesic x 6, Cetirizine x 2
 *   r2 2026-03-20 Maria Typed, Faculty, College of Business, BS Accountancy, 1st Year: Biogesic x 4
 *   r3 2026-04-02 Pedro Typed, Student, College of Arts, BS Biology, 3rd Year: Cetirizine x 5
 * and a manual dispense record and a prescription's record that must never be in it.
 */
class MedsOnlyReportTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $nurse;

    private User $admin;

    private Medicine $biogesic;

    private Medicine $cetirizine;

    private array $r = [];

    private College $arts;

    private College $business;

    private Course $biology;

    private YearLevel $second;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->admin = $this->makeAdmin(['first_name' => 'Ada', 'last_name' => 'Admin']);
        $this->nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse']);

        $generic = Generic::create(['name' => 'Acetaminophen']);
        $this->biogesic = $this->makeMedicine('Biogesic', ['dosage' => '500mg', 'uom' => 'tablet', 'generic_id' => $generic->id, 'brand_name' => 'Brandix']);
        $this->cetirizine = $this->makeMedicine('Cetirizine', ['dosage' => '10mg', 'uom' => 'tablet']);
        $this->stockIn($this->biogesic, 100, now()->addYear()->toDateString(), '500mg', 'B-1');
        $this->stockIn($this->cetirizine, 100, now()->addYear()->toDateString(), '10mg', 'C-1');
        $this->stockIn($this->cetirizine, 3, now()->addMonths(6)->toDateString(), '10mg', 'C-0');

        $this->arts = College::create(['college_name' => 'College of Arts']);
        $this->business = College::create(['college_name' => 'College of Business']);
        $this->biology = Course::create(['course_name' => 'BS Biology']);
        $accountancy = Course::create(['course_name' => 'BS Accountancy']);
        $this->second = YearLevel::create(['year_level_name' => '2nd Year']);
        $first = YearLevel::create(['year_level_name' => '1st Year']);
        $third = YearLevel::create(['year_level_name' => '3rd Year']);
        $student = PatientType::where('name', 'Student')->first() ?? PatientType::create(['name' => 'Student']);
        $faculty = PatientType::where('name', 'Faculty')->first() ?? PatientType::create(['name' => 'Faculty']);

        $this->actingAs($this->nurse);
        $service = app(MedsOnlyService::class);
        $make = fn (array $data) => $service->create($data, $this->nurse);
        $this->r[1] = $make(['bill_date' => '2026-03-05', 'patient_name' => 'Juan Typed', 'patient_type_id' => $student->id, 'college_id' => $this->arts->id, 'course_id' => $this->biology->id, 'year_level_id' => $this->second->id,
            'note' => 'headache', 'medicines' => [['medicine_id' => $this->biogesic->id, 'dosage' => '500mg', 'quantity' => 6], ['medicine_id' => $this->cetirizine->id, 'dosage' => '10mg', 'quantity' => 2]]]);
        $this->r[2] = $make(['bill_date' => '2026-03-20', 'patient_name' => 'Maria Typed', 'patient_type_id' => $faculty->id, 'college_id' => $this->business->id, 'course_id' => $accountancy->id, 'year_level_id' => $first->id,
            'medicines' => [['medicine_id' => $this->biogesic->id, 'dosage' => '500mg', 'quantity' => 4]]]);
        $this->r[3] = $make(['bill_date' => '2026-04-02', 'patient_name' => 'Pedro Typed', 'patient_type_id' => $student->id, 'college_id' => $this->arts->id, 'course_id' => $this->biology->id, 'year_level_id' => $third->id,
            'medicines' => [['medicine_id' => $this->cetirizine->id, 'dosage' => '10mg', 'quantity' => 5]]]);

        $manual = DispenseRecord::create(['history_number' => 'HIS-MANUAL', 'patient_id' => $this->makePatient(['first_name' => 'Manual', 'last_name' => 'Person'])->id, 'model_type' => DispenseRecord::class, 'bill_date' => '2026-03-10']);
        \App\Models\DispenseRecordItem::create(['dispense_id' => $manual->id, 'medicine_id' => $this->biogesic->id, 'dosage' => '500mg', 'quantity' => 9]);
    }

    private function ids(array $input = []): array
    {
        return ReportQueries::forTab('meds_only', ReportFilters::fromArray($input))->pluck('medicine_bills.id')->map(fn ($id) => (int) $id)->all();
    }

    private function brief(array $report): array
    {
        return array_map(fn (array $row) => $row['name'], $report['rows']);
    }

    // ------------------------------------------------------------------ the query

    public function test_the_tab_lists_only_meds_only_records_newest_first_and_the_filters_narrow_it(): void
    {
        [$r1, $r2, $r3] = [$this->r[1]->id, $this->r[2]->id, $this->r[3]->id];

        $this->assertContains('meds_only', ReportQueries::TABS);
        $this->assertSame([$r3, $r2, $r1], $this->ids(), 'newest first; no manual record');
        $this->assertSame([$r2, $r1], $this->ids(['date_to' => '2026-03-31']), 'dates are inclusive');
        $this->assertSame([$r3, $r2], $this->ids(['date_from' => '2026-03-20']));
        $this->assertSame([$r2], $this->ids(['date_from' => '2026-03-20', 'date_to' => '2026-03-20']));
        $this->assertSame([$r3, $r1], $this->ids(['college_id' => $this->arts->id]));
        $this->assertSame([$r2], $this->ids(['college_id' => $this->business->id]));
        $this->assertSame([$r3, $r1], $this->ids(['course_id' => $this->biology->id]));
        $this->assertSame([$r1], $this->ids(['year_level_id' => $this->second->id]));
        $this->assertSame([$r3, $r1], $this->ids(['patient_type_id' => PatientType::where('name', 'Student')->value('id')]));
        $this->assertSame([$r3, $r1], $this->ids(['medicine_id' => $this->cetirizine->id]), 'the records that include that drug');
        $this->assertSame([$r2, $r1], $this->ids(['medicine_id' => $this->biogesic->id]));
        $this->assertSame([], $this->ids(['college_id' => 999999]));
        $this->assertSame([$r3, $r2, $r1], $this->ids(['college_id' => 'junk', 'date_from' => 'not a date']), 'junk filters mean no filter');
    }

    public function test_the_word_search_finds_a_person_a_college_a_course_and_a_drug_by_name_generic_or_brand(): void
    {
        [$r1, $r2, $r3] = [$this->r[1]->id, $this->r[2]->id, $this->r[3]->id];

        $this->assertSame([$r1], $this->ids(['search' => 'juan']));
        $this->assertSame([$r3, $r1], $this->ids(['search' => 'arts']));
        $this->assertSame([$r2], $this->ids(['search' => 'accountancy']));
        $this->assertSame([$r2, $r1], $this->ids(['search' => 'acetaminophen']));
        $this->assertSame([$r2, $r1], $this->ids(['search' => 'brandix']));
        $this->assertSame([$r3, $r1], $this->ids(['search' => 'cetirizine']));
        $this->assertSame([$r1], $this->ids(['search' => 'juan acetaminophen']), 'every word must match something');
        $this->assertSame([$r1], $this->ids(['search' => 'headache']), 'the note');
        DispenseRecord::whereKey($r2)->update(['campus' => 'Main Campus 1']);
        $this->assertSame([$r2], $this->ids(['search' => 'main campus']), 'the campus');
        $this->assertSame([$r3, $r2, $r1], $this->ids(['search' => 'nora']), 'who recorded it');
        $this->assertSame([], $this->ids(['search' => 'juan nothing']));
        $this->assertSame([], $this->ids(['search' => 'manual']), 'a manual dispense record is never in this report');
    }

    // ------------------------------------------------------------------ the builder

    public function test_the_builder_gives_the_rows_and_the_units_given_per_medicine(): void
    {
        $report = app(MedsOnlyReportBuilder::class)->build(ReportFilters::fromArray([]), $this->admin);

        $this->assertSame(['Pedro Typed', 'Maria Typed', 'Juan Typed'], $this->brief($report));
        $row = $report['rows'][2];
        $this->assertSame(['2026-03-05', 'Juan Typed', 'Student', 'College of Arts', 'BS Biology', '2nd Year', 'Biogesic 500mg x 6, Cetirizine 10mg x 2', 'Nora Nurse', 'headache'],
            [$row['date'], $row['name'], $row['type'], $row['college'], $row['course'], $row['year_level'], $row['drugs'], $row['recorded_by'], $row['note']]);

        $this->assertSame(3, $report['records']);
        $this->assertSame(17, $report['units']);
        $this->assertSame([['Biogesic', '500mg', 'tablet', 2, 10], ['Cetirizine', '10mg', 'tablet', 2, 7]],
            array_map(fn (array $m) => [$m['name'], $m['strength'], $m['unit'], $m['records'], $m['units']], $report['medicines']));

        $arts = app(MedsOnlyReportBuilder::class)->build(ReportFilters::fromArray(['college_id' => $this->arts->id]), $this->admin);
        $this->assertSame([2, 13, [['Biogesic', 1, 6], ['Cetirizine', 2, 7]]],
            [$arts['records'], $arts['units'], array_map(fn (array $m) => [$m['name'], $m['records'], $m['units']], $arts['medicines'])]);

        $none = app(MedsOnlyReportBuilder::class)->build(ReportFilters::fromArray(['college_id' => 999999]), $this->admin);
        $this->assertSame([0, 0, []], [$none['records'], $none['units'], $none['medicines']]);
    }

    // ------------------------------------------------------------------ the screen

    public function test_the_screen_shows_the_tab_its_filters_and_the_downloads_that_carry_them(): void
    {
        $this->actingAs($this->admin);

        Livewire::withQueryParams(['tab' => 'meds_only'])->test(ReportGeneration::class)
            ->assertSee('Meds Only')->assertSee('Juan Typed')->assertSee('Maria Typed')->assertSee('Pedro Typed')
            ->assertSee('Biogesic 500mg x 6, Cetirizine 10mg x 2')->assertSee('College of Arts')->assertSee('BS Biology')->assertSee('2nd Year')
            ->assertDontSee('Manual Person')
            ->assertSeeHtml('/meds-only/pdf')->assertSeeHtml('/meds-only/xlsx')
            ->set('college_id', (string) $this->business->id)
            ->assertSee('Maria Typed')->assertDontSee('Juan Typed')->assertDontSee('Pedro Typed')
            ->assertSeeHtml('college_id=' . $this->business->id)
            ->set('college_id', '')->set('search', 'acetaminophen')
            ->assertSee('Juan Typed')->assertSee('Maria Typed')->assertDontSee('Pedro Typed');
    }

    // ------------------------------------------------------------------ the downloads

    public function test_the_csv_the_excel_sheet_and_the_pdf_hold_the_same_rows_as_the_report(): void
    {
        $this->actingAs($this->admin);
        $filters = ReportFilters::fromArray([]);
        $report = app(MedsOnlyReportBuilder::class)->build($filters, $this->admin);

        $csv = $this->get(route('activity-logs.export', ['tab' => 'meds_only']))->assertOk()->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $lines = array_map('str_getcsv', preg_split('/\r\n|\n/', trim(substr($csv, 3))));
        $this->assertSame(['Date', 'Name', 'Patient Type', 'Campus', 'College', 'Course', 'Year Level', 'Drugs', 'Recorded By', 'Note'], $lines[0]);
        $this->assertSame(array_column($report['rows'], 'name'), array_column(array_slice($lines, 1), 1), 'one line per record, same order');
        $this->assertSame(['2026-03-05', 'Juan Typed', 'Student', '', 'College of Arts', 'BS Biology', '2nd Year', 'Biogesic 500mg x 6, Cetirizine 10mg x 2', 'Nora Nurse', 'headache'], $lines[3]);
        foreach (array_slice($lines, 1) as $i => $line) {
            $this->assertSame(array_map('strval', ReportCsv::row('meds_only', DispenseRecord::findOrFail($report['rows'][$i]['id']))), $line, 'the CSV line of a record is what the report builds');
        }

        $narrow = array_map('str_getcsv', preg_split('/\r\n|\n/', trim(substr($this->get(route('activity-logs.export', ['tab' => 'meds_only', 'college_id' => $this->arts->id]))->streamedContent(), 3))));
        $this->assertSame(['Pedro Typed', 'Juan Typed'], array_column(array_slice($narrow, 1), 1));

        $pdfHtml = view('activity_logs.reports.meds_only_pdf', ['report' => $report])->render();
        $this->assertStringContainsString('MEDS ONLY', $pdfHtml);
        $this->assertStringContainsString('Biogesic 500mg x 6, Cetirizine 10mg x 2', $pdfHtml);
        $this->assertStringContainsString('3 records, 17 units given', $pdfHtml);
        $this->assertStringContainsString('all dates', $pdfHtml);
        $this->assertStringContainsString('2026-03-01 to 2026-03-31', view('activity_logs.reports.meds_only_pdf', ['report' => app(MedsOnlyReportBuilder::class)->build(ReportFilters::fromArray(['date_from' => '2026-03-01', 'date_to' => '2026-03-31']), $this->admin)])->render());
        $pdf = $this->get(route('activity-logs.meds-only', ['format' => 'pdf']))->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $this->assertStringContainsString('meds_only_', (string) $pdf->headers->get('Content-Disposition'));

        $excel = $this->get(route('activity-logs.meds-only', ['format' => 'xlsx', 'college_id' => $this->arts->id]))->assertOk();
        $cells = collect(IOFactory::load($excel->baseResponse->getFile()->getPathname())->getActiveSheet()->toArray());
        $excelRow = $cells->first(fn ($row) => ($row[1] ?? null) === 'Juan Typed');
        $this->assertNotNull($excelRow);
        $this->assertSame(['2026-03-05', 'Juan Typed', 'Student', '', 'College of Arts', 'BS Biology', '2nd Year', 'Biogesic 500mg x 6, Cetirizine 10mg x 2', 'Nora Nurse', 'headache'], array_map(fn ($cell) => $cell ?? '', array_slice($excelRow, 0, 10)));
        $this->assertNull($cells->first(fn ($row) => ($row[1] ?? null) === 'Maria Typed'), 'the Excel file keeps the filters of the screen');
        $this->assertContains('Ada Admin', $cells->flatten()->all(), 'Prepared by');
        $this->assertNotNull($cells->first(fn ($row) => ($row[0] ?? null) === 'Biogesic' && (int) ($row[4] ?? 0) === 6), 'the units per medicine are in the Excel file');

        $this->assertSame('text/csv; charset=UTF-8', $this->get(route('activity-logs.meds-only', ['format' => 'csv']))->assertOk()->headers->get('Content-Type'));
    }

    public function test_who_may_open_and_download_and_a_bad_format_or_filter_is_harmless(): void
    {
        $this->actingAs($this->nurse)->get(route('staff.activity-logs.meds-only', ['format' => 'csv']))->assertOk();
        $this->actingAs($this->makeDoctor())->get(route('doctors.activity-logs.meds-only', ['format' => 'pdf']))->assertOk();
        $this->actingAs($this->makePatient()->user)->get(route('activity-logs.meds-only', ['format' => 'csv']))->assertForbidden();
        $this->actingAs($this->admin)->get(route('activity-logs.meds-only', ['format' => 'docx']))->assertNotFound();
        $csv = $this->get(route('activity-logs.meds-only', ['format' => 'csv', 'college_id' => '1; DROP TABLE medicine_bills', 'date_from' => "x'--", 'search' => str_repeat('a', 500)]))->assertOk()->streamedContent();
        $this->assertStringContainsString('Name', $csv);
        auth()->logout();
        $this->get(route('activity-logs.meds-only', ['format' => 'csv']))->assertRedirect();
    }

    /** The route's role middleware already keeps a patient out, so only a direct call shows that the action checks the Reports access itself. */
    public function test_the_download_action_refuses_a_user_without_reports_access_even_when_called_directly(): void
    {
        $this->actingAs($this->makePatient()->user);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(\App\Http\Controllers\ActivityLogController::class)->medsOnly(\Illuminate\Http\Request::create('/probe'), 'csv');
    }

    public function test_a_name_that_looks_like_a_formula_is_not_run_by_excel_or_the_csv(): void
    {
        $this->actingAs($this->admin);
        DispenseRecord::medsOnly()->whereKey($this->r[1]->id)->update(['patient_name' => '=HYPERLINK("http://evil.test")', 'note' => '@SUM(1+1)']);

        $csv = $this->get(route('activity-logs.meds-only', ['format' => 'csv']))->assertOk()->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("'@SUM", $csv);

        $excel = $this->get(route('activity-logs.meds-only', ['format' => 'xlsx']))->assertOk();
        $values = collect(IOFactory::load($excel->baseResponse->getFile()->getPathname())->getActiveSheet()->toArray())->flatten()->all();
        $this->assertContains('\'=HYPERLINK("http://evil.test")', $values);
        $this->assertContains("'@SUM(1+1)", $values);
    }
}
