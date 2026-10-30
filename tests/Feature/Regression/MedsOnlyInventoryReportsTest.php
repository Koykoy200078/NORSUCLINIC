<?php

namespace Tests\Feature\Regression;

use App\Livewire\StockOutTable;
use App\Models\DispenseRecord;
use App\Models\Medicine;
use App\Models\PatientType;
use App\Models\StockOutView;
use App\Models\User;
use App\Services\MedicineInventoryService;
use App\Services\MedsOnlyService;
use App\Services\Reports\YearlyInventoryReportBuilder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Phase 11 C4: a Meds Only record takes its medicine out of stock through the same ledger as every other dispense, so the
 * inventory reports need no special case - this proves it end to end with the real module: the units are "used" in the
 * yearly Medicine Inventory report (and the year still adds up, also after the record is deleted and the units come
 * back) and they are Stock Out lines whose source is "Meds Only", named after the typed person.
 *
 * Every movement is made on a fixed date (Carbon::setTestNow) so the ledger crosses the quarter boundaries.
 */
class MedsOnlyInventoryReportsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $admin;

    private User $nurse;

    private Medicine $paracetamol;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->admin = $this->makeAdmin(['first_name' => 'Ada', 'last_name' => 'Admin']);
        $this->nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse']);
        $this->paracetamol = $this->makeMedicine('Paracetamol', ['dosage' => '500mg', 'uom' => 'tablet', 'generic_name' => 'Paracetamol']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function onDate(string $when, callable $do): mixed
    {
        Carbon::setTestNow($when);

        try {
            return $do();
        } finally {
            Carbon::setTestNow();
        }
    }

    private function medsOnly(string $name, int $quantity): DispenseRecord
    {
        $this->actingAs($this->nurse);

        return app(MedsOnlyService::class)->create([
            'bill_date' => Carbon::now()->toDateString(), 'patient_name' => $name, 'patient_type_id' => PatientType::where('code', 'student')->value('id'),
            'medicines' => [['medicine_id' => $this->paracetamol->id, 'dosage' => '500mg', 'quantity' => $quantity]],
        ], $this->nurse);
    }

    /** Stock in on 2026-01-10, Meds Only 7 units on 2026-05-10 (Q2), another kind of dispense of 3 units on 2026-08-02 (Q3). */
    private function scenario(): DispenseRecord
    {
        $this->onDate('2026-01-10 08:00:00', fn () => $this->stockIn($this->paracetamol, 100, '2030-01-01', '500mg', 'P1'));
        $record = $this->onDate('2026-05-10 09:00:00', fn () => $this->medsOnly('Juan Typed', 7));
        $this->onDate('2026-08-02 09:00:00', fn () => app(MedicineInventoryService::class)->deductStockFefo($this->paracetamol->id, 3, $this->admin->id, null, 'Other dispense'));

        return $record;
    }

    private function row(string $now): array
    {
        Carbon::setTestNow($now);
        $report = app(YearlyInventoryReportBuilder::class)->build(2026, $this->admin);
        Carbon::setTestNow();

        return collect($report['rows'])->firstWhere('name', 'Paracetamol') + ['_inconsistent' => $report['inconsistent']];
    }

    public function test_the_units_of_a_meds_only_record_are_used_in_the_yearly_inventory_report_in_the_quarter_they_were_given(): void
    {
        $this->scenario();
        $row = $this->row('2027-02-15');

        $this->assertSame(10, $row['used'], '7 for the Meds Only record and 3 for the other dispense');
        $this->assertSame(0, $row['left']);
        $this->assertSame(100, $row['additional']);
        $this->assertSame(90, $row['ending']);
        $this->assertSame([100, 93, 90, 90], array_map(fn ($key) => $row['quarters'][$key]['remaining'], ['q1', 'q2', 'q3', 'q4']), 'Q2 ends 7 lower because of the Meds Only record');
        $this->assertTrue($row['consistent']);
        $this->assertSame(0, $row['_inconsistent']);
    }

    public function test_the_year_still_adds_up_after_a_meds_only_record_is_deleted_and_the_units_come_back(): void
    {
        $record = $this->scenario();
        $this->onDate('2026-09-15 10:00:00', function () use ($record) {
            $this->actingAs($this->nurse);
            app(MedsOnlyService::class)->delete($record, $this->nurse);
        });

        $row = $this->row('2027-02-15');

        $this->assertSame(10, $row['used'], 'the dispense happened; the return is not un-using it');
        $this->assertSame(7, $row['adjusted'], 'the units that came back are an adjustment, as for any return');
        $this->assertSame(97, $row['ending']);
        $this->assertSame($row['ending'], $row['left'] + $row['additional'] - $row['used'] - $row['disposed'] + $row['adjusted']);
        $this->assertSame([100, 93, 97, 97], array_map(fn ($key) => $row['quarters'][$key]['remaining'], ['q1', 'q2', 'q3', 'q4']), 'the 7 units are back on 2026-09-15, in Q3');
        $this->assertTrue($row['consistent']);
        $this->assertSame(0, $row['_inconsistent']);
        $this->assertSame(97, (int) $this->paracetamol->fresh()->available_quantity, 'the shelf agrees with the report');
    }

    public function test_a_meds_only_record_is_stock_out_with_the_source_meds_only_and_the_typed_name(): void
    {
        $record = $this->onDate('2026-05-10 09:00:00', function () {
            $this->stockIn($this->paracetamol, 50, '2030-01-01', '500mg', 'P1');

            return $this->medsOnly('Juan Typed', 7);
        });

        $line = StockOutView::where('medicine_id', $this->paracetamol->id)->firstOrFail();
        $this->assertSame('Meds Only', $line->source);
        $this->assertSame('Juan Typed', $line->patient_name);
        $this->assertSame('Nora Nurse', $line->nurse_incharged);
        $this->assertSame(7, (int) $line->quantity);

        $this->actingAs($this->nurse);
        Livewire::withoutLazyLoading()->test(StockOutTable::class)->assertSee('Paracetamol')->assertSee('Juan Typed')->assertSee('Meds Only');

        // Deleting the record puts the units back: nothing of it is left in Stock Out.
        app(MedsOnlyService::class)->delete($record, $this->nurse);
        $this->assertSame(0, (int) StockOutView::where('medicine_id', $this->paracetamol->id)->sum('quantity'));
        Livewire::withoutLazyLoading()->test(StockOutTable::class)->assertDontSee('Juan Typed');
    }
}
