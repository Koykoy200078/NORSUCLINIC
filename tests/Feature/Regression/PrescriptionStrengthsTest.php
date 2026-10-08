<?php

namespace Tests\Feature\Regression;

use App\Models\DispenseRecord;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\User;
use App\Services\MedicineInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * STATUS.md R3-L6: one medicine may be prescribed in two strengths on the same prescription (the same
 * medicine AND strength twice is still refused), the dispense history keeps both lines, and an edit
 * cannot overwrite a prescription that was dispensed a moment earlier.
 */
class PrescriptionStrengthsTest extends TestCase
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
        $this->patient = $this->makePatient();
        $this->medicine = $this->makeMedicine('Paracetamol');
    }

    private function row(string $dosage, int $quantity): array
    {
        return [
            'medicine_id' => $this->medicine->id, 'dosage' => $dosage, 'route_of_administration' => 'oral',
            'frequency' => 1, 'duration_value' => $quantity, 'duration_unit' => 'day', 'total_quantity' => $quantity,
        ];
    }

    private function payload(array $rows): array
    {
        return [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->doctor->id,
            'consultation_date' => now()->toDateString(),
            'medicines' => $rows,
        ];
    }

    private function batchQuantity(string $dosage): int
    {
        return (int) MedicineBatch::where('medicine_id', $this->medicine->id)->where('dosage', $dosage)->sum('quantity');
    }

    public function test_two_strengths_of_one_medicine_can_be_prescribed_and_dispensed(): void
    {
        $this->stockIn($this->medicine, 50, now()->addMonths(6)->toDateString(), '250mg');
        $this->stockIn($this->medicine, 50, now()->addMonths(8)->toDateString(), '500mg');

        $this->actingAs($this->doctor)
            ->post(route('doctors.prescriptions.store'), $this->payload([$this->row('250mg', 4), $this->row('500mg', 6)]))
            ->assertRedirect();

        $prescription = Prescription::firstOrFail();
        $this->assertSame(2, $prescription->getMedicine()->count());

        app(MedicineInventoryService::class)->dispensePrescription($prescription, $this->doctor->id);

        $this->assertSame(46, $this->batchQuantity('250mg'));
        $this->assertSame(44, $this->batchQuantity('500mg'));

        // the dispense history has to show BOTH lines, not just the last one
        $items = DispenseRecord::where('model_type', Prescription::class)->where('model_id', $prescription->id)
            ->firstOrFail()->dispenseItems()->get();
        $this->assertSame(10, (int) $items->sum('quantity'));
        $this->assertEqualsCanonicalizing(['250mg' => 4, '500mg' => 6], $items->pluck('quantity', 'dosage')->map(fn ($q) => (int) $q)->all());
    }

    public function test_the_same_medicine_and_strength_twice_is_still_refused(): void
    {
        $this->stockIn($this->medicine, 50, now()->addMonths(6)->toDateString(), '500mg');

        $this->actingAs($this->doctor)
            ->post(route('doctors.prescriptions.store'), $this->payload([$this->row('500mg', 4), $this->row(' 500MG ', 6)]))
            ->assertRedirect();

        $this->assertSame(0, Prescription::count());
    }

    public function test_editing_a_prescription_can_add_a_second_strength(): void
    {
        $this->stockIn($this->medicine, 50, now()->addMonths(6)->toDateString(), '250mg');
        $this->stockIn($this->medicine, 50, now()->addMonths(8)->toDateString(), '500mg');

        $this->actingAs($this->doctor)->post(route('doctors.prescriptions.store'), $this->payload([$this->row('250mg', 4)]))->assertRedirect();
        $prescription = Prescription::firstOrFail();

        $this->put(route('doctors.prescriptions.update', $prescription), $this->payload([$this->row('250mg', 4), $this->row('500mg', 6)]))
            ->assertRedirect();

        $this->assertSame(2, $prescription->fresh()->getMedicine()->count());
    }

    /** the form used to grey out a medicine already chosen in another row, which made two strengths impossible */
    public function test_the_prescription_form_lets_the_same_medicine_be_listed_in_two_strengths(): void
    {
        $this->actingAs($this->doctor)->get(route('doctors.prescriptions.create', ['patientId' => $this->patient->id]))
            ->assertOk()
            ->assertDontSee('isSelectedElsewhere', false)
            ->assertSee('strengthKey', false);
    }

    /** the "dispensed" check used to happen before the transaction without a lock, so a dispense in between was overwritten */
    public function test_an_edit_cannot_overwrite_a_prescription_dispensed_while_the_form_was_being_saved(): void
    {
        $this->stockIn($this->medicine, 50, now()->addMonths(6)->toDateString(), '500mg');

        $this->actingAs($this->doctor)->post(route('doctors.prescriptions.store'), $this->payload([$this->row('500mg', 4)]))->assertRedirect();
        $prescription = Prescription::firstOrFail();

        // the pharmacy presses "Dispense" right after the edit passed its first check (it then looks up the doctor)
        $fired = false;
        DB::listen(function ($query) use (&$fired, $prescription) {
            if (! $fired && str_starts_with($query->sql, 'select * from `doctors` where `doctors`.`id` = ? limit 1')) {
                $fired = true;
                DB::table('prescriptions')->where('id', $prescription->id)->update(['status' => Prescription::DISPENSE_STATUS_DISPENSED]);
            }
        });

        $this->put(route('doctors.prescriptions.update', $prescription), $this->payload([$this->row('500mg', 9)]))->assertRedirect();

        $this->assertTrue($fired, 'the simulated dispense ran between the check and the save');
        $this->assertSame(4, (int) PrescriptionMedicine::where('prescription_id', $prescription->id)->firstOrFail()->total_quantity);
        $this->assertSame(Prescription::DISPENSE_STATUS_DISPENSED, $prescription->fresh()->status);
    }
}
