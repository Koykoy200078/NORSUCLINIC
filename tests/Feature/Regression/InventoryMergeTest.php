<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\ConsultationMedicine;
use App\Models\DocumentIssuance;
use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\Generic;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineTransaction;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\PurchasedMedicine;
use App\Models\StockIn;
use App\Models\User;
use App\Repositories\InventoryMergeRepository;
use App\Services\MedicineInventoryService;
use App\Services\Reports\YearlyInventoryReportBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

class InventoryMergeTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->admin = $this->makeAdmin();
        $this->actingAs($this->admin);
        $this->travelTo(now()->setDate(2026, 10, 20)->startOfDay());
    }

    private function pair(): array
    {
        $generic = Generic::create(['name' => 'Paracetamol']);
        $category = Category::create(['name' => 'Pain relief', 'is_active' => 1]);
        $common = ['generic_id' => $generic->id, 'generic_name' => $generic->name, 'uom' => 'tablet', 'category_id' => $category->id];

        return [$this->makeMedicine('Duplicate brand', $common + ['dosage' => '250mg']),
            $this->makeMedicine('Kept brand', $common + ['dosage' => '500mg'])];
    }

    private function preview(string $kind, int $source, int $target): array
    {
        return $this->get('/admin/inventory-merge?'.http_build_query(['kind' => $kind, 'source_id' => $source, 'target_id' => $target]))
            ->assertOk()->viewData('preview');
    }

    private function merge(string $kind, int $source, int $target, array $preview): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/admin/inventory-merge', ['kind' => $kind, 'source_id' => $source, 'target_id' => $target,
            'preview_token' => $preview['token'], 'confirmed' => true]);
    }

    public function test_admin_has_a_merge_tool_and_other_roles_have_no_access(): void
    {
        $this->get('/admin/inventory-merge')->assertOk()->assertSee('Merge duplicate inventory records');
        foreach ([$this->makeNurse(), $this->makeDoctor(), $this->makePatient()->patientUser] as $actor) {
            $this->actingAs($actor)->get('/admin/inventory-merge')->assertForbidden();
            $this->postJson('/admin/inventory-merge', [])->assertForbidden();
        }
    }

    public function test_preview_is_read_only_and_confirmation_preserves_batch_and_ledger_identity(): void
    {
        [$source, $target] = $this->pair();
        $target->update(['name' => $source->name, 'dosage' => $source->dosage]);
        $this->assertCount(1, app(\App\Services\DatabaseIntegrityChecker::class)->duplicateCandidates()['warnings']);
        $keptBatch = $this->stockIn($target, 10, '2027-03-01', '500mg', 'LOT');
        $movedBatch = $this->stockIn($source, 6, '2027-01-01', '250mg', 'LOT');
        $expired = $this->stockIn($source, 4, '2026-10-19', '250mg', 'OLD');
        $beforeLedger = DB::table('medicine_transactions')->orderBy('id')->get()->toArray();
        $beforeBatches = DB::table('medicine_batches')->orderBy('id')->get()->toArray();
        $beforeSource = $source->fresh()->getAttributes();
        $preview = $this->preview('medicine', $source->id, $target->id);

        $this->assertNotEmpty($preview['token']);
        $this->assertSame([], $preview['blockers']);
        $this->assertSame($beforeSource, $source->fresh()->getAttributes());
        $this->assertEquals($beforeBatches, DB::table('medicine_batches')->orderBy('id')->get()->toArray());
        $this->assertDatabaseCount('activity_logs', 0);
        $this->merge('medicine', $source->id, $target->id, $preview)->assertOk();

        $this->assertNull(Medicine::find($source->id));
        $archived = Medicine::withMerged()->findOrFail($source->id);
        $this->assertSame($target->id, $archived->merged_into_id);
        $this->assertSame($this->admin->id, $archived->merged_by);
        $this->assertNotNull($archived->merged_at);
        $this->assertSame(20, $target->fresh()->quantity);
        $this->assertSame(16, $target->fresh()->available_quantity);
        $this->assertSame(0, $archived->available_quantity);
        $this->assertSame([], app(\App\Services\DatabaseIntegrityChecker::class)->duplicateCandidates()['warnings']);
        $this->assertSame($target->id, $movedBatch->fresh()->medicine_id);
        $this->assertSame(6, $movedBatch->fresh()->quantity);
        $this->assertSame('2027-01-01', $movedBatch->fresh()->expiration_date->toDateString());
        $this->assertSame('LOT', $movedBatch->fresh()->original_batch_number);
        $this->assertNotSame('LOT', $movedBatch->fresh()->batch_number);
        $this->assertSame('LOT', $keptBatch->fresh()->batch_number);
        $this->assertSame($target->id, $expired->fresh()->medicine_id);
        $this->assertEquals($beforeLedger, DB::table('medicine_transactions')->orderBy('id')->get()->toArray());
        $audit = ActivityLog::where('action', 'inventory_merged')->sole();
        $this->assertSame('medicine', $audit->properties['kind']);
        $this->assertSame($source->id, $audit->properties['source']['id']);
        $this->assertSame($target->id, $audit->properties['target']['id']);
        $this->assertNotEmpty($audit->properties['batch_renames']);
        $this->get('/admin/medicines/'.$source->id.'/edit')->assertNotFound();
        $this->merge('medicine', $source->id, $target->id, $preview)->assertUnprocessable();
        $this->assertDatabaseCount('medicine_batches', 3);
        $oldPreviewUrl = route('inventory-merge.index', ['kind' => 'medicine', 'source_id' => $source->id, 'target_id' => $target->id]);
        $this->withHeader('Referer', $oldPreviewUrl)->get($oldPreviewUrl)->assertOk()
            ->assertSee('already merged')->assertDontSee('name="preview_token"', false);
    }

    public function test_changed_or_expired_or_other_actor_previews_cannot_be_confirmed(): void
    {
        [$source, $target] = $this->pair();
        $preview = $this->preview('medicine', $source->id, $target->id);
        $this->stockIn($source, 2, '2027-01-01');
        $this->merge('medicine', $source->id, $target->id, $preview)->assertUnprocessable()->assertSee('preview expired or these records changed');
        $preview = $this->preview('medicine', $source->id, $target->id);
        $this->travelTo(now()->addMinutes(16));
        $this->merge('medicine', $source->id, $target->id, $preview)->assertUnprocessable();
        $preview = $this->preview('medicine', $source->id, $target->id);
        $this->actingAs($this->makeAdmin());
        $this->merge('medicine', $source->id, $target->id, $preview)->assertUnprocessable();
        $this->assertNotNull($source->fresh());
        $this->assertDatabaseCount('activity_logs', 0);
    }

    /** @dataProvider incompatiblePairs */
    public function test_incompatible_ingredients_or_units_block_medicine_merges(array $change): void
    {
        [$source, $target] = $this->pair();
        $source->update($change);
        $preview = $this->preview('medicine', $source->id, $target->id);
        $this->assertNotEmpty($preview['blockers']);
        $this->assertNull($preview['token']);
        $this->assertNotNull($source->fresh());
    }

    public static function incompatiblePairs(): array
    {
        return [
            'ingredient' => [['generic_name' => 'Ibuprofen']],
            'unit' => [['uom' => 'mL']],
            'missing unit' => [['uom' => null]],
            'missing ingredient' => [['generic_name' => null, 'generic_id' => null]],
        ];
    }

    public function test_legacy_stock_is_materialized_without_being_lost_in_a_merge(): void
    {
        [$source, $target] = $this->pair();
        // An aggregate-only fixture represents stock from before batches existed.
        $source->update(['available_quantity' => 5, 'quantity' => 5]);
        $target->update(['available_quantity' => 3, 'quantity' => 3]);
        $preview = $this->preview('medicine', $source->id, $target->id);
        $this->assertDatabaseCount('medicine_batches', 0);
        $this->merge('medicine', $source->id, $target->id, $preview)->assertOk();
        $this->assertSame(8, $target->fresh()->available_quantity);
        $this->assertSame(8, (int) MedicineBatch::where('medicine_id', $target->id)->sum('quantity'));
        $this->assertDatabaseCount('medicine_transactions', 2);
    }

    public function test_category_and_generic_merges_repoint_ids_and_saved_labels_and_hide_archives(): void
    {
        [$medicine] = $this->pair();
        foreach (['category' => Category::class, 'generic' => Generic::class] as $kind => $class) {
            $column = $kind.'_id';
            $source = $class::findOrFail($medicine->fresh()->$column);
            $target = $class::create(['name' => 'Kept '.$kind, 'is_active' => 1]);
            $preview = $this->preview($kind, $source->id, $target->id);
            $this->merge($kind, $source->id, $target->id, $preview)->assertOk();
            $this->assertNull($class::find($source->id));
            $this->assertSame($target->id, $medicine->fresh()->$column);
            $this->assertSame($target->name, $medicine->fresh()->getAttribute($kind === 'category' ? 'category' : 'generic_name'));
            $this->assertSame($target->id, $class::withMerged()->find($source->id)->merged_into_id);
            $this->deleteJson('/admin/'.($kind === 'category' ? 'categories' : 'generics').'/'.$target->id)->assertJsonPath('success', false);
        }
        $this->assertSame(2, ActivityLog::where('action', 'inventory_merged')->count());
    }

    public function test_yearly_report_combines_both_histories_without_new_stock_movements(): void
    {
        [$source, $target] = $this->pair();
        $this->travelTo(now()->setDate(2025, 12, 15));
        $this->stockIn($target, 7, '2027-02-01', '500mg', 'B');
        $this->travelTo(now()->setDate(2026, 3, 15));
        $this->stockIn($source, 5, '2027-01-01', '250mg', 'A');
        $ledger = DB::table('medicine_transactions')->orderBy('id')->get()->toArray();
        $this->travelTo(now()->setDate(2026, 10, 20));
        $this->merge('medicine', $source->id, $target->id, $this->preview('medicine', $source->id, $target->id))->assertOk();
        $rows = app(YearlyInventoryReportBuilder::class)->build(2026)['rows'];
        $this->assertCount(1, $rows);
        $this->assertSame($target->id, $rows[0]['medicine_id']);
        $this->assertSame(7, $rows[0]['left']);
        $this->assertSame(5, $rows[0]['additional']);
        $this->assertSame(12, $rows[0]['ending']);
        $this->assertTrue($rows[0]['consistent']);
        $this->assertTrue($rows[0]['matches_today']);
        $this->assertDatabaseCount('medicine_transactions', 2);
        $this->assertEquals($ledger, DB::table('medicine_transactions')->orderBy('id')->get()->toArray());
    }

    public function test_failure_writing_audit_rolls_back_every_merge_write(): void
    {
        [$source, $target] = $this->pair();
        $batch = $this->stockIn($source, 3, '2027-01-01');
        $preview = $this->preview('medicine', $source->id, $target->id);
        ActivityLog::creating(function (ActivityLog $log): void {
            if ($log->action === 'inventory_merged') {
                throw new \RuntimeException('Synthetic audit failure');
            }
        });
        try {
            app(InventoryMergeRepository::class)->merge('medicine', $source->id, $target->id, $preview['token'], $this->admin);
            $this->fail('Audit failure must abort the merge.');
        } catch (\RuntimeException $error) {
            $this->assertSame('Synthetic audit failure', $error->getMessage());
        }
        $this->assertNotNull($source->fresh());
        $this->assertSame($source->id, $batch->fresh()->medicine_id);
        $this->assertSame(3, $source->fresh()->available_quantity);
        $this->assertSame(0, $target->fresh()->available_quantity);
    }

    public function test_all_reference_rows_keep_ids_and_null_strengths_keep_the_source_meaning(): void
    {
        [$source, $target] = $this->pair();
        $batch = $this->stockIn($source, 10, '2027-01-01', '250mg', 'SOURCE');
        $patient = $this->makePatient();
        $doctor = $this->makeDoctor();
        $document = DocumentIssuance::create(['document_type' => 'consultation_form', 'document_creator_id' => $doctor->id,
            'user_id' => $patient->user_id, 'name' => 'Synthetic patient', 'age' => 23, 'gender' => 'Male', 'address' => 'Test address', 'requested_at' => now()->toDateString()]);
        $line = ConsultationMedicine::create(['request_document_id' => $document->id, 'medicine_id' => $source->id,
            'dosage' => null, 'quantity' => 2, 'used_for' => 'nursing', 'dosage_instructions' => 'Keep these instructions']);
        app(MedicineInventoryService::class)->deductStockFefo($source->id, 2, $this->admin->id, $document, null,
            MedicineTransaction::TYPE_DISPENSE, '250mg');
        $prescription = Prescription::create(['patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id,
            'status' => 'pending', 'is_active' => 1, 'consultation_date' => now()->toDateString()]);
        $rxLine = PrescriptionMedicine::create(['prescription_id' => $prescription->id, 'medicine' => $source->id,
            'dosage' => null, 'route_of_administration' => 'oral', 'frequency' => 1, 'duration_value' => 2, 'duration_unit' => 'day', 'total_quantity' => 2]);
        $dispense = DispenseRecord::create(['history_number' => 'MERGE-HISTORY', 'patient_id' => $patient->id,
            'doctor_id' => $doctor->doctor->id, 'model_type' => Prescription::class, 'model_id' => $prescription->id, 'bill_date' => now()]);
        $dispenseLine = DispenseRecordItem::create(['dispense_id' => $dispense->id, 'medicine_id' => $source->id,
            'quantity' => 2, 'dosage' => null, 'expiry_date' => '2027-01-01']);
        $stockIn = StockIn::create(['availability_no' => 'MERGE-STOCK']);
        $stockLine = PurchasedMedicine::create(['medicine_availabilities_id' => $stockIn->id, 'medicine_id' => $source->id,
            'batch_id' => $batch->id, 'dosage' => null, 'quantity' => 10, 'expiry_date' => '2027-01-01', 'manufacturing_date' => '2026-01-01']);
        $legacyId = DB::table('used_medicines')->insertGetId(['medicine_id' => $source->id, 'stock_used' => 2,
            'model_type' => DocumentIssuance::class, 'model_id' => $document->id, 'created_at' => now(), 'updated_at' => now()]);
        $ledger = DB::table('medicine_transactions')->orderBy('id')->get()->toArray();
        $this->merge('medicine', $source->id, $target->id, $this->preview('medicine', $source->id, $target->id))->assertOk();

        foreach ([$line, $dispenseLine, $stockLine] as $row) {
            $this->assertSame($target->id, (int) $row->fresh()->medicine_id);
            $this->assertSame('250mg', $row->fresh()->dosage);
        }
        $this->assertSame($target->id, $rxLine->fresh()->medicine);
        $this->assertSame('250mg', $rxLine->fresh()->dosage);
        $this->assertSame($target->id, (int) DB::table('used_medicines')->where('id', $legacyId)->value('medicine_id'));
        $this->assertSame($batch->id, $stockLine->fresh()->batch_id);
        $this->assertSame('Keep these instructions', $line->fresh()->dosage_instructions);
        $this->assertEquals($ledger, DB::table('medicine_transactions')->orderBy('id')->get()->toArray());
        $this->assertSame(1, count(DB::table('used_medicines_view')->where('medicine_id', $target->id)->get()));
    }

    public function test_editing_a_moved_consultation_restores_its_exact_batch_even_after_expiry(): void
    {
        [$source, $target] = $this->pair();
        $batch = $this->stockIn($source, 10, '2026-11-01', '250mg', 'SOURCE');
        $this->stockIn($target, 5, '2027-12-01', '500mg', 'TARGET');
        $document = DocumentIssuance::create(['document_type' => 'consultation_form', 'document_creator_id' => $this->admin->id,
            'user_id' => $this->makePatient()->user_id, 'name' => 'Synthetic patient', 'age' => 23, 'gender' => 'Male', 'address' => 'Test address', 'requested_at' => now()->toDateString()]);
        $rows = ['nursing' => [['medicine_id' => $source->id, 'dosage' => '250mg', 'quantity' => 3]]];
        app(\App\Services\ConsultationMedicineService::class)->store($document, ['medicines' => $rows]);
        $line = $document->consultationMedicines()->sole();
        $this->merge('medicine', $source->id, $target->id, $this->preview('medicine', $source->id, $target->id))->assertOk();
        $this->travelTo(now()->addMonths(2));
        app(\App\Services\ConsultationMedicineService::class)->update($document, ['medicines' => ['nursing' => [[
            'id' => $line->id, 'medicine_id' => $target->id, 'dosage' => '250mg', 'quantity' => 1,
        ]]]]);
        $this->assertSame(9, $batch->fresh()->quantity);
        $this->assertSame('2026-11-01', $batch->fresh()->expiration_date->toDateString());
        $this->assertSame(5, $target->fresh()->available_quantity);
        $this->assertSame(1, $line->fresh()->quantity);
    }

    public function test_existing_same_strength_consultation_lines_remain_editable_after_merging(): void
    {
        [$source, $target] = $this->pair();
        $source->update(['dosage' => '500mg']);
        $this->stockIn($source, 10, '2027-01-01');
        $this->stockIn($target, 10, '2027-02-01');
        $document = DocumentIssuance::create(['document_type' => 'consultation_form', 'document_creator_id' => $this->admin->id,
            'user_id' => $this->makePatient()->user_id, 'name' => 'Synthetic patient', 'age' => 23, 'gender' => 'Male', 'address' => 'Test address', 'requested_at' => now()->toDateString()]);
        app(\App\Services\ConsultationMedicineService::class)->store($document, ['medicines' => ['nursing' => [
            ['medicine_id' => $source->id, 'dosage' => '500mg', 'quantity' => 2],
            ['medicine_id' => $target->id, 'dosage' => '500mg', 'quantity' => 3],
        ]]]);
        $this->merge('medicine', $source->id, $target->id, $this->preview('medicine', $source->id, $target->id))->assertOk();
        $rows = $document->consultationMedicines()->get()->map(fn ($line) => ['id' => $line->id,
            'medicine_id' => $target->id, 'dosage' => '500mg', 'quantity' => $line->quantity])->all();
        app(\App\Services\ConsultationMedicineService::class)->update($document, ['medicines' => ['nursing' => $rows]]);
        $this->assertCount(2, $document->consultationMedicines()->get());
        $this->assertSame(15, $target->fresh()->available_quantity);
        $this->expectException(\RuntimeException::class);
        $rows[] = ['medicine_id' => $target->id, 'dosage' => '500mg', 'quantity' => 1];
        app(\App\Services\ConsultationMedicineService::class)->update($document, ['medicines' => ['nursing' => $rows]]);
    }

    public function test_stock_operations_refuse_the_archived_id_from_a_stale_form(): void
    {
        [$source, $target] = $this->pair();
        $this->stockIn($source, 5, '2027-01-01', '250mg');
        $this->merge('medicine', $source->id, $target->id, $this->preview('medicine', $source->id, $target->id))->assertOk();
        $ledgerCount = MedicineTransaction::count();
        foreach (['add', 'deduct', 'restore'] as $operation) {
            try {
                $inventory = app(MedicineInventoryService::class);
                if ($operation === 'add') {
                    $inventory->recordStockIn(['medicine_id' => $source->id, 'quantity' => 1, 'dosage' => '250mg', 'expiration_date' => '2027-01-01']);
                } elseif ($operation === 'deduct') {
                    $inventory->deductStockFefo($source->id, 1);
                } else {
                    $inventory->restoreStock($source->id, 1);
                }
                $this->fail('An archived identity must not accept stock movements.');
            } catch (\RuntimeException $error) {
                $this->assertStringContainsString('merged', $error->getMessage());
            }
        }
        $this->assertSame($ledgerCount, MedicineTransaction::count());
        $this->assertSame(5, $target->fresh()->available_quantity);
    }

    public function test_a_pending_prescription_with_both_identities_can_still_be_edited_after_merge(): void
    {
        [$source, $target] = $this->pair();
        $source->update(['dosage' => '500mg']);
        $patient = $this->makePatient();
        $doctor = $this->makeDoctor();
        $prescription = Prescription::create(['patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id,
            'status' => 'pending', 'is_active' => 1, 'consultation_date' => now()->toDateString()]);
        $rows = [];
        foreach ([$source, $target] as $index => $medicine) {
            $row = ['medicine_id' => $medicine->id, 'dosage' => '500mg', 'route_of_administration' => 'oral',
                'frequency' => 1, 'duration_value' => 2, 'duration_unit' => 'day', 'total_quantity' => 2,
                'instructions' => 'Original instruction '.$index];
            PrescriptionMedicine::create(['prescription_id' => $prescription->id, 'medicine' => $medicine->id] + $row);
            $rows[] = array_merge($row, ['medicine_id' => $target->id]);
        }
        $this->merge('medicine', $source->id, $target->id, $this->preview('medicine', $source->id, $target->id))->assertOk();
        $payload = ['patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id,
            'consultation_date' => now()->toDateString(), 'medicines' => $rows, 'advice' => 'Edited after merge'];
        $this->put(route('prescriptions.update', $prescription), $payload)->assertRedirect()->assertSessionMissing('error');
        $this->assertSame('Edited after merge', $prescription->fresh()->advice);
        $this->assertSame(['Original instruction 0', 'Original instruction 1'], $prescription->getMedicine()->orderBy('id')->pluck('instructions')->all());
        $payload['medicines'][] = $rows[0];
        $payload['advice'] = 'Must not be saved';
        $this->put(route('prescriptions.update', $prescription), $payload)->assertRedirect();
        $this->assertSame('Edited after merge', $prescription->fresh()->advice, 'A new duplicate is still refused.');
    }

    public function test_old_category_and_generic_labels_resolve_to_the_retained_record_on_new_medicines(): void
    {
        [$medicine] = $this->pair();
        $category = Category::create(['name' => 'Canonical category', 'is_active' => 1]);
        $generic = Generic::create(['name' => 'Canonical generic']);
        $this->merge('category', $medicine->category_id, $category->id, $this->preview('category', $medicine->category_id, $category->id))->assertOk();
        $this->merge('generic', $medicine->generic_id, $generic->id, $this->preview('generic', $medicine->generic_id, $generic->id))->assertOk();
        $this->post(route('medicines.store'), ['generic_name' => 'Paracetamol', 'category' => 'Pain relief',
            'brand_name' => 'New brand after merge', 'dosage' => '500mg', 'uom' => 'tablet', 'reorder_level' => 0])
            ->assertRedirect()->assertSessionMissing('error');
        $new = Medicine::where('brand_name', 'New brand after merge')->sole();
        $this->assertSame($category->id, $new->category_id);
        $this->assertSame($generic->id, $new->generic_id);
        $this->assertSame($generic->name, $new->generic_name);
        $this->assertSame($category->name, $new->category);
        $this->assertSame(1, Category::count());
        $this->assertSame(1, Generic::count());
    }

    public function test_confirmation_and_untampered_preview_are_required(): void
    {
        [$source, $target] = $this->pair();
        $preview = $this->preview('medicine', $source->id, $target->id);
        $this->postJson('/admin/inventory-merge', ['kind' => 'medicine', 'source_id' => $source->id,
            'target_id' => $target->id, 'preview_token' => $preview['token']])->assertUnprocessable();
        $preview['token'] .= 'tampered';
        $this->merge('medicine', $source->id, $target->id, $preview)->assertUnprocessable();
        $this->getJson('/admin/inventory-merge?kind=medicine&source_id='.$source->id.'&target_id='.$source->id)->assertUnprocessable();
        $this->getJson('/admin/inventory-merge?kind=patients')->assertUnprocessable();
        $this->assertNotNull($source->fresh());
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_matching_ingredient_and_unit_ignore_case_and_surrounding_spaces(): void
    {
        [$source, $target] = $this->pair();
        $source->update(['generic_name' => '  PARACETAMOL ', 'uom' => ' Tablet ']);
        $preview = $this->preview('medicine', $source->id, $target->id);
        $this->assertSame([], $preview['blockers']);
        $this->merge('medicine', $source->id, $target->id, $preview)->assertOk();
        $this->assertSame('tablet', $target->fresh()->uom);
    }

    public function test_legacy_label_preview_and_audit_use_the_same_database_matching_as_the_transfer(): void
    {
        $source = Category::create(['name' => 'Café remedy', 'is_active' => 1]);
        $target = Category::create(['name' => 'Retained remedy', 'is_active' => 1]);
        $medicine = $this->makeMedicine('Legacy label medicine', ['category_id' => null,
            'category' => 'Cafe remedy', 'category_name' => null]);
        $preview = $this->preview('category', $source->id, $target->id);
        $this->assertSame(1, collect($preview['counts'])->firstWhere('label', 'Medicine labels')['count']);
        $this->merge('category', $source->id, $target->id, $preview)->assertOk();
        $this->assertSame($target->id, $medicine->fresh()->category_id);
        $this->assertSame([$medicine->id], ActivityLog::where('action', 'inventory_merged')->sole()->properties['moved_ids']['medicines']);
    }
}
