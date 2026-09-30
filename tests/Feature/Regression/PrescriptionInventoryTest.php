<?php

namespace Tests\Feature\Regression;

use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineTransaction;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\StockIn;
use App\Models\User;
use App\Repositories\MedicineAvailabilityRepository;
use App\Services\MedicineInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Regression tests for the prescription / dispensing / inventory fixes of the 2026-09 re-audit
 * (H-07, M-01, M-02, C-04, H-04, H-05, M-03, H-12, P2-M5).
 */
class PrescriptionInventoryTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function pendingPrescription(User $doctorUser, Medicine $medicine, int $quantity = 5, string $dosage = '500mg'): Prescription
    {
        $patient = $this->makePatient();

        $prescription = Prescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctorUser->doctor->id,
            'status' => Prescription::DISPENSE_STATUS_PENDING,
            'is_active' => 1,
            'consultation_date' => now()->toDateString(),
        ]);

        PrescriptionMedicine::create([
            'prescription_id' => $prescription->id,
            'medicine' => $medicine->id,
            'dosage' => $dosage,
            'route_of_administration' => 'oral',
            'frequency' => 1,
            'duration_value' => $quantity,
            'duration_unit' => 'day',
            'total_quantity' => $quantity,
        ]);

        $record = DispenseRecord::create([
            'history_number' => 'HIS' . generateUniqueHistoryNumber(),
            'patient_id' => $patient->id,
            'doctor_id' => $prescription->doctor_id,
            'model_type' => Prescription::class,
            'model_id' => $prescription->id,
            'bill_date' => now(),
        ]);
        DispenseRecordItem::create(['dispense_id' => $record->id, 'medicine_id' => $medicine->id, 'quantity' => $quantity]);

        return $prescription;
    }

    /** M-02 + M-01 */
    public function test_dispensing_uses_the_prescribed_strength_and_records_the_real_batch(): void
    {
        $doctor = $this->makeDoctor();
        $medicine = $this->makeMedicine('Biogesic');
        $earlier250 = $this->stockIn($medicine, 40, now()->addMonths(3)->toDateString(), '250mg');
        $later500 = $this->stockIn($medicine, 100, now()->addYear()->toDateString(), '500mg');

        $prescription = $this->pendingPrescription($doctor, $medicine, 5, '500mg');

        app(MedicineInventoryService::class)->dispensePrescription($prescription, $doctor->id);

        $this->assertSame(40, (int) $earlier250->fresh()->quantity, 'the earlier-expiring 250 mg batch must be untouched');
        $this->assertSame(95, (int) $later500->fresh()->quantity);
        $this->assertSame(Prescription::DISPENSE_STATUS_DISPENSED, $prescription->fresh()->status);

        $item = DispenseRecord::where('model_id', $prescription->id)->firstOrFail()->dispenseItems()->get();
        $this->assertCount(1, $item);
        $this->assertSame('500mg', $item[0]->dosage);
        $this->assertSame(5, (int) $item[0]->quantity);
    }

    /** M-02 */
    public function test_nothing_is_dispensed_when_the_prescribed_strength_has_no_stock(): void
    {
        $doctor = $this->makeDoctor();
        $medicine = $this->makeMedicine('Biogesic');
        $only250 = $this->stockIn($medicine, 40, now()->addYear()->toDateString(), '250mg');
        $prescription = $this->pendingPrescription($doctor, $medicine, 5, '500mg');

        try {
            app(MedicineInventoryService::class)->dispensePrescription($prescription, $doctor->id);
            $this->fail('Dispensing should have been refused.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Insufficient stock', $e->getMessage());
        }

        $this->assertSame(40, (int) $only250->fresh()->quantity);
        $this->assertSame(Prescription::DISPENSE_STATUS_PENDING, $prescription->fresh()->status);
    }

    /** H-07 */
    public function test_a_dispensed_prescription_cannot_be_edited_or_deleted(): void
    {
        $doctor = $this->makeDoctor();
        $medicine = $this->makeMedicine('Biogesic');
        $this->stockIn($medicine, 100, now()->addYear()->toDateString());
        $prescription = $this->pendingPrescription($doctor, $medicine, 5);
        app(MedicineInventoryService::class)->dispensePrescription($prescription, $doctor->id);

        $this->actingAs($doctor);

        $this->get(route('doctors.prescriptions.edit', $prescription))->assertRedirect();

        $this->putJson(route('doctors.prescriptions.update', $prescription), [
            'patient_id' => $prescription->patient_id,
            'doctor_id' => $prescription->doctor_id,
            'consultation_date' => now()->toDateString(),
            'medicines' => [[
                'medicine_id' => $medicine->id, 'dosage' => '500mg', 'route_of_administration' => 'oral',
                'frequency' => 1, 'duration_value' => 99, 'duration_unit' => 'day', 'total_quantity' => 99,
            ]],
        ]);
        $this->assertSame(5, (int) $prescription->getMedicine()->first()->total_quantity);

        $this->deleteJson(route('doctors.prescriptions.destroy', $prescription))->assertStatus(422);
        $this->assertNotNull(Prescription::find($prescription->id));
    }

    /** M-01 */
    public function test_a_prescription_dispense_record_cannot_be_deleted_from_the_dispense_screens(): void
    {
        $doctor = $this->makeDoctor();
        $staff = $this->makeStaff('clinic_head', 'pharmacy');
        $medicine = $this->makeMedicine('Biogesic');
        $this->stockIn($medicine, 100, now()->addYear()->toDateString());
        $prescription = $this->pendingPrescription($doctor, $medicine, 5);
        $record = DispenseRecord::where('model_id', $prescription->id)->firstOrFail();
        $stockBefore = (int) $medicine->fresh()->available_quantity;

        $this->actingAs($staff)
            ->deleteJson(route('staff.dispense-records.destroy', $record->id))
            ->assertStatus(422);

        $this->assertNotNull(DispenseRecord::find($record->id));
        $this->assertSame($stockBefore, (int) $medicine->fresh()->available_quantity, 'no phantom stock');
    }

    /** C-04 */
    public function test_a_medicine_with_history_cannot_be_deleted_but_an_unused_one_can(): void
    {
        $admin = $this->makeAdmin();
        $used = $this->makeMedicine('Used Med');
        $this->stockIn($used, 20, now()->addYear()->toDateString());
        $unused = $this->makeMedicine('Unused Med');

        $this->actingAs($admin);

        $this->deleteJson(route('medicines.destroy', $used))->assertStatus(422);
        $this->assertSame(1, MedicineBatch::where('medicine_id', $used->id)->count());
        $this->assertGreaterThan(0, MedicineTransaction::count());

        $this->deleteJson(route('medicines.destroy', $unused))->assertOk();
        $this->assertNull(Medicine::find($unused->id));
    }

    /** C-04: the database itself refuses as well */
    public function test_the_database_restricts_deleting_a_medicine_that_has_batches(): void
    {
        $medicine = $this->makeMedicine('Protected');
        $this->stockIn($medicine, 5, now()->addYear()->toDateString());

        $this->expectException(\Illuminate\Database\QueryException::class);
        \DB::table('medicines')->where('id', $medicine->id)->delete();
    }

    /** H-05 */
    public function test_deleting_a_stock_in_removes_units_from_the_batch_it_created(): void
    {
        $admin = $this->makeAdmin();
        $medicine = $this->makeMedicine('Ledger Med');
        $repository = app(MedicineAvailabilityRepository::class);

        $repository->store($this->stockInInput($medicine, 5, '2026-12-01'));
        $repository->store($this->stockInInput($medicine, 10, '2028-12-01'));

        $late = StockIn::orderByDesc('id')->firstOrFail();

        $this->actingAs($admin)->deleteJson(route('stock-in.destroy', $late))->assertOk();

        $this->assertSame(
            ['2026-12-01' => 5, '2028-12-01' => 0],
            MedicineBatch::where('medicine_id', $medicine->id)->orderBy('expiration_date')->get()
                ->mapWithKeys(fn ($b) => [$b->expiration_date->toDateString() => (int) $b->quantity])->all()
        );
    }

    /** H-05 */
    public function test_a_stock_in_whose_units_were_dispensed_cannot_be_deleted(): void
    {
        $admin = $this->makeAdmin();
        $medicine = $this->makeMedicine('Ledger Med');
        app(MedicineAvailabilityRepository::class)->store($this->stockInInput($medicine, 5, '2028-12-01'));
        $stockIn = StockIn::firstOrFail();

        app(MedicineInventoryService::class)->deductStockFefo($medicine->id, 3, $admin->id, null, 'test', MedicineTransaction::TYPE_DISPENSE, '10mg');

        $this->actingAs($admin)->deleteJson(route('stock-in.destroy', $stockIn))->assertStatus(422);

        $this->assertNotNull(StockIn::find($stockIn->id));
        $this->assertSame(2, (int) MedicineBatch::where('medicine_id', $medicine->id)->value('quantity'));
    }

    /** H-04 */
    public function test_editing_a_stock_in_line_moves_stock_to_the_new_medicine_and_expiry(): void
    {
        $admin = $this->makeAdmin();
        $medA = $this->makeMedicine('Med A');
        $medB = $this->makeMedicine('Med B');
        $repository = app(MedicineAvailabilityRepository::class);

        $repository->store($this->stockInInput($medA, 10, '2027-01-01'));
        $stockIn = StockIn::firstOrFail();
        $line = $stockIn->purchasedMedcines()->firstOrFail();
        $this->assertNotNull($line->batch_id, 'the line remembers its batch');

        $repository->updatePurchaseMedicine([
            'availability_no' => $stockIn->availability_no,
            'purchased_medicine_id' => [$line->id],
            'medicine' => [$medB->id],
            'dosage' => ['10mg'],
            'manufacturing_date' => ['2026-01-01'],
            'expiry_date' => ['2029-06-30'],
            'quantity' => [10],
        ], $stockIn->id);

        $this->assertSame(0, (int) $medA->fresh()->quantity);
        $this->assertSame(10, (int) $medB->fresh()->quantity);
        $this->assertSame(
            '2029-06-30',
            MedicineBatch::where('medicine_id', $medB->id)->firstOrFail()->expiration_date->toDateString()
        );

        // Lowering / raising the quantity of an unchanged line adjusts the same batch.
        foreach ([4 => 4, 9 => 9] as $newQuantity => $expected) {
            $repository->updatePurchaseMedicine([
                'availability_no' => $stockIn->availability_no,
                'purchased_medicine_id' => [$line->id],
                'medicine' => [$medB->id],
                'dosage' => ['10mg'],
                'manufacturing_date' => ['2026-01-01'],
                'expiry_date' => ['2029-06-30'],
                'quantity' => [$newQuantity],
            ], $stockIn->id);

            $this->assertSame($expected, (int) MedicineBatch::where('medicine_id', $medB->id)->value('quantity'));
        }
    }

    /** M-03 */
    public function test_an_existing_batch_number_cannot_be_given_a_different_expiry(): void
    {
        $medicine = $this->makeMedicine('Lot Med');
        $service = app(MedicineInventoryService::class);

        $service->recordStockIn(['medicine_id' => $medicine->id, 'quantity' => 6, 'dosage' => '10mg', 'batch_number' => 'LOT-X', 'expiration_date' => '2030-01-01']);
        $service->recordStockIn(['medicine_id' => $medicine->id, 'quantity' => 4, 'dosage' => '10mg', 'batch_number' => 'LOT-X', 'expiration_date' => '2030-01-01']);

        $this->assertSame(10, (int) MedicineBatch::where('batch_number', 'LOT-X')->value('quantity'));

        $this->expectException(\RuntimeException::class);
        $service->recordStockIn(['medicine_id' => $medicine->id, 'quantity' => 6, 'dosage' => '10mg', 'batch_number' => 'LOT-X', 'expiration_date' => '2031-01-01']);
    }

    /** H-12 */
    public function test_dispense_history_numbers_are_unique_and_enforced_by_the_database(): void
    {
        $patient = $this->makePatient();
        $numbers = collect(range(1, 30))->map(fn () => 'HIS' . generateUniqueHistoryNumber());
        $this->assertSame(30, $numbers->unique()->count());

        DispenseRecord::create(['history_number' => 'HIS123456', 'patient_id' => $patient->id, 'model_type' => DispenseRecord::class, 'bill_date' => now()]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DispenseRecord::create(['history_number' => 'HIS123456', 'patient_id' => $patient->id, 'model_type' => DispenseRecord::class, 'bill_date' => now()]);
    }

    /** P2-M5 */
    public function test_a_generic_used_by_medicines_cannot_be_deleted(): void
    {
        $admin = $this->makeAdmin();
        $generic = \App\Models\Generic::create(['name' => 'Paracetamol']);
        $medicine = $this->makeMedicine('Biogesic', ['generic_id' => $generic->id]);

        $this->actingAs($admin)->deleteJson(route('generics.destroy', $generic))->assertStatus(422);

        $this->assertSame($generic->id, (int) $medicine->fresh()->generic_id);
    }

    /**
     * @return array<string, mixed>
     */
    private function stockInInput(Medicine $medicine, int $quantity, string $expiry): array
    {
        return [
            'availability_no' => random_int(700000, 799999),
            'medicine' => [$medicine->id],
            'dosage' => ['10mg'],
            'manufacturing_date' => ['2026-01-01'],
            'expiry_date' => [$expiry],
            'quantity' => [$quantity],
        ];
    }
}
