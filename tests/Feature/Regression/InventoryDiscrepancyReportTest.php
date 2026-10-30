<?php

namespace Tests\Feature\Regression;

use App\Livewire\ReportGeneration;
use App\Models\InventoryCount;
use App\Models\InventoryCountLine;
use App\Models\Medicine;
use App\Models\User;
use App\Services\InventoryCountService;
use App\Services\MedicineInventoryService;
use App\Services\Reports\InventoryDiscrepancyReportBuilder;
use App\Services\Reports\YearlyInventoryReportBuilder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * The "Medicines with notes" report (Report Generation > Medicine Inventory > Medicines with notes): what the finalized count
 * sheets found, medicine by medicine, with the reason and note, built by one builder for the screen, the PDF, the Excel sheet
 * and the CSV. By default only the lines that have a difference or a note; a draft sheet and a medicine that was not counted
 * are never in it. The yearly inventory report's Notes also tell what a finalized quarter count found.
 *
 * Ledger as in InventoryCountServiceTest (Alpha ends March at 90, is at 85 today 2026-07-15; Beta is at 40). Sheets:
 *   s1 2026 Q1: Alpha counted 88 (lost, "two boxes missing"; stock corrected 2026-07-16), Beta 0 (no difference);
 *   s2 2026 Q2: Alpha counted 85 (no difference, note "checked twice"), Beta counted 40;
 *   day: a count of everything on 2026-07-14: Alpha counted 84 (damaged, "cracked"), Beta not counted;
 *   draft Q3: Alpha counted 80 (never finalized).
 */
class InventoryDiscrepancyReportTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $admin;

    private User $nurse;

    private Medicine $a;

    private Medicine $b;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->admin = $this->makeAdmin(['first_name' => 'Ada', 'last_name' => 'Admin']);
        $this->nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse']);
        $this->a = $this->makeMedicine('Alpha', ['dosage' => '500mg', 'uom' => 'tablet']);
        $this->b = $this->makeMedicine('Beta', ['dosage' => '10mg', 'uom' => 'capsule']);

        Carbon::setTestNow('2026-02-10 10:00:00');
        $this->stockIn($this->a, 100, '2028-01-01', '500mg', 'LOT-A');
        Carbon::setTestNow('2026-03-05 10:00:00');
        app(MedicineInventoryService::class)->deductStockFefo($this->a->id, 10, $this->nurse->id, null, 'test dispense');
        Carbon::setTestNow('2026-04-10 10:00:00');
        app(MedicineInventoryService::class)->deductStockFefo($this->a->id, 5, $this->nurse->id, null, 'test dispense');
        Carbon::setTestNow('2026-05-01 10:00:00');
        $this->stockIn($this->b, 40, '2028-01-01', '10mg', 'LOT-B');
        Carbon::setTestNow('2026-07-15 12:00:00');

        $this->actingAs($this->nurse);
        $service = app(InventoryCountService::class);
        $make = function (array $sheet, array $rows, bool $finalize = true) use ($service) {
            $count = $service->create($sheet + ['year' => 2026, 'scope' => 'all', 'counted_on' => '2026-07-15'], $this->nurse);
            $service->saveCounts($count, $rows, $this->nurse);
            if ($finalize) {
                $service->finalize($count, $this->nurse);
            }

            return $count;
        };

        $s1 = $make(['quarter' => 1], [$this->a->id => ['counted' => '88', 'reason' => 'lost', 'note' => 'two boxes missing'], $this->b->id => ['counted' => '0']]);
        InventoryCountLine::where('inventory_count_id', $s1->id)->where('medicine_id', $this->a->id)->update(['adjusted_at' => '2026-07-16 08:00:00']);
        $make(['quarter' => 2], [$this->a->id => ['counted' => '85', 'note' => 'checked twice'], $this->b->id => ['counted' => '40']]);
        $make(['quarter' => null, 'counted_on' => '2026-07-14'], [$this->a->id => ['counted' => '84', 'reason' => 'damaged', 'note' => 'cracked']]);
        $make(['quarter' => 3], [$this->a->id => ['counted' => '80', 'reason' => 'lost']], false);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function build(?int $year = null, ?string $period = null, string $show = 'notes'): array
    {
        return app(InventoryDiscrepancyReportBuilder::class)->build($year, $period, $show, $this->admin);
    }

    private function brief(array $report): array
    {
        return array_map(fn (array $row) => $row['sheet'] . ' ' . $row['name'] . ' ' . $row['counted'] . '/' . $row['system'], $report['rows']);
    }

    // ------------------------------------------------------------------ the builder

    public function test_by_default_only_finalized_lines_with_a_difference_or_a_note_are_in_the_report_newest_first(): void
    {
        $report = $this->build();

        $this->assertSame(['2026 Q2 Alpha 85/85', '2026 Q1 Alpha 88/90', '2026, all medicines counted 2026-07-14 Alpha 84/85'], $this->brief($report),
            'no draft, no Beta (no difference, no note, or not counted)');

        $row = $report['rows'][1];
        $this->assertSame(['2026 Q1', '2026-07-15', 'Alpha', '500mg', 'tablet', 90, 88, -2, 'Lost or missing', 'two boxes missing', '2026-07-16'],
            [$row['sheet'], $row['counted_on'], $row['name'], $row['strength'], $row['unit'], $row['system'], $row['counted'], $row['difference'], $row['reason'], $row['note'], $row['corrected']]);
        $this->assertSame(['', ''], [$report['rows'][0]['reason'], $report['rows'][0]['corrected']], 'a note without a difference has no reason and was not corrected');

        $this->assertSame(['sheets' => 3, 'differences' => 2, 'net' => -3, 'corrected' => 1], array_intersect_key($report, array_flip(['sheets', 'differences', 'net', 'corrected'])));
    }

    public function test_show_all_adds_every_counted_line_and_the_filters_narrow_the_report(): void
    {
        $this->assertSame(['2026 Q2 Alpha 85/85', '2026 Q2 Beta 40/40', '2026 Q1 Alpha 88/90', '2026 Q1 Beta 0/0', '2026, all medicines counted 2026-07-14 Alpha 84/85'],
            $this->brief($this->build(null, null, 'all')), 'Beta was not counted on the day sheet: left out');

        $this->assertSame(['2026 Q1 Alpha 88/90'], $this->brief($this->build(2026, '1')));
        $this->assertSame(['2026 Q2 Alpha 85/85'], $this->brief($this->build(2026, '2')));
        $this->assertSame(['2026, all medicines counted 2026-07-14 Alpha 84/85'], $this->brief($this->build(2026, 'day')));
        $this->assertSame([], $this->build(2026, '3')['rows'], 'the Q3 sheet is a draft');
        $this->assertSame([], $this->build(2025)['rows']);
        $this->assertCount(3, $this->build(2026)['rows']);
        $this->assertCount(3, $this->build(null, 'bogus')['rows'], 'an unknown period is ignored');
        $this->assertCount(3, $this->build(null, null, 'bogus')['rows'], 'an unknown view means the default');
    }

    public function test_a_year_that_has_no_sheets_and_the_years_of_the_picker(): void
    {
        $this->assertSame([2026], InventoryDiscrepancyReportBuilder::years());
        $empty = $this->build(2025);
        $this->assertSame([0, 0, 0, 0], [$empty['sheets'], $empty['differences'], $empty['net'], $empty['corrected']]);
    }

    // ------------------------------------------------------------------ the screen

    public function test_the_screen_shows_the_report_with_its_filters_and_downloads_that_carry_them(): void
    {
        $this->actingAs($this->admin);

        Livewire::withQueryParams(['tab' => 'inventory'])->test(ReportGeneration::class)
            ->assertSee('Medicines with notes')
            ->set('inventory_year', 'discrepancies')
            ->assertSee('MEDICINES WITH NOTES')->assertSee('Alpha')->assertSee('Lost or missing')->assertSee('two boxes missing')->assertSee('checked twice')->assertSee('cracked')
            ->assertDontSee('Beta')->assertDontSee('Left from last year')
            ->assertSeeHtml('/inventory-discrepancies/pdf?show=notes')
            ->set('discrepancy_period', '1')
            ->assertSee('two boxes missing')->assertDontSee('checked twice')
            ->assertSeeHtml('/inventory-discrepancies/csv?period=1&amp;show=notes')
            ->set('discrepancy_show', 'all')->set('discrepancy_period', '2')
            ->assertSee('checked twice')->assertSee('Beta')
            ->assertSeeHtml('period=2&amp;show=all');
    }

    // ------------------------------------------------------------------ the downloads

    public function test_the_pdf_the_excel_sheet_and_the_csv_hold_the_same_rows_as_the_screen(): void
    {
        $this->actingAs($this->admin);
        $report = $this->build();
        $route = fn (string $format, array $query = []) => route('activity-logs.inventory-discrepancies', ['format' => $format] + $query);

        $pdfHtml = view('activity_logs.reports.inventory_discrepancies_pdf', ['report' => $report])->render();
        $this->assertStringContainsString('MEDICINES WITH NOTES', $pdfHtml);
        $this->assertStringContainsString('two boxes missing', $pdfHtml);
        $pdf = $this->get($route('pdf'))->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $this->assertStringContainsString('medicines_with_notes_', (string) $pdf->headers->get('Content-Disposition'));

        $excel = $this->get($route('xlsx'))->assertOk();
        $cells = collect(IOFactory::load($excel->baseResponse->getFile()->getPathname())->getActiveSheet()->toArray());
        $excelRow = $cells->first(fn ($row) => ($row[2] ?? null) === 'Alpha' && ($row[0] ?? null) === '2026 Q1');
        $this->assertNotNull($excelRow);
        $this->assertSame(['2026 Q1', '2026-07-15', 'Alpha', '500mg', 'tablet', 90, 88, -2, 'Lost or missing', 'two boxes missing', '2026-07-16'],
            array_map(fn ($cell) => is_numeric($cell) ? (int) $cell : $cell, array_slice($excelRow, 0, 11)));
        $this->assertContains('Ada Admin', $cells->flatten()->all(), 'Prepared by');

        $csv = $this->get($route('csv'))->assertOk()->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $lines = array_map('str_getcsv', preg_split('/\r\n|\n/', trim(substr($csv, 3))));
        $this->assertSame(['Sheet', 'Day counted', 'Medicine', 'Strength', 'Unit', 'System', 'Counted', 'Difference', 'Reason', 'Note', 'Stock corrected'], $lines[0]);
        $this->assertCount(count($report['rows']), array_slice($lines, 1));
        $this->assertSame(['2026 Q1', '2026-07-15', 'Alpha', '500mg', 'tablet', '90', '88', '-2', 'Lost or missing', 'two boxes missing', '2026-07-16'], $lines[2]);

        $filtered = array_map('str_getcsv', preg_split('/\r\n|\n/', trim(substr($this->get($route('csv', ['period' => '2', 'show' => 'all']))->streamedContent(), 3))));
        $this->assertSame(['2026 Q2', '2026 Q2'], array_column(array_slice($filtered, 1), 0));
    }

    public function test_who_may_download_and_a_bad_format_or_year_is_harmless(): void
    {
        $this->actingAs($this->nurse)->get(route('staff.activity-logs.inventory-discrepancies', ['format' => 'csv']))->assertOk();
        $this->actingAs($this->makeDoctor())->get(route('doctors.activity-logs.inventory-discrepancies', ['format' => 'csv']))->assertOk();
        $this->actingAs($this->makePatient()->user)->get(route('activity-logs.inventory-discrepancies', ['format' => 'csv']))->assertForbidden();
        $this->actingAs($this->admin)->get(route('activity-logs.inventory-discrepancies', ['format' => 'docx']))->assertNotFound();
        $csv = $this->get(route('activity-logs.inventory-discrepancies', ['format' => 'csv', 'year' => '2026; DROP TABLE medicines', 'period' => "1'--", 'show' => 'x']))->assertOk()->streamedContent();
        $this->assertStringContainsString('Alpha', $csv);
        auth()->logout();
        $this->get(route('activity-logs.inventory-discrepancies', ['format' => 'csv']))->assertRedirect();
    }

    /** The route's role middleware already keeps a patient out, so only a direct call shows that the action checks the Reports access itself. */
    public function test_the_download_action_refuses_a_user_without_reports_access_even_when_called_directly(): void
    {
        $this->actingAs($this->makePatient()->user);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(\App\Http\Controllers\ActivityLogController::class)->inventoryDiscrepancies(\Illuminate\Http\Request::create('/probe'), 'csv');
    }

    public function test_a_name_or_note_that_looks_like_a_formula_is_not_run_by_excel_or_the_csv(): void
    {
        $this->actingAs($this->admin);
        InventoryCountLine::query()->update(['note' => '=HYPERLINK("http://evil.test")']);

        $csv = $this->get(route('activity-logs.inventory-discrepancies', ['format' => 'csv']))->assertOk()->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString(',=HYPERLINK', $csv);

        $excel = $this->get(route('activity-logs.inventory-discrepancies', ['format' => 'xlsx']))->assertOk();
        $values = collect(IOFactory::load($excel->baseResponse->getFile()->getPathname())->getActiveSheet()->toArray())->flatten()->all();
        $this->assertContains('\'=HYPERLINK("http://evil.test")', $values);
    }

    // ------------------------------------------------------------------ the yearly report tells it too

    public function test_the_yearly_inventory_notes_tell_what_a_finalized_quarter_count_found(): void
    {
        $row = collect(app(YearlyInventoryReportBuilder::class)->build(2026)['rows'])->firstWhere('name', 'Alpha');

        $this->assertStringContainsString('Q1 counted 88 (system 90, -2, Lost or missing: two boxes missing)', $row['notes']);
        $this->assertStringNotContainsString('Q2 counted', $row['notes'], 'a count that matches the system says nothing');
        $this->assertStringNotContainsString('Q3 counted', $row['notes'], 'a draft sheet says nothing');
        $this->assertStringNotContainsString('cracked', $row['notes'], 'a count of everything on a day belongs to no quarter');

        // the latest finalized count of a quarter is the one that counts
        $service = app(InventoryCountService::class);
        $again = $service->create(['year' => 2026, 'quarter' => 1, 'scope' => 'all', 'counted_on' => '2026-07-15'], $this->nurse);
        $service->saveCounts($again, [$this->a->id => ['counted' => '87', 'reason' => 'damaged']], $this->nurse);
        $service->finalize($again, $this->nurse);
        $row = collect(app(YearlyInventoryReportBuilder::class)->build(2026)['rows'])->firstWhere('name', 'Alpha');
        $this->assertStringContainsString('Q1 counted 87 (system 90, -3, Damaged)', $row['notes']);
        $this->assertStringNotContainsString('Q1 counted 88', $row['notes']);
    }
}
