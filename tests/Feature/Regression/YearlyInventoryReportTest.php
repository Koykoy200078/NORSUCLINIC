<?php

namespace Tests\Feature\Regression;

use App\Livewire\ReportGeneration;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineTransaction;
use App\Models\User;
use App\Services\MedicineInventoryService;
use App\Services\Reports\YearlyInventoryReportBuilder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 4.5: the YEARLY MEDICINE INVENTORY report (Report Generation > Medicine Inventory > Yearly). One row per
 * medicine with what was left from last year, what was added this year and what remained at the end of each
 * quarter, built from the stock ledger (each batch's `balance_after` at the moment), so returns, disposals and
 * corrections are counted correctly. The screen, the PDF, the Excel sheet and the CSV come from one builder.
 *
 * Every movement is made through the real inventory service on a fixed date (Carbon::setTestNow) so the ledger really
 * crosses the quarter and year boundaries.
 */
class YearlyInventoryReportTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $admin;

    private MedicineInventoryService $stock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->admin = $this->makeAdmin(['first_name' => 'Ada', 'last_name' => 'Admin']);
        $this->stock = app(MedicineInventoryService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function medicine(string $name, string $unit, string $strength, ?string $generic = null): Medicine
    {
        return $this->makeMedicine($name, ['uom' => $unit, 'dosage' => $strength, 'generic_name' => $generic ?? $name]);
    }

    private function stockIn(Medicine $medicine, int $quantity, string $expiry, string $batch): void
    {
        $this->stock->recordStockIn([
            'medicine_id' => $medicine->id, 'quantity' => $quantity, 'dosage' => $medicine->dosage,
            'batch_number' => $batch, 'expiration_date' => $expiry,
        ]);
    }

    private function dispense(Medicine $medicine, int $quantity): void
    {
        $this->stock->deductStockFefo($medicine->id, $quantity, $this->admin->id, null, 'Test dispense');
    }

    /** What the Stock-out screen's disposal does to the ledger: the batch goes to 0 and a `disposal` line says so. */
    private function dispose(string $batchNumber): void
    {
        $batch = MedicineBatch::where('batch_number', $batchNumber)->firstOrFail();
        $units = (int) $batch->quantity;
        $batch->update(['quantity' => 0]);
        MedicineTransaction::create([
            'batch_id' => $batch->id, 'user_id' => $this->admin->id, 'transaction_type' => MedicineTransaction::TYPE_DISPOSAL,
            'quantity' => $units, 'balance_after' => 0, 'remarks' => 'Expired stock disposed',
        ]);
    }

    /**
     * The clinic's stock over two years, every movement on its own date. Nothing after $through is made.
     *
     * @return array<string, Medicine>
     */
    private function scenario(string $through = '2026-12-31 23:59:59'): array
    {
        $a = $this->medicine('Paracetamol', 'tablet', '500mg');
        $b = $this->medicine('Amoxicillin', 'capsule', '250mg', 'Amoxicillin trihydrate');
        $c = $this->medicine('Oldmed', 'box', '10mg');
        $d = $this->medicine('Expirymed', 'bottle', '60ml');
        $e = $this->medicine('Neverstocked', 'tube', '1%');

        $events = [
            ['2024-06-01 10:00:00', fn () => $this->stockIn($c, 500, '2030-01-01', 'C1')],
            ['2025-06-01 10:00:00', fn () => $this->dispense($c, 500)],
            ['2025-11-15 10:00:00', fn () => $this->stockIn($a, 100, '2027-12-31', 'A1')],
            ['2025-12-20 09:00:00', fn () => $this->dispense($a, 30)],
            // 2026: the first moment of the year belongs to the year, not to what was left
            ['2026-01-01 00:00:00', fn () => $this->stockIn($d, 80, '2026-02-28', 'D1')],
            ['2026-01-10 08:00:00', fn () => $this->stockIn($a, 50, '2026-05-31', 'A2')],
            ['2026-02-01 09:00:00', fn () => $this->stockIn($b, 200, '2027-06-30', 'B1')],
            ['2026-02-05 11:00:00', fn () => $this->dispense($a, 20)],
            // the last second of March is the first quarter, the first second of April is the second
            ['2026-03-31 23:59:59', fn () => $this->dispense($b, 40)],
            ['2026-04-01 00:00:00', fn () => $this->stockIn($b, 10, '2027-06-30', 'B1')],
            ['2026-04-20 09:00:00', fn () => $this->stock->restoreStock($a->id, 5, $this->admin->id, null, 'Returned', '500mg', null)],
            ['2026-05-10 09:00:00', fn () => $this->dispense($a, 10)],
            ['2026-05-20 10:00:00', fn () => $this->stock->reverseStockIn($b->id, 5, MedicineBatch::where('batch_number', 'B1')->value('id'), '250mg', null, $this->admin->id, null, 'Stock-in reversed')],
            ['2026-07-20 10:00:00', fn () => $this->dispose('A2')],
            ['2026-10-05 10:00:00', fn () => $this->dispense($a, 15)],
        ];

        foreach ($events as [$when, $do]) {
            if ($when > $through) {
                break;
            }
            Carbon::setTestNow($when);
            $do();
        }
        Carbon::setTestNow();

        return compact('a', 'b', 'c', 'd', 'e');
    }

    private function build(int $year, string $now): array
    {
        Carbon::setTestNow($now);

        return app(YearlyInventoryReportBuilder::class)->build($year, $this->admin);
    }

    /** @return array<string, array<string, mixed>> medicine name => row */
    private function rows(array $report): array
    {
        return collect($report['rows'])->keyBy('name')->all();
    }

    /** @return array<int, int|null> Q1..Q4 remaining */
    private function remaining(array $row): array
    {
        return array_map(fn ($key) => $row['quarters'][$key]['remaining'], ['q1', 'q2', 'q3', 'q4']);
    }

    public function test_a_finished_year_has_what_was_left_what_was_added_and_what_remained_at_every_quarter_end(): void
    {
        $this->scenario();
        $report = $this->build(2026, '2027-02-15 09:00:00');
        $rows = $this->rows($report);

        $this->assertSame(2026, $report['year']);
        $this->assertSame(['Q1', 'Q2', 'Q3', 'Q4'], array_column($report['quarters'], 'label'));

        // Paracetamol: 70 left from 2025; +50; 100 at March, 95 at June (20 of them expired), 75 at September, 60 at year end.
        $paracetamol = $rows['Paracetamol'];
        $this->assertSame(70, $paracetamol['left']);
        $this->assertSame(50, $paracetamol['additional']);
        $this->assertSame([100, 95, 75, 60], $this->remaining($paracetamol));

        // Amoxicillin: nothing left; the dispense at 23:59:59 on March 31 is the first quarter, the stock-in at 00:00:00 on April 1 the second.
        // 5 of the units were taken out again on May 20 (a stock-in reversed): a correction that lowers the stock.
        $amoxicillin = $rows['Amoxicillin'];
        $this->assertSame(0, $amoxicillin['left']);
        $this->assertSame(210, $amoxicillin['additional']);
        $this->assertSame([160, 165, 165, 165], $this->remaining($amoxicillin));

        // The first second of the year is part of the year: 80 added, none left.
        $this->assertSame(0, $rows['Expirymed']['left']);
        $this->assertSame(80, $rows['Expirymed']['additional']);

        // Names, generic name, strength and the medicine's own unit.
        $this->assertSame(['tablet', '500mg', 'Paracetamol'], [$paracetamol['unit'], $paracetamol['strength'], $paracetamol['generic']]);
        $this->assertSame(['capsule', '250mg', 'Amoxicillin trihydrate'], [$amoxicillin['unit'], $amoxicillin['strength'], $amoxicillin['generic']]);
        $this->assertSame('bottle', $rows['Expirymed']['unit']);
    }

    public function test_the_rows_are_the_medicines_that_had_stock_or_movement_that_year_sorted_by_name(): void
    {
        $this->scenario();

        // A medicine whose only ledger line of the year moved nothing (a count that found no difference) is no row.
        $zero = $this->medicine('Zeromed', 'tablet', '5mg');
        $emptyBatch = MedicineBatch::create(['medicine_id' => $zero->id, 'batch_number' => 'Z1', 'dosage' => '5mg', 'quantity' => 0, 'expiration_date' => '2030-01-01', 'date_received' => '2026-02-01']);
        Carbon::setTestNow('2026-02-02 10:00:00');
        MedicineTransaction::create([
            'batch_id' => $emptyBatch->id, 'user_id' => $this->admin->id, 'transaction_type' => MedicineTransaction::TYPE_ADJUSTMENT,
            'quantity' => 0, 'balance_after' => 0, 'remarks' => 'Count: nothing differed',
        ]);
        Carbon::setTestNow();

        $this->assertSame(['Amoxicillin', 'Expirymed', 'Paracetamol'], array_column($this->build(2026, '2027-02-15')['rows'], 'name'), 'Oldmed ran out in 2025, Neverstocked never had stock, Zeromed only had a line that moved nothing');

        // 2025: Oldmed was used up in June, Paracetamol started in November.
        $report = $this->build(2025, '2026-02-15');
        $rows = $this->rows($report);
        $this->assertSame(['Oldmed', 'Paracetamol'], array_column($report['rows'], 'name'));
        $this->assertSame(500, $rows['Oldmed']['left']);
        $this->assertSame([500, 0, 0, 0], $this->remaining($rows['Oldmed']));
        $this->assertSame(0, $rows['Paracetamol']['left']);
        $this->assertSame(100, $rows['Paracetamol']['additional']);
        $this->assertSame([0, 0, 0, 70], $this->remaining($rows['Paracetamol']), 'year end of 2025 is next year\'s "left from last year"');
        $this->assertSame($this->rows($this->build(2026, '2027-02-15'))['Paracetamol']['left'], $this->remaining($rows['Paracetamol'])[3]);

        // A year before any stock: nothing, but the report is still a report.
        $empty = $this->build(2019, '2027-02-15');
        $this->assertSame([], $empty['rows']);
        $this->assertSame(['Q1', 'Q2', 'Q3', 'Q4'], array_column($empty['quarters'], 'label'));
    }

    public function test_the_year_in_progress_stops_at_today_and_a_quarter_in_the_future_is_blank(): void
    {
        $this->scenario('2026-08-15 12:00:00');
        $report = $this->build(2026, '2026-08-15 12:00:00');
        $paracetamol = $this->rows($report)['Paracetamol'];

        $this->assertSame([100, 95, 75, null], $this->remaining($paracetamol), 'the fourth quarter has not started');
        $this->assertTrue($report['quarters'][2]['to_date'], 'September is still going: its figure is the stock of today');
        $this->assertFalse($report['quarters'][1]['to_date']);
        $this->assertTrue($report['quarters'][3]['blank']);

        // Q-now equals what is on the shelf today, for every medicine.
        foreach ($report['rows'] as $row) {
            $this->assertSame($row['stock_today'], $row['ending'], $row['name']);
            $this->assertTrue($row['matches_today']);
        }
        $this->assertSame(75, $paracetamol['stock_today']);
        $this->assertSame((int) MedicineBatch::where('medicine_id', Medicine::where('name', 'Paracetamol')->value('id'))->sum('quantity'), $paracetamol['stock_today']);
    }

    public function test_the_current_year_is_compared_with_the_shelf_and_a_difference_is_reported_not_hidden(): void
    {
        $this->scenario('2026-08-15 12:00:00');
        // Somebody changed a batch without going through the inventory, so the ledger knows nothing about the 3 units.
        $batch = MedicineBatch::where('batch_number', 'A1')->firstOrFail();
        $batch->update(['quantity' => $batch->quantity + 3]);

        $report = $this->build(2026, '2026-08-15 12:00:00');
        $paracetamol = $this->rows($report)['Paracetamol'];

        $this->assertSame(75, $paracetamol['ending']);
        $this->assertSame(78, $paracetamol['stock_today']);
        $this->assertFalse($paracetamol['matches_today']);
        $this->assertTrue($paracetamol['consistent'], 'the ledger itself still adds up: only the shelf differs');
        $this->assertStringContainsString('The stock today (78) differs from the ledger (75)', $paracetamol['notes']);
        $this->assertTrue($this->rows($report)['Amoxicillin']['matches_today'], 'a medicine nobody touched still agrees');

        // Only the current year is compared with the shelf: a finished year is a fact of the ledger.
        $past = $this->rows($this->build(2025, '2026-08-15 12:00:00'))['Paracetamol'];
        $this->assertNull($past['stock_today']);
        $this->assertTrue($past['matches_today']);
        $this->assertStringNotContainsString('differs', $past['notes']);
    }

    public function test_left_plus_additional_minus_used_and_disposed_plus_adjustments_is_the_year_end_remaining(): void
    {
        $this->scenario();
        $report = $this->build(2026, '2027-02-15');
        $rows = $this->rows($report);

        $paracetamol = $rows['Paracetamol'];
        $this->assertSame(45, $paracetamol['used'], '20 + 10 + 15 dispensed');
        $this->assertSame(20, $paracetamol['disposed']);
        $this->assertSame(5, $paracetamol['adjusted'], 'the 5 units that came back');
        $this->assertSame(60, $paracetamol['left'] + $paracetamol['additional'] - $paracetamol['used'] - $paracetamol['disposed'] + $paracetamol['adjusted']);
        $this->assertSame($paracetamol['ending'], 60);

        // Amoxicillin: 210 added, 40 used, and a correction that took 5 out again (a NEGATIVE adjustment).
        $amoxicillin = $rows['Amoxicillin'];
        $this->assertSame([210, 40, 0, -5, 165], [$amoxicillin['additional'], $amoxicillin['used'], $amoxicillin['disposed'], $amoxicillin['adjusted'], $amoxicillin['ending']]);

        foreach ($report['rows'] as $row) {
            $this->assertTrue($row['consistent'], $row['name'] . ': left + additional - used - disposed +/- adjustments = year-end remaining');
            $this->assertSame($row['ending'], $row['left'] + $row['additional'] - $row['used'] - $row['disposed'] + $row['adjusted'], $row['name']);
        }
        $this->assertSame(0, $report['inconsistent']);
    }

    public function test_stock_that_expired_on_the_shelf_is_counted_with_a_note_until_it_is_disposed(): void
    {
        $this->scenario();
        $report = $this->build(2026, '2027-02-15');
        $rows = $this->rows($report);

        // Paracetamol batch A2 expired on May 31, was still on the shelf on June 30 and was disposed in July.
        $this->assertSame([0, 20, 0, 0], array_map(fn ($key) => $rows['Paracetamol']['quarters'][$key]['expired'], ['q1', 'q2', 'q3', 'q4']));
        $this->assertSame(95, $rows['Paracetamol']['quarters']['q2']['remaining'], 'expired units are still counted');
        $this->assertStringContainsString('expired', $rows['Paracetamol']['notes']);
        $this->assertStringContainsString('Q2 20', $rows['Paracetamol']['notes']);

        // Expirymed was never disposed: expired from March on.
        $this->assertSame([80, 80, 80, 80], array_map(fn ($key) => $rows['Expirymed']['quarters'][$key]['expired'], ['q1', 'q2', 'q3', 'q4']));
        $this->assertSame('', $rows['Amoxicillin']['notes']);
    }

    public function test_stock_is_expired_from_the_day_after_its_expiry_date_not_on_the_day_itself(): void
    {
        $edge = $this->medicine('Edgemed', 'tablet', '1mg');
        Carbon::setTestNow('2026-01-15 09:00:00');
        $this->stockIn($edge, 10, '2026-03-31', 'E1');
        Carbon::setTestNow();

        $row = $this->rows($this->build(2026, '2027-02-15'))['Edgemed'];

        $this->assertSame([10, 10, 10, 10], $this->remaining($row));
        $this->assertSame([0, 10, 10, 10], array_map(fn ($key) => $row['quarters'][$key]['expired'], ['q1', 'q2', 'q3', 'q4']), 'still good on March 31 itself, expired from April 1');
    }

    public function test_a_ledger_line_that_does_not_add_up_is_flagged_not_hidden(): void
    {
        $this->scenario();
        // Somebody changed a recorded quantity by hand: the dispense says 20 but the batch only went down by 20 -> make it 25.
        MedicineTransaction::where('transaction_type', 'dispense')->where('quantity', 20)->update(['quantity' => 25]);

        $report = $this->build(2026, '2027-02-15');
        $paracetamol = $this->rows($report)['Paracetamol'];

        $this->assertFalse($paracetamol['consistent']);
        $this->assertGreaterThanOrEqual(1, $report['inconsistent']);
        $this->assertStringContainsString('does not add up', $paracetamol['notes']);
        $this->assertSame([100, 95, 75, 60], $this->remaining($paracetamol), 'the remaining figures still come from the ledger balances');
    }

    public function test_two_wrong_ledger_lines_that_cancel_each_other_out_are_still_flagged(): void
    {
        $this->scenario();
        // The dispenses of the second and the third quarter say 20 and 10; change them to 15 and 15 so the sum still holds.
        MedicineTransaction::where('transaction_type', 'dispense')->where('quantity', 20)->update(['quantity' => 15]);
        MedicineTransaction::where('transaction_type', 'dispense')->where('quantity', 10)->update(['quantity' => 15]);

        $paracetamol = $this->rows($this->build(2026, '2027-02-15'))['Paracetamol'];

        $this->assertSame(45, $paracetamol['used'], 'the totals still agree with the balances');
        $this->assertSame($paracetamol['ending'], $paracetamol['left'] + $paracetamol['additional'] - $paracetamol['used'] - $paracetamol['disposed'] + $paracetamol['adjusted']);
        $this->assertFalse($paracetamol['consistent'], 'but two lines no longer match what they did to the batch');
        $this->assertStringContainsString('does not add up', $paracetamol['notes']);
    }

    public function test_the_screen_the_pdf_the_excel_sheet_and_the_csv_show_the_same_figures(): void
    {
        $this->scenario();
        Carbon::setTestNow('2027-02-15 09:00:00');
        $this->actingAs($this->admin);
        $report = app(YearlyInventoryReportBuilder::class)->build(2026, $this->admin);

        // Screen
        Livewire::withQueryParams(['tab' => 'inventory'])->test(ReportGeneration::class)
            ->set('inventory_year', '2026')
            ->assertSee('Left from last year')
            ->assertSee('Additional')
            ->assertSee('Paracetamol')
            ->assertSee('Amoxicillin trihydrate')
            ->assertSee('500mg')
            ->assertSee('tablet')
            ->assertSee('Q1')->assertSee('Q4')
            ->assertDontSee('Neverstocked')
            ->assertDontSee('Reorder at')
            // the three download links carry the year that is on the screen, not "this year"
            ->assertSeeHtml('/inventory-yearly/pdf?year=2026')
            ->assertSeeHtml('/inventory-yearly/xlsx?year=2026')
            ->assertSeeHtml('/inventory-yearly/csv?year=2026');

        // PDF / Excel sheet are rendered from the same Blade tables
        $pdfHtml = view('activity_logs.reports.inventory_yearly_pdf', ['report' => $report])->render();
        $this->assertStringContainsString('YEARLY MEDICINE INVENTORY', $pdfHtml);
        $this->assertStringContainsString('Paracetamol', $pdfHtml);
        $pdf = $this->get(route('activity-logs.inventory-yearly', ['format' => 'pdf', 'year' => 2026]))->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $this->assertStringContainsString('medicine_inventory_2026_', (string) $pdf->headers->get('Content-Disposition'));

        $excel = $this->get(route('activity-logs.inventory-yearly', ['format' => 'xlsx', 'year' => 2026]))->assertOk();
        $sheet = IOFactory::load($excel->baseResponse->getFile()->getPathname())->getActiveSheet();
        $cells = collect($sheet->toArray());
        $excelRow = $cells->first(fn ($row) => ($row[0] ?? null) === 'Paracetamol');
        $this->assertNotNull($excelRow);
        $this->assertSame(['Paracetamol', 'Paracetamol', '500mg', 'tablet', 70, 50, 100, 95, 75, 60], array_map(fn ($cell) => is_numeric($cell) ? (int) $cell : $cell, array_slice($excelRow, 0, 10)));
        $this->assertContains('Ada Admin', $cells->flatten()->all(), 'Prepared by');

        // CSV: the same columns in the same order
        $csv = $this->get(route('activity-logs.inventory-yearly', ['format' => 'csv', 'year' => 2026]))->assertOk()->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $lines = array_map('str_getcsv', preg_split('/\r\n|\n/', trim(substr($csv, 3))));
        $this->assertSame(['Medicine', 'Generic', 'Strength', 'Unit', 'Left from last year', 'Additional', 'Q1 remaining', 'Q2 remaining', 'Q3 remaining', 'Q4 remaining', 'Notes'], $lines[0]);
        $paracetamol = collect($lines)->first(fn ($line) => ($line[0] ?? null) === 'Paracetamol');
        $this->assertSame(['70', '50', '100', '95', '75', '60'], array_slice($paracetamol, 4, 6));
        $this->assertCount(3, array_slice($lines, 1), 'one line per medicine of the report');
    }

    public function test_a_future_quarter_is_blank_in_every_output(): void
    {
        $this->scenario('2026-08-15 12:00:00');
        Carbon::setTestNow('2026-08-15 12:00:00');
        $this->actingAs($this->admin);

        $csv = $this->get(route('activity-logs.inventory-yearly', ['format' => 'csv', 'year' => 2026]))->assertOk()->streamedContent();
        $lines = array_map('str_getcsv', preg_split('/\r\n|\n/', trim(substr($csv, 3))));
        $paracetamol = collect($lines)->first(fn ($line) => ($line[0] ?? null) === 'Paracetamol');
        $this->assertSame(['100', '95', '75', ''], array_slice($paracetamol, 6, 4));

        $screen = Livewire::withQueryParams(['tab' => 'inventory'])->test(ReportGeneration::class)->set('inventory_year', '2026');
        $screen->assertSee('to date')->assertSeeHtml('class="inv-blank"');
    }

    public function test_the_report_screen_offers_the_years_of_the_ledger_and_keeps_the_current_stock_list_by_default(): void
    {
        $this->scenario();
        Carbon::setTestNow('2026-12-31 12:00:00');
        $this->actingAs($this->admin);

        $screen = Livewire::withQueryParams(['tab' => 'inventory'])->test(ReportGeneration::class);
        $screen->assertSee('Current Stock')->assertSee('Reorder at')
            ->assertSee('Current stock')
            ->assertSee('Export CSV')->assertSee('Search Keywords')->assertSee('Stock Status')
            ->assertSeeHtml('<option value="2024">')->assertSeeHtml('<option value="2025">')->assertSeeHtml('<option value="2026">');
        $this->assertSame('', $screen->get('inventory_year'));

        // The yearly report has downloads of its own: the stock list's export, search box and stock-status filter go away.
        $screen->set('inventory_year', '2025')->assertSee('Left from last year')->assertDontSee('Reorder at')
            ->assertDontSee('Export CSV')->assertDontSee('Search Keywords')->assertDontSee('Stock Status');
        $screen->call('resetFilters');
        $this->assertSame('', $screen->get('inventory_year'));

        // The year belongs to the Medicine Inventory tab only.
        $screen->set('inventory_year', '2025')->call('setTab', 'visits')->assertDontSee('Left from last year');
    }

    public function test_the_yearly_inventory_is_open_to_everyone_who_may_see_reports_and_refuses_unknown_formats_and_years(): void
    {
        $this->scenario();
        Carbon::setTestNow('2027-02-15 09:00:00');

        $this->actingAs($this->admin)->get(route('activity-logs.inventory-yearly', ['format' => 'docx', 'year' => 2026]))->assertNotFound();

        $nurse = $this->makeNurse();
        $this->actingAs($nurse)->get(route('staff.activity-logs.inventory-yearly', ['format' => 'csv', 'year' => 2026]))->assertOk();
        // Reports stay open when every optional role permission has been revoked.
        \App\Models\Role::findByName('staff')->syncPermissions([]);
        $this->actingAs($nurse->fresh())->get(route('staff.activity-logs.inventory-yearly', ['format' => 'csv', 'year' => 2026]))->assertOk();
        $this->actingAs($this->makeDoctor())->get(route('doctors.activity-logs.inventory-yearly', ['format' => 'csv', 'year' => 2026]))->assertOk();

        // A guest and a patient are turned away.
        auth()->logout();
        $this->get(route('activity-logs.inventory-yearly', ['format' => 'csv', 'year' => 2026]))->assertRedirect();
        $this->actingAs($this->makePatient()->user)->get(route('activity-logs.inventory-yearly', ['format' => 'csv', 'year' => 2026]))->assertForbidden();

        // No year, or a year that is not one, means this year (never a query on junk).
        $this->actingAs($this->admin);
        $csv = $this->get(route('activity-logs.inventory-yearly', ['format' => 'csv', 'year' => '2026; DROP TABLE medicines']))->assertOk()->streamedContent();
        $this->assertStringContainsString('Q1 remaining', $csv);
        $this->assertSame(1, DB::table('medicines')->where('name', 'Paracetamol')->count());
    }

    public function test_a_name_that_looks_like_a_formula_is_not_run_by_excel_or_the_csv(): void
    {
        $this->medicine('=HYPERLINK("http://x","y")', 'tablet', '+1mg');
        Carbon::setTestNow('2026-03-01 09:00:00');
        $this->stockIn(Medicine::where('name', 'like', '=HYPER%')->firstOrFail(), 5, '2028-01-01', 'F1');
        $this->actingAs($this->admin);

        $csv = $this->get(route('activity-logs.inventory-yearly', ['format' => 'csv', 'year' => 2026]))->assertOk()->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);

        $excel = $this->get(route('activity-logs.inventory-yearly', ['format' => 'xlsx', 'year' => 2026]))->assertOk();
        $sheet = IOFactory::load($excel->baseResponse->getFile()->getPathname())->getActiveSheet();
        foreach ($sheet->getCellCollection()->getCoordinates() as $coordinate) {
            $this->assertNotSame('f', $sheet->getCell($coordinate)->getDataType(), "{$coordinate} must not be a formula");
        }
    }
}
