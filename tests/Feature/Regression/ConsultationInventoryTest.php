<?php

namespace Tests\Feature\Regression;

use App\Models\ConsultationMedicine;
use App\Models\DocumentIssuance;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineTransaction;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Regression tests for the medicines a doctor / nurse records inside a consultation
 * (2026-10-01 re-audit R3-H1, R3-H2): stock must go back to the exact batches it came from, and a
 * duplicated medicine row must not corrupt inventory.
 */
class ConsultationInventoryTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $doctor;

    private User $nurse;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();

        $this->doctor = $this->makeDoctor();
        $this->nurse = $this->makeStaff('nurse', 'triage_area');
        $this->patient = $this->makePatient();
    }

    /** @param array<int, array<string, mixed>> $plan  @param array<int, array<string, mixed>> $nursing */
    private function consultationPayload(array $plan = [], array $nursing = [], array $extra = []): array
    {
        $medicines = array_filter(['plan' => $plan, 'nursing' => $nursing]);

        return array_merge([
            'document_type' => 'consultation_form',
            'user_id' => $this->patient->user_id,
            'name' => 'Juan Dela Cruz',
            'date_of_birth' => '2003-05-01',
            'gender' => 'Male',
            'requested_at' => now()->toDateString(),
            'consult_mode' => 'physical',
            'complaints' => 'Headache',
            'nursing_incharged' => $this->nurse->id,
        ], $medicines ? ['medicines' => $medicines] : [], $extra);
    }

    private function row(Medicine $medicine, int $quantity, string $dosage = '500mg', ?int $id = null): array
    {
        return array_filter([
            'id' => $id,
            'medicine_id' => $medicine->id,
            'dosage' => $dosage,
            'quantity' => $quantity,
            'dosage_instructions' => '1 tablet',
        ], fn ($value) => $value !== null);
    }

    private function createConsultation(array $plan = [], array $nursing = []): DocumentIssuance
    {
        $this->actingAs($this->doctor)
            ->post(route('doctors.document-issuances.store'), $this->consultationPayload($plan, $nursing))
            ->assertRedirect()
            ->assertSessionMissing('error');

        return DocumentIssuance::where('document_type', 'consultation_form')->latest('id')->firstOrFail();
    }

    private function editConsultation(DocumentIssuance $document, array $plan = [], array $nursing = []): \Illuminate\Testing\TestResponse
    {
        $payload = $this->consultationPayload($plan, $nursing);
        unset($payload['document_type'], $payload['user_id']);

        return $this->actingAs($this->doctor)->put(route('doctors.document-issuances.update', $document), $payload);
    }

    private function batchQuantities(Medicine $medicine): array
    {
        return MedicineBatch::where('medicine_id', $medicine->id)->orderBy('id')
            ->pluck('quantity', 'batch_number')->map(fn ($quantity) => (int) $quantity)->all();
    }

    /** R3-H1 */
    public function test_deleting_a_consultation_after_its_batch_expired_does_not_create_never_expiring_stock(): void
    {
        $medicine = $this->makeMedicine('Ibuprofen');
        $batch = $this->stockIn($medicine, 5, now()->addDay()->toDateString(), '500mg', 'EXP-SOON');

        $document = $this->createConsultation([$this->row($medicine, 5)]);
        $this->assertSame(0, (int) $batch->fresh()->quantity);

        $this->travelTo(now()->addDays(3)); // the batch is now expired

        $this->delete(route('doctors.document-issuances.destroy', $document))->assertRedirect();

        // The units go back to the batch they came from (still expired), not to a "2099" batch.
        $this->assertSame(['EXP-SOON' => 5], $this->batchQuantities($medicine));
        $this->assertSame(0, MedicineBatch::whereDate('expiration_date', '2099-12-31')->where('medicine_id', $medicine->id)->count());
        $this->assertSame(0, (int) $medicine->fresh()->available_quantity, 'expired units must not count as available');
    }

    /** R3-H1 */
    public function test_lowering_or_removing_a_medicine_returns_units_to_the_batches_they_came_from(): void
    {
        $medicine = $this->makeMedicine('Paracetamol');
        $this->stockIn($medicine, 3, now()->addDays(10)->toDateString(), '500mg', 'A-EARLY');
        $this->stockIn($medicine, 10, now()->addDays(20)->toDateString(), '500mg', 'B-LATE');

        $document = $this->createConsultation([$this->row($medicine, 5)]); // 3 from A, 2 from B
        $this->assertSame(['A-EARLY' => 0, 'B-LATE' => 8], $this->batchQuantities($medicine));
        $rowId = $document->consultationMedicines()->firstOrFail()->id;

        // 5 -> 4: the one unit taken back was the last one handed out, i.e. from batch B.
        $this->editConsultation($document, [$this->row($medicine, 4, '500mg', $rowId)])->assertRedirect()->assertSessionMissing('error');
        $this->assertSame(['A-EARLY' => 0, 'B-LATE' => 9], $this->batchQuantities($medicine));

        // Removing the medicine returns everything: 1 to B (net 1 outstanding), 3 to A.
        $this->editConsultation($document)->assertRedirect()->assertSessionMissing('error');
        $this->assertSame(['A-EARLY' => 3, 'B-LATE' => 10], $this->batchQuantities($medicine));
        $this->assertSame(0, $document->consultationMedicines()->count());
        $this->assertSame(13, (int) $medicine->fresh()->available_quantity);
    }

    /** R3-H1: consultations recorded before stock was tracked per batch have no ledger trail */
    public function test_a_legacy_consultation_medicine_without_a_ledger_trail_never_returns_as_never_expiring_stock(): void
    {
        $medicine = $this->makeMedicine('Cetirizine');
        $expired = $this->stockIn($medicine, 4, now()->addDay()->toDateString(), '500mg', 'OLD');
        $expired->update(['quantity' => 0]);
        $this->travelTo(now()->addDays(5));

        $document = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $this->doctor->id,
            'user_id' => $this->patient->user_id, 'name' => 'Juan Dela Cruz', 'age' => 23, 'gender' => 'Male',
            'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
        ]);
        ConsultationMedicine::create([
            'request_document_id' => $document->id, 'medicine_id' => $medicine->id,
            'dosage' => '500mg', 'quantity' => 2, 'used_for' => 'plan',
        ]);

        $this->actingAs($this->doctor)->delete(route('doctors.document-issuances.destroy', $document))->assertRedirect();

        $this->assertSame(0, MedicineBatch::where('medicine_id', $medicine->id)->whereDate('expiration_date', '>=', now()->toDateString())->count(),
            'the units must not be turned into stock that is dispensable today');
        $this->assertSame(2, (int) MedicineBatch::where('medicine_id', $medicine->id)->sum('quantity'));
        $this->assertSame(0, (int) $medicine->fresh()->available_quantity);
    }

    /** R3-H1: a legacy medicine row still returns to an unexpired batch when one exists */
    public function test_a_legacy_consultation_medicine_returns_to_an_unexpired_batch_when_there_is_one(): void
    {
        $medicine = $this->makeMedicine('Loratadine');
        $this->stockIn($medicine, 6, now()->addDays(30)->toDateString(), '500mg', 'GOOD');
        $document = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $this->doctor->id,
            'user_id' => $this->patient->user_id, 'name' => 'Juan Dela Cruz', 'age' => 23, 'gender' => 'Male',
            'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
        ]);
        ConsultationMedicine::create([
            'request_document_id' => $document->id, 'medicine_id' => $medicine->id,
            'dosage' => '500mg', 'quantity' => 2, 'used_for' => 'plan',
        ]);

        $this->actingAs($this->doctor)->delete(route('doctors.document-issuances.destroy', $document))->assertRedirect();

        $this->assertSame(['GOOD' => 8], $this->batchQuantities($medicine));
    }

    /** R3-H2 */
    public function test_the_same_medicine_cannot_be_listed_twice_in_one_consultation(): void
    {
        $medicine = $this->makeMedicine('Ibuprofen');
        $this->stockIn($medicine, 100, now()->addDays(60)->toDateString());

        $this->actingAs($this->doctor)
            ->post(route('doctors.document-issuances.store'), $this->consultationPayload([
                $this->row($medicine, 3), $this->row($medicine, 2),
            ]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertStringContainsString('more than once', session('error'));
        $this->assertSame(0, DocumentIssuance::count(), 'nothing is saved when the medicine list is invalid');
        $this->assertSame(100, (int) $medicine->fresh()->available_quantity);
    }

    /** R3-H2 */
    public function test_editing_with_a_duplicated_medicine_row_changes_nothing(): void
    {
        $medicine = $this->makeMedicine('Ibuprofen');
        $this->stockIn($medicine, 100, now()->addDays(60)->toDateString());
        $document = $this->createConsultation([$this->row($medicine, 3)]);
        $this->assertSame(97, (int) $medicine->fresh()->available_quantity);

        $this->editConsultation($document, [$this->row($medicine, 3), $this->row($medicine, 2)])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(97, (int) $medicine->fresh()->available_quantity);
        $this->assertSame([3], $document->consultationMedicines()->pluck('quantity')->map(fn ($q) => (int) $q)->all());
    }

    /** R3-H2: the evidence scenario, with the same medicine as a plan row and a nursing row */
    public function test_removing_one_of_two_rows_for_the_same_medicine_restores_exactly_that_row(): void
    {
        $medicine = $this->makeMedicine('Ibuprofen');
        $this->stockIn($medicine, 100, now()->addDays(60)->toDateString());
        $document = $this->createConsultation([$this->row($medicine, 3)], [$this->row($medicine, 2)]);
        $this->assertSame(95, (int) $medicine->fresh()->available_quantity);

        $planRow = $document->consultationMedicines()->where('used_for', 'plan')->firstOrFail();

        $this->editConsultation($document, [$this->row($medicine, 3, '500mg', $planRow->id)])
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->assertSame(97, (int) $medicine->fresh()->available_quantity);
        $this->assertSame(['plan' => 3], $document->consultationMedicines()->pluck('quantity', 'used_for')->map(fn ($q) => (int) $q)->all());
    }

    /** R3-H2: forms opened before the update do not send row ids; rows are then matched by medicine + strength */
    public function test_rows_submitted_without_ids_are_matched_by_medicine_and_strength(): void
    {
        $medicine = $this->makeMedicine('Ibuprofen');
        $this->stockIn($medicine, 100, now()->addDays(60)->toDateString());
        $document = $this->createConsultation([$this->row($medicine, 3)]);

        $this->editConsultation($document, [$this->row($medicine, 5)])->assertRedirect()->assertSessionMissing('error');

        $this->assertSame(95, (int) $medicine->fresh()->available_quantity);
        $this->assertSame(1, $document->consultationMedicines()->count(), 'the row is updated, not duplicated');
        $this->assertSame(5, (int) $document->consultationMedicines()->firstOrFail()->quantity);
    }

    /** R3-H2: swapping the medicine of an existing row restores the old one and deducts the new one */
    public function test_changing_the_medicine_of_an_existing_row_swaps_the_stock(): void
    {
        $first = $this->makeMedicine('Ibuprofen');
        $second = $this->makeMedicine('Mefenamic');
        $this->stockIn($first, 50, now()->addDays(60)->toDateString());
        $this->stockIn($second, 50, now()->addDays(60)->toDateString());
        $document = $this->createConsultation([$this->row($first, 4)]);
        $rowId = $document->consultationMedicines()->firstOrFail()->id;

        $this->editConsultation($document, [$this->row($second, 6, '500mg', $rowId)])->assertRedirect()->assertSessionMissing('error');

        $this->assertSame(50, (int) $first->fresh()->available_quantity);
        $this->assertSame(44, (int) $second->fresh()->available_quantity);
        $this->assertSame([$second->id], $document->consultationMedicines()->pluck('medicine_id')->map(fn ($id) => (int) $id)->all());
    }

    /** the stock moves are traceable: every deduction and return is a ledger row for the consultation */
    public function test_every_stock_movement_of_a_consultation_is_in_the_ledger(): void
    {
        $medicine = $this->makeMedicine('Ibuprofen');
        $this->stockIn($medicine, 10, now()->addDays(60)->toDateString());
        $document = $this->createConsultation([$this->row($medicine, 4)]);
        $this->delete(route('doctors.document-issuances.destroy', $document))->assertRedirect();

        $rows = MedicineTransaction::where('reference_type', DocumentIssuance::class)
            ->where('reference_id', $document->id)->orderBy('id')->get();

        $this->assertSame([MedicineTransaction::TYPE_DISPENSE, MedicineTransaction::TYPE_ADJUSTMENT], $rows->pluck('transaction_type')->all());
        $this->assertSame([4, 4], $rows->pluck('quantity')->map(fn ($q) => (int) $q)->all());
        $this->assertSame(10, (int) $medicine->fresh()->available_quantity);
    }
}
