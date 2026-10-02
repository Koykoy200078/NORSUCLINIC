<?php

namespace Tests\Feature\Regression;

use App\Livewire\StockOutTable;
use App\Models\DispenseRecord;
use App\Models\DocumentIssuance;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\StockOutView;
use App\Models\User;
use App\Services\MedicineInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * R3-M3 (2026-10-01 re-audit): the dispensing "Stock-out" tab listed every placeholder line of a prescription
 * that had not been dispensed yet, always showed the patient as "N/A", and showed the earliest unexpired
 * stock-in date instead of the batch that was handed out. It is now built from the stock ledger.
 */
class StockOutTabTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $doctor;

    private Patient $patient;

    private Medicine $medicine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();

        $this->doctor = $this->makeDoctor();
        $this->patient = $this->makePatient(['first_name' => 'Ana', 'last_name' => 'Reyes']);
        $this->medicine = $this->makeMedicine('Cefalexin');
        // Two batches: FEFO hands out the one that expires first, which is NOT the later one.
        $this->stockIn($this->medicine, 3, now()->addDays(20)->toDateString(), '500mg', 'SOON');
        $this->stockIn($this->medicine, 50, now()->addDays(90)->toDateString(), '500mg', 'LATER');
    }

    private function pendingPrescription(int $quantity): Prescription
    {
        $prescription = Prescription::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->doctor->id,
            'status' => Prescription::DISPENSE_STATUS_PENDING,
            'is_active' => 1,
            'consultation_date' => now()->toDateString(),
        ]);

        PrescriptionMedicine::create([
            'prescription_id' => $prescription->id,
            'medicine' => $this->medicine->id,
            'dosage' => '500mg',
            'route_of_administration' => 'oral',
            'frequency' => 1,
            'duration_value' => $quantity,
            'duration_unit' => 'day',
            'total_quantity' => $quantity,
        ]);

        // The placeholder dispense record + line a pending prescription gets (this used to show as stock out).
        $record = DispenseRecord::create([
            'history_number' => 'HIS' . generateUniqueHistoryNumber(),
            'patient_id' => $this->patient->id,
            'doctor_id' => $prescription->doctor_id,
            'model_type' => Prescription::class,
            'model_id' => $prescription->id,
            'bill_date' => now(),
        ]);
        \App\Models\DispenseRecordItem::create(['dispense_id' => $record->id, 'medicine_id' => $this->medicine->id, 'quantity' => $quantity]);

        return $prescription;
    }

    public function test_a_prescription_that_was_not_dispensed_yet_is_not_stock_out(): void
    {
        $this->pendingPrescription(7);

        $this->assertSame(0, StockOutView::count());
    }

    public function test_a_dispensed_prescription_shows_the_patient_and_the_batch_that_was_handed_out(): void
    {
        $prescription = $this->pendingPrescription(5); // 3 from SOON, 2 from LATER
        app(MedicineInventoryService::class)->dispensePrescription($prescription, $this->makeStaff('pharmacist', 'pharmacy')->id);

        $rows = StockOutView::orderBy('expiry_date')->get();

        $this->assertCount(2, $rows);
        $this->assertSame([3, 2], $rows->pluck('quantity')->map(fn ($q) => (int) $q)->all());
        $this->assertSame(['Ana Reyes', 'Ana Reyes'], $rows->pluck('patient_name')->all());
        $this->assertSame(['Prescription', 'Prescription'], $rows->pluck('source')->all());
        $this->assertSame(
            [now()->addDays(20)->toDateString(), now()->addDays(90)->toDateString()],
            $rows->pluck('expiry_date')->map(fn ($d) => $d->toDateString())->all()
        );
        $this->assertSame('500mg', $rows->first()->dosage);
    }

    public function test_a_manual_dispense_record_shows_its_patient(): void
    {
        $record = DispenseRecord::create([
            'history_number' => 'HIS' . generateUniqueHistoryNumber(),
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->doctor->id,
            'model_type' => Patient::class,
            'model_id' => $this->patient->id,
            'bill_date' => now(),
        ]);
        app(MedicineInventoryService::class)->deductStockFefo($this->medicine->id, 2, null, $record, 'Dispense record', 'dispense', '500mg');

        $row = StockOutView::firstOrFail();
        $this->assertSame('Ana Reyes', $row->patient_name);
        $this->assertSame('Dispense Record', $row->source);
        $this->assertSame(2, (int) $row->quantity);
    }

    public function test_consultation_medicines_show_patient_and_nurse_and_returns_net_out(): void
    {
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $this->actingAs($this->doctor)->post(route('doctors.document-issuances.store'), [
            'document_type' => 'consultation_form',
            'user_id' => $this->patient->user_id,
            'name' => 'Ana Reyes',
            'date_of_birth' => '2003-05-01',
            'gender' => 'Female',
            'requested_at' => now()->toDateString(),
            'consult_mode' => 'physical',
            'complaints' => 'Cough',
            'nursing_incharged' => $nurse->id,
            'medicines' => ['plan' => [['medicine_id' => $this->medicine->id, 'dosage' => '500mg', 'quantity' => 4]]],
        ])->assertRedirect()->assertSessionMissing('error');

        $rows = StockOutView::orderBy('created_at')->orderBy('id')->get();
        $this->assertSame('Ana Reyes', $rows->first()->patient_name);
        $this->assertSame('Consultation', $rows->first()->source);
        $this->assertSame($nurse->full_name, $rows->first()->nurse_incharged);
        $this->assertSame(4, (int) $rows->sum('quantity'));

        // The consultation is deleted: the units go back, and the tab nets out to zero instead of
        // pretending 4 units are still out.
        $document = DocumentIssuance::firstOrFail();
        $this->delete(route('doctors.document-issuances.destroy', $document))->assertRedirect();

        $this->assertSame(0, (int) StockOutView::get()->sum('quantity'));
    }

    public function test_the_stock_out_tab_renders_the_ledger_rows(): void
    {
        $prescription = $this->pendingPrescription(2);
        app(MedicineInventoryService::class)->dispensePrescription($prescription, $this->makeStaff('pharmacist', 'pharmacy')->id);

        $this->actingAs($this->makeStaff('pharmacist', 'pharmacy'));
        Livewire::withoutLazyLoading()->test(StockOutTable::class)->assertSee('Cefalexin')->assertSee('Ana Reyes')->assertSee('Prescription');
    }
}
