<?php

namespace Tests\Feature\Regression;

use App\Livewire\MedicineDispenseTable;
use App\Models\ConsultationMedicine;
use App\Models\DispenseHistoryEntry;
use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\DocumentIssuance;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 2: the Dispense History lists EVERY way medicine left the clinic - dispense records, dispensed
 * prescriptions and the medicines a doctor / nurse recorded inside a consultation - through the read-only
 * database view `dispense_history_view`. Before, consultation medicines never appeared there.
 */
class DispenseHistoryTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $admin;

    private User $doctor;

    private User $nurse;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();

        $this->admin = $this->makeAdmin();
        $this->doctor = $this->makeDoctor(['first_name' => 'Hilda', 'last_name' => 'Ramos']);
        $this->nurse = $this->makeStaff('nurse', 'triage_area', ['first_name' => 'Nora', 'last_name' => 'Nurse']);
        $this->patient = $this->makePatient();
    }

    /** @param array<int, array<string, mixed>> $plan  @param array<int, array<string, mixed>> $nursing */
    private function consultationPayload(array $plan = [], array $nursing = []): array
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
        ], $medicines ? ['medicines' => $medicines] : []);
    }

    /** @return array<string, mixed> */
    private function row(Medicine $medicine, int $quantity, ?int $id = null): array
    {
        return array_filter([
            'id' => $id,
            'medicine_id' => $medicine->id,
            'dosage' => '500mg',
            'quantity' => $quantity,
            'dosage_instructions' => '1 tablet',
        ], fn ($value) => $value !== null);
    }

    private function stockedMedicine(string $name, int $stock = 50): Medicine
    {
        $medicine = $this->makeMedicine($name);
        $this->stockIn($medicine, $stock, now()->addYear()->toDateString());

        return $medicine;
    }

    private function consultationBy(User $author, array $plan = [], array $nursing = []): DocumentIssuance
    {
        $store = $author->hasRole('staff') ? 'staff.document-issuances.store' : 'doctors.document-issuances.store';

        $this->actingAs($author)
            ->post(route($store), $this->consultationPayload($plan, $nursing))
            ->assertRedirect()
            ->assertSessionMissing('error');

        return DocumentIssuance::where('document_type', 'consultation_form')->latest('id')->firstOrFail();
    }

    private function dispenseRecordWithLines(array $lines, string $modelType = DispenseRecord::class, ?string $modelId = null, $billDate = null): DispenseRecord
    {
        $record = DispenseRecord::create([
            'history_number' => 'HIS' . generateUniqueHistoryNumber(),
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->doctor->id,
            'model_type' => $modelType,
            'model_id' => $modelId ?? (string) $this->patient->id,
            'bill_date' => $billDate ?? now(),
        ]);

        foreach ($lines as [$medicine, $quantity]) {
            DispenseRecordItem::create([
                'medicine_bill_id' => $record->id,
                'medicine_id' => $medicine->id,
                'dosage' => '500mg',
                'sale_quantity' => $quantity,
            ]);
        }

        return $record;
    }

    private function prescription(string $status): Prescription
    {
        return Prescription::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->doctor->id,
            'status' => $status,
            'is_active' => 1,
        ]);
    }

    private function entries(?string $source = null)
    {
        $query = DispenseHistoryEntry::query();

        return $source ? $query->where('source', $source)->get() : $query->get();
    }

    public function test_a_consultation_with_medicines_is_listed_with_the_total_of_its_plan_and_nursing_lines(): void
    {
        $paracetamol = $this->stockedMedicine('Paracetamol');
        $ibuprofen = $this->stockedMedicine('Ibuprofen');

        $document = $this->consultationBy($this->doctor, [$this->row($paracetamol, 2)], [$this->row($ibuprofen, 3)]);

        $entries = $this->entries('Consultation');
        $this->assertCount(1, $entries);

        $entry = $entries->first();
        $this->assertSame('C' . $document->id, $entry->id);
        $this->assertSame($document->id, (int) $entry->record_id);
        $this->assertSame(5, (int) $entry->quantity);
        $this->assertSame('Juan Dela Cruz', $entry->patient_name);
        $this->assertSame($this->patient->id, (int) $entry->patient_id);
        $this->assertSame($this->doctor->doctor->id, (int) $entry->doctor_id);
        $this->assertStringContainsString('Hilda', $entry->given_by);
        $this->assertEqualsCanonicalizing(['nursing', 'plan'], explode(',', $entry->used_for));
        $this->assertNotNull($entry->dispensed_at);
    }

    public function test_a_consultation_without_medicines_is_not_listed(): void
    {
        $this->consultationBy($this->doctor);

        $this->assertCount(0, $this->entries('Consultation'));
    }

    public function test_medicine_lines_on_another_document_type_do_not_create_a_consultation_history_row(): void
    {
        $medicine = $this->stockedMedicine('Paracetamol');
        $document = $this->consultationBy($this->doctor);
        $document->update(['document_type' => 'medical_certificate']);

        ConsultationMedicine::create([
            'request_document_id' => $document->id,
            'medicine_id' => $medicine->id,
            'quantity' => 2,
            'used_for' => 'plan',
        ]);

        $this->assertCount(0, $this->entries('Consultation'));
    }

    public function test_archived_patients_keep_their_history_without_an_unreachable_patient_link(): void
    {
        $medicine = $this->stockedMedicine('Paracetamol');
        $document = $this->consultationBy($this->doctor, [$this->row($medicine, 2)]);
        $this->patient->delete();
        $this->patient->user->delete();

        $this->assertCount(1, $this->entries('Consultation'));

        foreach ([$this->admin, $this->doctor, $this->nurse] as $user) {
            $this->actingAs($user);
            $html = Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class)->html();

            $this->assertStringContainsString('Juan Dela Cruz', $html);
            $this->assertStringNotContainsString(getRouteByRole('patients.show', [$this->patient->id]) . '"', $html);
            $this->assertStringContainsString(getRouteByRole('dispense-records.consultation', [$document->id]), $html);
        }
    }

    public function test_a_consultation_recorded_by_a_nurse_is_listed_with_the_nurse_as_the_person_who_gave_it(): void
    {
        $medicine = $this->stockedMedicine('Paracetamol');

        $document = $this->consultationBy($this->nurse, [], [$this->row($medicine, 4)]);

        $entry = $this->entries('Consultation')->firstWhere('record_id', $document->id);
        $this->assertNotNull($entry, 'a nurse-recorded consultation must be listed');
        $this->assertSame(4, (int) $entry->quantity);
        $this->assertNull($entry->doctor_id, 'the nurse is not a doctor');
        $this->assertStringContainsString('Nora', $entry->given_by);
    }

    public function test_editing_a_consultation_changes_the_listed_quantity_and_removing_every_line_removes_the_row(): void
    {
        $medicine = $this->stockedMedicine('Paracetamol');
        $document = $this->consultationBy($this->doctor, [$this->row($medicine, 5)]);
        $lineId = $document->consultationMedicines()->firstOrFail()->id;

        $payload = $this->consultationPayload([$this->row($medicine, 2, $lineId)]);
        unset($payload['document_type'], $payload['user_id']);
        $this->actingAs($this->doctor)->put(route('doctors.document-issuances.update', $document), $payload)->assertRedirect();

        $this->assertSame(2, (int) $this->entries('Consultation')->firstWhere('record_id', $document->id)->quantity);

        $payload = $this->consultationPayload();
        unset($payload['document_type'], $payload['user_id']);
        $this->actingAs($this->doctor)->put(route('doctors.document-issuances.update', $document), $payload)->assertRedirect();

        $this->assertCount(0, $this->entries('Consultation'));
    }

    public function test_a_deleted_consultation_leaves_the_history_and_comes_back_when_it_is_restored(): void
    {
        $medicine = $this->stockedMedicine('Paracetamol');
        $document = $this->consultationBy($this->doctor, [$this->row($medicine, 3)]);
        $this->assertCount(1, $this->entries('Consultation'));

        $this->actingAs($this->doctor)->delete(route('doctors.document-issuances.destroy', $document))->assertRedirect();
        $this->assertCount(0, $this->entries('Consultation'), 'the stock went back, so the history must not keep the line');

        $this->actingAs($this->admin)
            ->post(route('deleted-records.restore', ['type' => 'document', 'id' => $document->id]))
            ->assertRedirect();

        $this->assertSame(3, (int) $this->entries('Consultation')->firstWhere('record_id', $document->id)->quantity);
    }

    public function test_dispense_records_and_dispensed_prescriptions_stay_listed_and_pending_prescriptions_do_not(): void
    {
        $paracetamol = $this->stockedMedicine('Paracetamol');
        $ibuprofen = $this->stockedMedicine('Ibuprofen');

        $manual = $this->dispenseRecordWithLines([[$paracetamol, 2], [$ibuprofen, 4]]);
        $dispensedRx = $this->prescription(Prescription::DISPENSE_STATUS_DISPENSED);
        $dispensedRecord = $this->dispenseRecordWithLines([[$paracetamol, 1]], Prescription::class, (string) $dispensedRx->id);
        $pendingRx = $this->prescription(Prescription::DISPENSE_STATUS_PENDING);
        $this->dispenseRecordWithLines([[$paracetamol, 9]], Prescription::class, (string) $pendingRx->id);

        $this->assertCount(2, $this->entries(), 'the pending prescription is not dispensed yet');

        $manualEntry = $this->entries('Dispense Record')->firstOrFail();
        $this->assertSame('B' . $manual->id, $manualEntry->id);
        $this->assertSame($manual->id, (int) $manualEntry->record_id);
        $this->assertSame($manual->history_number, $manualEntry->history_number);
        $this->assertSame(6, (int) $manualEntry->quantity);
        $this->assertSame($this->doctor->doctor->id, (int) $manualEntry->doctor_id);

        $rxEntry = $this->entries('Prescription')->firstOrFail();
        $this->assertSame('B' . $dispensedRecord->id, $rxEntry->id);
        $this->assertSame(1, (int) $rxEntry->quantity);
    }

    public function test_the_table_lists_every_source_and_the_source_filter_narrows_it(): void
    {
        $medicine = $this->stockedMedicine('Paracetamol');
        $this->consultationBy($this->doctor, [$this->row($medicine, 2)]);
        $this->dispenseRecordWithLines([[$medicine, 3]]);
        $rx = $this->prescription(Prescription::DISPENSE_STATUS_DISPENSED);
        $this->dispenseRecordWithLines([[$medicine, 1]], Prescription::class, (string) $rx->id);

        $this->actingAs($this->admin);
        $component = Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class);

        $component->assertSee('Consultation')->assertSee('Dispense Record')->assertSee('Prescription');
        $this->assertCount(3, $component->instance()->getRows());

        foreach (['Consultation', 'Dispense Record', 'Prescription'] as $source) {
            $filtered = Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class)->set('sourceFilter', $source);
            $this->assertCount(1, $filtered->instance()->getRows(), "source filter '{$source}'");
        }

        $all = Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class)->set('sourceFilter', 'Consultation')->set('sourceFilter', '');
        $this->assertCount(3, $all->instance()->getRows());

        // a value that is not one of the three sources (the property is editable from the browser) is ignored, not "no rows"
        $forged = Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class)->set('sourceFilter', 'Everything');
        $this->assertCount(3, $forged->instance()->getRows());
    }

    public function test_a_consultation_row_is_found_by_the_patient_name_and_by_the_doctor_name(): void
    {
        $medicine = $this->stockedMedicine('Paracetamol');
        $this->consultationBy($this->doctor, [$this->row($medicine, 2)]);

        $this->actingAs($this->admin);
        foreach (['Juan', 'Dela Cruz', 'Cruz, Juan', 'Hilda Ramos', 'Ramos'] as $term) {
            $rows = Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class)->set('search', $term)->instance()->getRows();
            $this->assertCount(1, $rows, "searching '{$term}'");
        }

        $none = Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class)->set('search', 'Nobodyhere')->instance()->getRows();
        $this->assertCount(0, $none);
    }

    public function test_the_history_sorts_by_the_requested_column_in_both_directions(): void
    {
        $medicine = $this->stockedMedicine('Paracetamol');
        $document = $this->consultationBy($this->doctor, [$this->row($medicine, 2)]);
        $record = $this->dispenseRecordWithLines([[$medicine, 5]]);
        // created_at is not fillable: update() silently kept both rows at the same second, so "newest first" was never tested
        $document->forceFill(['created_at' => now()->subDay()])->saveQuietly();
        $this->assertTrue($document->fresh()->created_at->lt(now()->subHours(12)), 'the consultation must really be older');

        $this->actingAs($this->admin);
        $component = Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class);
        $this->assertSame(['B' . $record->id, 'C' . $document->id], $component->instance()->getRows()->pluck('id')->all());

        $component->call('sortBy', 'quantity');
        $this->assertSame([2, 5], $component->instance()->getRows()->pluck('quantity')->all());

        $component->call('sortBy', 'quantity');
        $this->assertSame([5, 2], $component->instance()->getRows()->pluck('quantity')->all());
    }

    public function test_a_consultation_row_offers_only_view_while_a_dispense_record_row_keeps_edit_and_delete(): void
    {
        $medicine = $this->stockedMedicine('Paracetamol');
        $document = $this->consultationBy($this->doctor, [$this->row($medicine, 2)]);
        $record = $this->dispenseRecordWithLines([[$medicine, 3]]);
        $prescription = $this->prescription(Prescription::DISPENSE_STATUS_DISPENSED);
        $prescriptionRecord = $this->dispenseRecordWithLines([[$medicine, 1]], Prescription::class, (string) $prescription->id);

        $this->actingAs($this->admin);
        $html = Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class)->html();

        $this->assertStringContainsString(route('dispense-records.consultation', $document->id), $html);
        $this->assertStringContainsString(route('dispense-records.show', $record->id), $html);
        $this->assertStringContainsString(route('dispense-records.edit', $record->id), $html);
        $this->assertStringNotContainsString(route('dispense-records.edit', $document->id) . '"', $html);
        $this->assertStringContainsString(route('dispense-records.show', $prescriptionRecord->id), $html);
        $this->assertStringNotContainsString(route('dispense-records.edit', $prescriptionRecord->id) . '"', $html);
        $this->assertSame(1, substr_count($html, 'medicine-bill-delete-btn'), 'only the dispense record can be deleted from here');
    }

    public function test_the_consultation_dispense_page_shows_the_medicines_and_follows_the_role_access(): void
    {
        $paracetamol = $this->stockedMedicine('Paracetamol');
        $ibuprofen = $this->stockedMedicine('Ibuprofen');
        $document = $this->consultationBy($this->doctor, [$this->row($paracetamol, 2)], [$this->row($ibuprofen, 3)]);

        $this->actingAs($this->admin)
            ->get(route('dispense-records.consultation', $document->id))
            ->assertOk()
            ->assertSee('Paracetamol')->assertSee('Ibuprofen')
            ->assertSee('Juan Dela Cruz')
            ->assertSee(route('document-issuances.show', $document->id), false);

        $this->actingAs($this->doctor)
            ->get(route('doctors.dispense-records.consultation', $document->id))
            ->assertOk()->assertSee('Paracetamol');

        $pharmacist = $this->makeStaff('pharmacist', 'pharmacy');
        \App\Models\Role::findByName('staff')->revokePermissionTo('manage_request_documents');
        $response = $this->actingAs($pharmacist->fresh())->get(route('staff.dispense-records.consultation', $document->id));
        $response->assertOk()->assertSee('Paracetamol');
        // A role without document permission has no consultation access: no link into the consultation itself.
        $response->assertDontSee(route('staff.document-issuances.show', $document->id), false);

        $this->actingAs($this->patient->user)
            ->get(route('dispense-records.consultation', $document->id))
            ->assertForbidden();
    }

    public function test_the_consultation_dispense_page_answers_404_for_a_deleted_consultation_or_another_kind_of_document(): void
    {
        $medicine = $this->stockedMedicine('Paracetamol');
        $document = $this->consultationBy($this->doctor, [$this->row($medicine, 2)]);

        $certificate = DocumentIssuance::whereKey($document->id)->firstOrFail()->replicate();
        $certificate->document_type = 'medical_certificate';
        $certificate->save();

        $this->actingAs($this->admin)->get(route('dispense-records.consultation', $certificate->id))->assertNotFound();

        $this->actingAs($this->doctor)->delete(route('doctors.document-issuances.destroy', $document))->assertRedirect();
        $this->actingAs($this->admin)->get(route('dispense-records.consultation', $document->id))->assertNotFound();
    }

    public function test_staff_with_consultation_access_can_follow_the_consultation_link_from_dispensing(): void
    {
        $medicine = $this->stockedMedicine('Paracetamol');
        $document = $this->consultationBy($this->doctor, [$this->row($medicine, 2)]);
        $clinicHead = $this->makeStaff('clinic_head', 'front_desk');

        $this->actingAs($clinicHead)
            ->get(route('staff.dispense-records.consultation', $document->id))
            ->assertOk()
            ->assertSee(route('staff.document-issuances.show', $document->id), false);

        $this->get(route('staff.document-issuances.show', $document->id))->assertOk();
    }

    /**
     * Re-check 2026-10-11. Every manual dispense record of a day is saved at 00:00:00 (the form only has a date), so a busy
     * day is a long run of IDENTICAL sort values. Without a unique last sort key MySQL may order the ties differently for
     * each page query: one page repeats rows another page never shows (reproduced: 60 same-day records over 6 pages
     * listed 48 distinct rows and repeated 23).
     */
    public function test_paging_never_repeats_or_skips_rows_that_share_the_same_date(): void
    {
        $medicine = $this->stockedMedicine('Paracetamol', 500);
        $day = now()->subDays(3)->startOfDay();
        $created = [];
        for ($i = 0; $i < 60; $i++) {
            $created[] = 'B' . $this->dispenseRecordWithLines([[$medicine, 1 + ($i % 5)]], DispenseRecord::class, null, $day)->id;
        }

        $this->actingAs($this->admin);
        $component = Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class);

        $seen = [];
        foreach (range(1, 6) as $page) {
            $component->call('gotoPage', $page);
            foreach ($component->instance()->getRows()->pluck('id') as $id) {
                $seen[] = $id;
            }
        }

        $this->assertCount(60, $seen, 'six pages of ten rows');
        $this->assertCount(60, array_unique($seen), 'a row repeated on another page');
        $this->assertEqualsCanonicalizing($created, $seen, 'a row never appeared on any page');
        $this->assertSame(array_reverse($created), $seen, 'among rows with the same date the newest record comes first');

        // The same must hold when another column is sorted (quantity has only five different values here).
        $component->call('sortBy', 'quantity');
        $seen = [];
        foreach (range(1, 6) as $page) {
            $component->call('gotoPage', $page);
            foreach ($component->instance()->getRows()->pluck('id') as $id) {
                $seen[] = $id;
            }
        }
        $this->assertEqualsCanonicalizing($created, $seen, 'sorted by quantity, rows repeated or went missing between pages');
    }

    public function test_the_rows_show_the_number_the_parts_the_units_and_the_person_who_gave_the_medicine(): void
    {
        $paracetamol = $this->stockedMedicine('Paracetamol');
        $ibuprofen = $this->stockedMedicine('Ibuprofen');
        $document = $this->consultationBy($this->doctor, [$this->row($paracetamol, 2)], [$this->row($ibuprofen, 3)]);
        $entry = $this->entries('Consultation')->firstOrFail();

        $this->actingAs($this->admin);

        $this->assertStringContainsString('#CONS-' . $document->id, view('medicine-history.columns.bill_id', ['row' => $entry])->render());
        $this->assertSame('5', trim(view('medicine-history.columns.dispensed_quantity', ['row' => $entry])->render()));

        $source = view('medicine-history.columns.source', ['row' => $entry])->render();
        $this->assertStringContainsString('Consultation', $source);
        $this->assertStringContainsString('Nursing + Plan', $source);

        $date = view('medicine-history.columns.bill_date', ['row' => $entry])->render();
        $this->assertStringContainsString($entry->dispensed_at->format('h:i A'), $date);
        $this->assertStringContainsString($entry->dispensed_at->translatedFormat('jS M, Y'), $date);

        $html = Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class)->html();
        $this->assertStringContainsString('Juan Dela Cruz', $html);
        $this->assertStringContainsString('Hilda Ramos', $html);
        $this->assertStringContainsString('aria-label="Filter by source"', $html, 'the source filter needs an accessible name');
    }

    public function test_a_prescription_dispensed_through_the_dispensing_screen_enters_the_history_with_the_units_handed_out(): void
    {
        $medicine = $this->stockedMedicine('Amoxicillin', 100);
        $prescription = $this->prescription(Prescription::DISPENSE_STATUS_PENDING);
        PrescriptionMedicine::create([
            'prescription_id' => $prescription->id,
            'medicine' => $medicine->id,
            'dosage' => '500mg',
            'route_of_administration' => 'oral',
            'frequency' => 1,
            'duration_value' => 6,
            'duration_unit' => 'day',
            'total_quantity' => 6,
        ]);
        // what the prescription form creates alongside: the dispense record with the PRESCRIBED line, dated when it was written
        $this->dispenseRecordWithLines([[$medicine, 6]], Prescription::class, (string) $prescription->id, now()->subDays(3));

        $this->assertCount(0, $this->entries(), 'a pending prescription moved no stock, so it is not history yet');

        $before = now()->subSecond();
        $this->actingAs($this->makeNurse())->postJson(route('staff.prescriptions.dispense', $prescription->id))->assertOk();

        $entry = $this->entries('Prescription')->sole();
        $this->assertSame(6, (int) $entry->quantity, 'the units handed out');
        $this->assertTrue($entry->dispensed_at->greaterThanOrEqualTo($before), 'dated when it was dispensed, not when it was written');
        $this->assertSame(94, (int) $medicine->fresh()->available_quantity);
        $this->assertCount(1, $this->entries(), 'one row for the prescription, no duplicate');
    }

    /**
     * Re-check 2026-10-11. The patient's own history page has a card with the same title as the Dispensing tab. It listed
     * every dispense record of the patient, so pending and cancelled prescriptions (nothing was handed out) showed up as
     * "dispensed", and the medicines recorded in consultations were missing, the very gap Phase 2 closed in the tab.
     */
    public function test_the_patients_history_page_lists_what_the_dispense_history_tab_lists(): void
    {
        $ibuprofen = $this->stockedMedicine('Ibuprofen');
        $paracetamol = $this->stockedMedicine('Paracetamol');
        $consultamol = $this->stockedMedicine('Consultamol');
        $pendingcillin = $this->makeMedicine('Pendingcillin');
        $cancelcillin = $this->makeMedicine('Cancelcillin');

        $this->dispenseRecordWithLines([[$ibuprofen, 4]]);
        $dispensed = $this->prescription(Prescription::DISPENSE_STATUS_DISPENSED);
        $this->dispenseRecordWithLines([[$paracetamol, 2]], Prescription::class, (string) $dispensed->id);
        $pending = $this->prescription(Prescription::DISPENSE_STATUS_PENDING);
        $this->dispenseRecordWithLines([[$pendingcillin, 9]], Prescription::class, (string) $pending->id);
        $cancelled = $this->prescription(Prescription::DISPENSE_STATUS_CANCELLED);
        $this->dispenseRecordWithLines([[$cancelcillin, 7]], Prescription::class, (string) $cancelled->id);
        $document = $this->consultationBy($this->doctor, [$this->row($consultamol, 3)]);

        $html = $this->actingAs($this->admin)->get(route('patients.showMyHistory', $this->patient->id))->assertOk()->getContent();
        $card = Str::after($html, 'Medicine Dispense History');

        $this->assertStringContainsString('Ibuprofen', $card, 'the manual dispense record');
        $this->assertStringContainsString('Paracetamol', $card, 'the dispensed prescription');
        $this->assertStringContainsString('Consultamol', $card, 'the medicine recorded in the consultation');
        $this->assertStringNotContainsString('Pendingcillin', $card, 'a pending prescription handed nothing out');
        $this->assertStringNotContainsString('Cancelcillin', $card, 'a cancelled prescription handed nothing out');
        $this->assertStringContainsString('3 record(s)', $card);
        $this->assertStringContainsString('#CONS-' . $document->id, $card, 'the consultation row carries the same number as in the tab');

        // a deleted consultation gave its stock back: it leaves this card too
        $this->actingAs($this->doctor)->delete(route('doctors.document-issuances.destroy', $document))->assertRedirect();
        $card = Str::after($this->actingAs($this->admin)->get(route('patients.showMyHistory', $this->patient->id))->getContent(), 'Medicine Dispense History');
        $this->assertStringNotContainsString('Consultamol', $card);
        $this->assertStringContainsString('2 record(s)', $card);
    }
}
