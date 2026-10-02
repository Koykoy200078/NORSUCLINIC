<?php

namespace Tests\Feature\Regression;

use App\Livewire\PrescriptionVerificationTable;
use App\Models\ActivityLog;
use App\Models\DispenseRecord;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\User;
use App\Services\MedicineInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Regression tests for prescription control (2026-10-01 re-audit R3-H3, R3-H4, part of R3-M5):
 * a deactivated prescription cannot be dispensed, a prescription belongs to the doctor who wrote it,
 * staff may only write one on a doctor's recorded verbal order, and every change leaves an audit row.
 */
class PrescriptionControlTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $doctor;

    private User $otherDoctor;

    private User $pharmacist;

    private Patient $patient;

    private Medicine $medicine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();

        $this->doctor = $this->makeDoctor();
        $this->otherDoctor = $this->makeDoctor();
        $this->pharmacist = $this->makeStaff('pharmacist', 'pharmacy');
        $this->patient = $this->makePatient();
        $this->medicine = $this->makeMedicine('Amoxicillin');
        $this->stockIn($this->medicine, 100, now()->addMonths(6)->toDateString());
    }

    private function pendingPrescription(?User $doctor = null, int $quantity = 5): Prescription
    {
        $prescription = Prescription::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => ($doctor ?? $this->doctor)->doctor->id,
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

        DispenseRecord::create([
            'history_number' => 'HIS' . generateUniqueHistoryNumber(),
            'patient_id' => $this->patient->id,
            'doctor_id' => $prescription->doctor_id,
            'model_type' => Prescription::class,
            'model_id' => $prescription->id,
            'bill_date' => now(),
        ]);

        return $prescription;
    }

    private function stock(): int
    {
        return (int) MedicineBatch::where('medicine_id', $this->medicine->id)->sum('quantity');
    }

    /** @return array<string, mixed> */
    private function formPayload(?int $doctorId = null, array $overrides = []): array
    {
        return array_merge([
            'patient_id' => $this->patient->id,
            'doctor_id' => $doctorId ?? $this->doctor->doctor->id,
            'consultation_date' => now()->toDateString(),
            'medicines' => [[
                'medicine_id' => $this->medicine->id,
                'dosage' => '500mg',
                'route_of_administration' => 'oral',
                'frequency' => 2,
                'duration_value' => 3,
                'duration_unit' => 'day',
                'total_quantity' => 6,
                'instructions' => 'after meals',
            ]],
        ], $overrides);
    }

    /** R3-H3 */
    public function test_deactivating_a_pending_prescription_cancels_it_and_the_pharmacy_cannot_dispense_it(): void
    {
        $prescription = $this->pendingPrescription();

        $this->actingAs($this->doctor)
            ->postJson(route('doctors.prescription.status', $prescription->id))
            ->assertOk();

        $prescription->refresh();
        $this->assertSame(Prescription::DISPENSE_STATUS_CANCELLED, $prescription->status);
        $this->assertFalse($prescription->is_active);

        // It left the pharmacy queue ...
        $this->actingAs($this->pharmacist);
        $queue = (new PrescriptionVerificationTable())->builder()->pluck('prescriptions.id')->all();
        $this->assertNotContains($prescription->id, $queue);

        // ... and a direct dispense request is refused without touching stock.
        $this->postJson(route('staff.prescriptions.dispense', $prescription->id))->assertStatus(422);
        $this->assertSame(100, $this->stock());
        $this->assertSame(Prescription::DISPENSE_STATUS_CANCELLED, $prescription->fresh()->status);
    }

    /** R3-H3: rows deactivated before the fix kept status "pending" */
    public function test_an_old_inactive_but_pending_prescription_is_not_dispensable_either(): void
    {
        $prescription = $this->pendingPrescription();
        $prescription->update(['is_active' => 0]);

        $this->actingAs($this->pharmacist);
        $queue = (new PrescriptionVerificationTable())->builder()->pluck('prescriptions.id')->all();
        $this->assertNotContains($prescription->id, $queue);

        $this->postJson(route('staff.prescriptions.dispense', $prescription->id))->assertStatus(422);
        $this->assertSame(100, $this->stock());

        $this->expectException(\RuntimeException::class);
        app(MedicineInventoryService::class)->dispensePrescription($prescription->fresh(), $this->pharmacist->id);
    }

    /** R3-H3 */
    public function test_reactivating_a_cancelled_prescription_puts_it_back_in_the_queue(): void
    {
        $prescription = $this->pendingPrescription();
        $this->actingAs($this->doctor);
        $this->postJson(route('doctors.prescription.status', $prescription->id))->assertOk(); // cancel
        $this->postJson(route('doctors.prescription.status', $prescription->id))->assertOk(); // reactivate

        $prescription->refresh();
        $this->assertSame(Prescription::DISPENSE_STATUS_PENDING, $prescription->status);
        $this->assertTrue($prescription->is_active);

        $this->actingAs($this->pharmacist)->postJson(route('staff.prescriptions.dispense', $prescription->id))->assertOk();
        $this->assertSame(Prescription::DISPENSE_STATUS_DISPENSED, $prescription->fresh()->status);
        $this->assertSame(95, $this->stock());
    }

    /** R3-H3: the list had no way to switch a prescription off, so the doctor's only way to stop it was deleting it */
    public function test_the_prescription_list_offers_cancel_and_reactivate_buttons_that_work_without_javascript(): void
    {
        $prescription = $this->pendingPrescription();
        $this->actingAs($this->doctor);

        $row = view('prescriptions.action', ['row' => $prescription])->render();
        $this->assertStringContainsString('Cancel prescription', $row);
        $this->assertStringNotContainsString('Reactivate prescription', $row);

        // A normal form post (no JSON) is answered with a redirect back to the list, not raw JSON.
        $this->post(route('doctors.prescription.status', $prescription->id))->assertRedirect()->assertSessionHas('flash_notification');
        $this->assertSame(Prescription::DISPENSE_STATUS_CANCELLED, $prescription->fresh()->status);

        $row = view('prescriptions.action', ['row' => $prescription->fresh()])->render();
        $this->assertStringContainsString('Reactivate prescription', $row);
        $this->assertStringNotContainsString('Cancel prescription', $row);

        // Staff see the same buttons; a dispensed prescription shows neither.
        $this->post(route('doctors.prescription.status', $prescription->id))->assertRedirect();
        $this->actingAs($this->pharmacist)->post(route('staff.prescriptions.dispense', $prescription->id))->assertRedirect();
        $row = view('prescriptions.action', ['row' => $prescription->fresh()])->render();
        $this->assertStringNotContainsString('Cancel prescription', $row);
        $this->assertStringNotContainsString('Reactivate prescription', $row);
    }

    /** R3-H3 */
    public function test_a_dispensed_prescription_cannot_be_deactivated(): void
    {
        $prescription = $this->pendingPrescription();
        $this->actingAs($this->pharmacist)->postJson(route('staff.prescriptions.dispense', $prescription->id))->assertOk();

        $this->actingAs($this->doctor)->postJson(route('doctors.prescription.status', $prescription->id))->assertStatus(422);

        $prescription->refresh();
        $this->assertSame(Prescription::DISPENSE_STATUS_DISPENSED, $prescription->status);
        $this->assertTrue($prescription->is_active);
    }

    /** R3-H4 */
    public function test_a_doctor_always_writes_prescriptions_in_their_own_name(): void
    {
        $this->actingAs($this->doctor)
            ->post(route('doctors.prescriptions.store'), $this->formPayload($this->otherDoctor->doctor->id))
            ->assertRedirect();

        $this->assertSame($this->doctor->doctor->id, (int) Prescription::firstOrFail()->doctor_id);
    }

    /** R3-H4 */
    public function test_a_doctor_cannot_rewrite_deactivate_dispense_or_delete_another_doctors_prescription(): void
    {
        $prescription = $this->pendingPrescription($this->doctor);
        $this->actingAs($this->otherDoctor);

        $this->put(route('doctors.prescriptions.update', $prescription), $this->formPayload($this->otherDoctor->doctor->id, [
            'medicines' => [[
                'medicine_id' => $this->medicine->id, 'dosage' => '500mg', 'route_of_administration' => 'oral',
                'frequency' => 1, 'duration_value' => 50, 'duration_unit' => 'day', 'total_quantity' => 50,
            ]],
        ]))->assertForbidden();
        $this->postJson(route('doctors.prescription.status', $prescription->id))->assertForbidden();
        $this->postJson(route('doctors.prescriptions.dispense', $prescription->id))->assertForbidden();
        $this->deleteJson(route('doctors.prescriptions.destroy', $prescription->id))->assertStatus(422);

        $prescription->refresh();
        $this->assertSame($this->doctor->doctor->id, (int) $prescription->doctor_id);
        $this->assertSame(5, (int) $prescription->getMedicine()->first()->total_quantity);
        $this->assertTrue($prescription->is_active);
        $this->assertSame(100, $this->stock());
    }

    /** R3-H4 */
    public function test_the_owning_doctor_can_edit_but_cannot_hand_the_prescription_to_another_doctor(): void
    {
        $prescription = $this->pendingPrescription($this->doctor);

        $this->actingAs($this->doctor)
            ->put(route('doctors.prescriptions.update', $prescription), $this->formPayload($this->otherDoctor->doctor->id))
            ->assertRedirect();

        $prescription->refresh();
        $this->assertSame($this->doctor->doctor->id, (int) $prescription->doctor_id);
        $this->assertSame(6, (int) $prescription->getMedicine()->firstOrFail()->total_quantity);
    }

    /** decision: staff may write a prescription only on a recorded verbal / phone order */
    public function test_staff_need_to_record_a_verbal_order_to_write_a_prescription(): void
    {
        $this->actingAs($this->pharmacist);

        $this->post(route('staff.prescriptions.store'), $this->formPayload())->assertSessionHasErrors();
        $this->assertSame(0, Prescription::count());

        $this->post(route('staff.prescriptions.store'), $this->formPayload(null, ['verbal_order' => 1]))->assertRedirect();

        $prescription = Prescription::firstOrFail();
        $this->assertSame($this->doctor->doctor->id, (int) $prescription->doctor_id);

        $log = ActivityLog::where('action', 'prescription_record')->where('subject_id', $prescription->id)->firstOrFail();
        $this->assertSame($this->pharmacist->id, (int) $log->user_id);
        $this->assertTrue($log->properties['verbal_order']);
        $this->assertSame($this->doctor->doctor->id, $log->properties['doctor_id']);
        $this->assertStringContainsString('verbal', $log->description);
    }

    public function test_staff_need_the_verbal_order_to_change_a_prescription_and_cannot_delete_one(): void
    {
        $prescription = $this->pendingPrescription();
        $this->actingAs($this->pharmacist);

        $this->put(route('staff.prescriptions.update', $prescription), $this->formPayload())->assertSessionHasErrors();
        $this->assertSame(5, (int) $prescription->getMedicine()->first()->total_quantity);

        $this->put(route('staff.prescriptions.update', $prescription), $this->formPayload(null, ['verbal_order' => 1]))->assertRedirect();
        $this->assertSame(6, (int) $prescription->getMedicine()->first()->total_quantity);

        // Staff cancel with the status switch; deleting a doctor's prescription is for the doctor / admin.
        $this->deleteJson(route('staff.prescriptions.destroy', $prescription->id))->assertForbidden();
        $this->assertNotNull(Prescription::find($prescription->id));
    }

    public function test_the_clinic_admin_can_write_for_any_doctor_and_it_is_logged(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('prescriptions.store'), $this->formPayload($this->otherDoctor->doctor->id))->assertRedirect();

        $prescription = Prescription::firstOrFail();
        $this->assertSame($this->otherDoctor->doctor->id, (int) $prescription->doctor_id);
        $log = ActivityLog::where('action', 'prescription_record')->where('subject_id', $prescription->id)->firstOrFail();
        $this->assertSame($admin->id, (int) $log->user_id);
    }

    /** R3-M5 */
    public function test_cancelling_dispensing_and_deleting_a_prescription_are_audited(): void
    {
        $kept = $this->pendingPrescription();
        $deleted = $this->pendingPrescription();

        $this->actingAs($this->doctor)->postJson(route('doctors.prescription.status', $kept->id))->assertOk(); // cancel
        $this->postJson(route('doctors.prescription.status', $kept->id))->assertOk(); // reactivate
        $this->actingAs($this->pharmacist)->postJson(route('staff.prescriptions.dispense', $kept->id))->assertOk();
        $this->actingAs($this->doctor)->deleteJson(route('doctors.prescriptions.destroy', $deleted->id))->assertOk();

        $actions = ActivityLog::where('subject_type', 'Prescription')->where('subject_id', $kept->id)->orderBy('id')->pluck('action')->all();
        $this->assertSame(['prescription_cancelled', 'prescription_reactivated', 'prescription_dispensed'], $actions);

        $deleteLog = ActivityLog::where('action', 'prescription_deleted')->where('subject_id', $deleted->id)->firstOrFail();
        $this->assertSame($this->doctor->id, (int) $deleteLog->user_id);
        $this->assertSame('Amoxicillin', $deleteLog->properties['medicines'][0]['medicine']);
        $this->assertNull(Prescription::find($deleted->id));
    }
}
