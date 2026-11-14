<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\InventoryCount;
use App\Models\InventoryCountLine;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineTransaction;
use App\Models\User;
use App\Services\InventoryCountService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * The owner's decision of 2026-11-14 (Meds Only / Phase 11 default 3): a finalized count sheet's differences can be applied to
 * the stock in one go. A shortage is taken from the batches that expire first (as a dispense would), extra units are added to the
 * batch that expires first; each change is a batch count in the stock ledger with its audit row, and the line is marked
 * corrected. A line the per-line rule refuses (the shelf moved since the count), a medicine with more than one strength and a
 * surplus with no batch to add to are left for the per-batch Correct stock screen.
 *
 * Fixture (a count of everything on 2026-07-15): Alpha 50 (exp 2028-01) + 35 (exp 2028-06), counted 80; Delta 3 (exp 2027-01) +
 * 10 (exp 2028-01), counted 8; Echo 10 (exp 2028-06) + 10 (exp 2027-06), counted 23; Foxtrot 10 x 250mg + 10 x 500mg, counted
 * 15; Golf nothing in stock, counted 5; Hotel 20, counted 18, then 5 more stocked in; Bravo 40, counted 40.
 */
class InventoryCountCorrectAllTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $nurse;

    /** @var array<string, Medicine> */
    private array $medicines = [];

    /** @var array<string, MedicineBatch> */
    private array $batches = [];

    private InventoryCount $sheet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse']);
        foreach (['Alpha', 'Bravo', 'Delta', 'Echo', 'Foxtrot', 'Golf', 'Hotel'] as $name) {
            $this->medicines[$name] = $this->makeMedicine($name, ['dosage' => '500mg', 'uom' => 'tablet']);
        }

        Carbon::setTestNow('2026-06-01 09:00:00');
        $this->batches['A1'] = $this->stockIn($this->medicines['Alpha'], 50, '2028-01-01', '500mg', 'A1');
        $this->batches['A2'] = $this->stockIn($this->medicines['Alpha'], 35, '2028-06-01', '500mg', 'A2');
        $this->batches['B1'] = $this->stockIn($this->medicines['Bravo'], 40, '2028-01-01', '500mg', 'B1');
        $this->batches['D1'] = $this->stockIn($this->medicines['Delta'], 3, '2027-01-01', '500mg', 'D1');
        $this->batches['D2'] = $this->stockIn($this->medicines['Delta'], 10, '2028-01-01', '500mg', 'D2');
        $this->batches['E1'] = $this->stockIn($this->medicines['Echo'], 10, '2028-06-01', '500mg', 'E1');
        $this->batches['E2'] = $this->stockIn($this->medicines['Echo'], 10, '2027-06-01', '500mg', 'E2');
        $this->batches['F1'] = $this->stockIn($this->medicines['Foxtrot'], 10, '2028-01-01', '250mg', 'F1');
        $this->batches['F2'] = $this->stockIn($this->medicines['Foxtrot'], 10, '2028-01-01', '500mg', 'F2');
        $this->batches['H1'] = $this->stockIn($this->medicines['Hotel'], 20, '2028-01-01', '500mg', 'H1');
        Carbon::setTestNow('2026-07-15 12:00:00');

        $this->actingAs($this->nurse);
        $service = app(InventoryCountService::class);
        $this->sheet = $service->create(['year' => 2026, 'quarter' => null, 'scope' => 'all', 'counted_on' => '2026-07-15'], $this->nurse);
        $counted = ['Alpha' => 80, 'Bravo' => 40, 'Delta' => 8, 'Echo' => 23, 'Foxtrot' => 15, 'Golf' => 5, 'Hotel' => 18];
        $service->saveCounts($this->sheet, collect($counted)->mapWithKeys(fn ($units, $name) => [
            $this->medicines[$name]->id => ['counted' => (string) $units, 'reason' => 'counting_error'],
        ])->all(), $this->nurse);
        $service->finalize($this->sheet, $this->nurse);

        // after the count the shelf of Hotel moved, so its line stays a record
        $this->stockIn($this->medicines['Hotel'], 5, '2028-02-01', '500mg', 'H2');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function line(string $name): InventoryCountLine
    {
        return $this->sheet->lines()->where('medicine_id', $this->medicines[$name]->id)->firstOrFail();
    }

    private function units(string $batch): int
    {
        return (int) $this->batches[$batch]->fresh()->quantity;
    }

    private function correctAll()
    {
        return $this->post(route('staff.inventory-counts.correct-all', $this->sheet));
    }

    public function test_a_shortage_leaves_the_batches_that_expire_first_and_a_surplus_joins_the_batch_that_expires_first(): void
    {
        $this->correctAll()->assertRedirect(route('staff.inventory-counts.show', $this->sheet))->assertSessionHasNoErrors();

        $this->assertSame([45, 35], [$this->units('A1'), $this->units('A2')], 'Alpha: 5 missing, all from the batch that expires first');
        $this->assertSame([0, 8], [$this->units('D1'), $this->units('D2')], 'Delta: 5 missing, the 3 of the first batch then 2 of the next');
        $this->assertSame([10, 13], [$this->units('E1'), $this->units('E2')], 'Echo: 3 extra, to E2, which expires first');
        $this->assertSame(40, $this->units('B1'), 'Bravo had no difference');

        foreach (['Alpha', 'Delta', 'Echo'] as $name) {
            $line = $this->line($name);
            $this->assertNotNull($line->adjusted_at, "{$name} is marked corrected");
            $this->assertSame($this->nurse->id, (int) $line->adjusted_by);
            $audit = ActivityLog::findOrFail($line->adjusted_audit_id);
            $this->assertSame('stock_count', $audit->action);
            $this->assertSame($line->id, (int) $audit->properties['count_line_id']);
            $this->assertSame((int) $line->counted_quantity, (int) MedicineBatch::where('medicine_id', $line->medicine_id)->sum('quantity'), "{$name}: the shelf is the count");
        }

        // every batch that changed has its ledger line, referring to the audit row
        $ledger = MedicineTransaction::whereIn('batch_id', [$this->batches['A1']->id, $this->batches['D1']->id, $this->batches['D2']->id, $this->batches['E2']->id])
            ->where('transaction_type', MedicineTransaction::TYPE_ADJUSTMENT)->get();
        $this->assertCount(4, $ledger);
        $this->assertSame([0, 8, 13, 45], $ledger->pluck('balance_after')->map(fn ($units) => (int) $units)->sort()->values()->all());
    }

    public function test_the_lines_that_cannot_be_corrected_at_once_are_left_for_the_per_batch_screen_and_say_why(): void
    {
        $this->correctAll()->assertSessionHas('skipped');
        $skipped = session('skipped');

        $this->assertSame(['Foxtrot', 'Golf', 'Hotel'], array_keys($skipped));
        $this->assertStringContainsString('more than one strength', $skipped['Foxtrot']);
        $this->assertStringContainsString('no batch', $skipped['Golf']);
        $this->assertStringContainsString('shelf changed', $skipped['Hotel']);
        $this->assertSame([10, 10, 20], [$this->units('F1'), $this->units('F2'), $this->units('H1')], 'nothing of theirs moved');
        $this->assertSame(0, InventoryCountLine::whereIn('id', [$this->line('Foxtrot')->id, $this->line('Golf')->id, $this->line('Hotel')->id])->whereNotNull('adjusted_at')->count());
        $this->assertSame('3 line(s) corrected at once: the stock now matches the count.', session('success'));
    }

    public function test_the_sheet_offers_the_button_with_the_number_it_will_correct_and_a_second_press_changes_nothing(): void
    {
        $this->get(route('staff.inventory-counts.show', $this->sheet))->assertOk()
            ->assertSee(route('staff.inventory-counts.correct-all', $this->sheet))
            ->assertSee('Correct all differences (3)');

        $this->correctAll();
        $before = MedicineTransaction::count();

        $this->get(route('staff.inventory-counts.show', $this->sheet))->assertOk()->assertDontSee('Correct all differences');
        $this->correctAll()->assertSessionHas('success', 'Nothing was corrected: no line can be corrected at once.');
        $this->assertSame($before, MedicineTransaction::count());
    }

    public function test_a_draft_is_not_corrected_and_a_doctor_has_neither_the_button_nor_the_route(): void
    {
        $service = app(InventoryCountService::class);
        $draft = $service->create(['year' => 2026, 'quarter' => null, 'scope' => 'all', 'counted_on' => '2026-07-14'], $this->nurse);
        $service->saveCounts($draft, [$this->medicines['Bravo']->id => ['counted' => '30', 'reason' => 'lost']], $this->nurse);

        $this->get(route('staff.inventory-counts.show', $draft))->assertOk()->assertDontSee('Correct all differences');
        $this->post(route('staff.inventory-counts.correct-all', $draft))->assertSessionHasErrors();
        $this->assertSame(40, $this->units('B1'));

        $this->actingAs($this->makeDoctor())->get(route('doctors.inventory-counts.show', $this->sheet))->assertOk()->assertDontSee('Correct all differences');
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('doctors.inventory-counts.correct-all'));
    }
}
