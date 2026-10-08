<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\PurchasedMedicine;
use App\Models\StockIn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * STATUS.md R3-L5: stock typed on the medicine form ("Initial / additional batch stock") and on the
 * prescription quick-add pop-up goes through the Stock-In register like any other delivery, so it has a
 * stock-in number, an activity row, and can be viewed, edited or reversed from Stock-In.
 */
class InitialStockRegisterTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function medicinePayload(array $overrides = []): array
    {
        return array_merge([
            'generic_name' => 'Paracetamol', 'brand_name' => 'Biogesic', 'category' => 'Analgesic', 'dosage' => '500mg', 'uom' => 'tablet',
            'initial_stock_quantity' => 30, 'batch_number' => 'B-100', 'manufacturing_date' => '2026-01-01',
            'expiration_date' => now()->addYear()->toDateString(), 'supplier_name' => 'Mercury Drug', 'unit_cost' => '2.50',
        ], $overrides);
    }

    public function test_stock_added_on_the_new_medicine_form_appears_in_the_stock_in_register(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('medicines.store'), $this->medicinePayload())->assertRedirect();

        $medicine = Medicine::firstOrFail();
        $stockIn = StockIn::firstOrFail();
        $line = PurchasedMedicine::where('medicine_availabilities_id', $stockIn->id)->firstOrFail();
        $batch = MedicineBatch::where('medicine_id', $medicine->id)->firstOrFail();

        $this->assertNotEmpty($stockIn->availability_no);
        $this->assertSame($medicine->id, (int) $line->medicine_id);
        $this->assertSame($batch->id, (int) $line->batch_id, 'the register line remembers the batch it created');
        $this->assertSame(30, (int) $line->quantity);
        $this->assertSame(30, (int) $batch->quantity);
        $this->assertSame('B-100', $batch->batch_number);
        $this->assertSame('Mercury Drug', $batch->supplier_name);
        $this->assertEquals(2.5, (float) $batch->unit_cost);
        $this->assertSame(30, (int) $medicine->fresh()->available_quantity);
        $this->assertNotNull(ActivityLog::where('action', 'procured_medicine')->where('subject_id', $medicine->id)->first());
    }

    public function test_that_stock_can_be_reversed_from_the_stock_in_register(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->post(route('medicines.store'), $this->medicinePayload())->assertRedirect();
        $stockIn = StockIn::firstOrFail();

        $this->deleteJson(route('stock-in.destroy', $stockIn))->assertOk();

        $this->assertSame(0, (int) MedicineBatch::firstOrFail()->quantity);
        $this->assertSame(0, (int) Medicine::firstOrFail()->fresh()->available_quantity);
    }

    public function test_additional_batch_stock_on_the_edit_form_is_a_second_register_entry(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->post(route('medicines.store'), $this->medicinePayload())->assertRedirect();
        $medicine = Medicine::firstOrFail();

        $this->put(route('medicines.update', $medicine), $this->medicinePayload([
            'initial_stock_quantity' => 12, 'batch_number' => 'B-200', 'expiration_date' => now()->addMonths(18)->toDateString(),
        ]))->assertRedirect();

        $this->assertSame(2, StockIn::count());
        $this->assertSame(42, (int) MedicineBatch::where('medicine_id', $medicine->id)->sum('quantity'));
        $this->assertNotSame(StockIn::orderBy('id')->first()->availability_no, StockIn::orderByDesc('id')->first()->availability_no);
    }

    public function test_no_register_entry_is_made_when_no_stock_is_typed(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('medicines.store'), $this->medicinePayload(['initial_stock_quantity' => 0, 'batch_number' => null, 'expiration_date' => null]))
            ->assertRedirect();

        $this->assertSame(1, Medicine::count());
        $this->assertSame(0, StockIn::count());
    }

    public function test_a_missing_expiry_date_is_explained_and_nothing_is_saved(): void
    {
        $this->actingAs($this->makeAdmin())
            ->from(route('medicines.create'))
            ->post(route('medicines.store'), $this->medicinePayload(['expiration_date' => null]))
            ->assertRedirect(route('medicines.create'));

        $this->assertSame(0, Medicine::count());
        $this->assertSame(0, StockIn::count());
        $this->assertStringContainsString('Expiration date', (string) collect(session('flash_notification', []))->pluck('message')->implode(' '));
    }

    public function test_the_prescription_quick_add_pop_up_also_uses_the_register(): void
    {
        $doctor = $this->makeDoctor();

        $this->actingAs($doctor)->postJson(route('doctors.prescription.medicine.store'), $this->medicinePayload())->assertOk();

        $this->assertSame(1, StockIn::count());
        $this->assertSame(30, (int) MedicineBatch::firstOrFail()->quantity);
        $this->assertSame(1, PurchasedMedicine::count());
    }
}
