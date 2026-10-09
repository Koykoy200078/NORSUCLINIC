<?php

namespace Tests\Feature\Regression;

use App\Models\Medicine;
use App\Models\MedicineTransaction;
use App\Services\DatabaseIntegrityChecker;
use App\Services\InventoryConsistency;
use App\Services\OrphanBatchRecovery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 1 follow-up (owner decision, 2026-10-09): stock batches whose medicine was deleted are kept. The medicine is
 * re-created under its old id as "Unknown medicine #N" and its stock is written off through the ledger (there is no
 * inactive flag on a medicine, and every picker lists medicines with available stock), so the history stays, the
 * `medicine_batches.medicine_id` foreign key can be added, and nobody can dispense a medicine that is not there.
 */
class OrphanBatchRecoveryTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    public function test_batches_of_a_deleted_medicine_are_found_and_nothing_changes_in_a_dry_run(): void
    {
        [$healthy, $deletedId] = $this->oneHealthyAndOneDeletedMedicine();

        $orphans = app(OrphanBatchRecovery::class)->orphans();

        $this->assertSame([['medicine_id' => $deletedId, 'batches' => 2, 'units' => 30, 'dosage' => '20mg/g']], $orphans);
        $this->artisan('inventory:recover-orphan-batches')
            ->expectsOutputToContain('Dry run')
            ->assertSuccessful();
        $this->assertNull(Medicine::find($deletedId), 'a dry run creates nothing');
        $this->assertSame(30, (int) DB::table('medicine_batches')->where('medicine_id', $deletedId)->sum('quantity'));
        $this->assertNotNull(Medicine::find($healthy->id));
    }

    public function test_apply_recreates_the_medicine_under_its_old_id_and_writes_the_stock_off_through_the_ledger(): void
    {
        [$healthy, $deletedId] = $this->oneHealthyAndOneDeletedMedicine();

        $this->artisan('inventory:recover-orphan-batches', ['--apply' => true])
            ->expectsOutputToContain('Re-created 1 medicine(s)')
            ->assertSuccessful();

        $medicine = Medicine::findOrFail($deletedId);
        $this->assertSame("Unknown medicine #{$deletedId}", $medicine->name);
        $this->assertSame('20mg/g', $medicine->dosage, 'one dosage on all its batches: the placeholder keeps it');
        $this->assertSame(0, (int) $medicine->quantity);
        $this->assertSame(0, (int) $medicine->available_quantity, 'it must not appear in any picker');
        $this->assertSame(0, (int) DB::table('medicine_batches')->where('medicine_id', $deletedId)->sum('quantity'));

        $ledger = DB::table('medicine_transactions as t')->join('medicine_batches as b', 'b.id', '=', 't.batch_id')
            ->where('b.medicine_id', $deletedId)->get(['t.transaction_type', 't.quantity', 't.balance_after', 't.remarks']);
        $this->assertSame(30, (int) $ledger->where('transaction_type', MedicineTransaction::TYPE_STOCK_IN)->sum('quantity'), 'the stock-in history stays');
        $this->assertSame(30, (int) $ledger->where('transaction_type', MedicineTransaction::TYPE_DISPOSAL)->sum('quantity'));
        $this->assertStringContainsString('deleted', (string) $ledger->firstWhere('transaction_type', MedicineTransaction::TYPE_DISPOSAL)->remarks);

        $this->assertSame([], app(InventoryConsistency::class)->totalMismatches());
        $this->assertSame([], app(InventoryConsistency::class)->ledgerMismatches());
        $this->assertSame('ok', (new DatabaseIntegrityChecker())->foreignKeys()['severity']);
        $this->assertSame(10, (int) Medicine::findOrFail($healthy->id)->quantity, 'a healthy medicine is not touched');
    }

    public function test_a_second_run_finds_nothing_to_do(): void
    {
        $this->oneHealthyAndOneDeletedMedicine();
        $this->artisan('inventory:recover-orphan-batches', ['--apply' => true])->assertSuccessful();

        $this->artisan('inventory:recover-orphan-batches', ['--apply' => true])
            ->expectsOutputToContain('No stock batch points at a missing medicine')
            ->assertSuccessful();
    }

    public function test_an_expired_batch_is_written_off_too_and_mixed_dosages_leave_the_placeholder_without_one(): void
    {
        $medicine = $this->makeMedicine('Gone', ['dosage' => '10mg']);
        $this->stockIn($medicine, 5, now()->addYear()->toDateString(), '10mg', 'B-1');
        $this->stockIn($medicine, 7, now()->addYear()->toDateString(), '20mg', 'B-2');
        DB::table('medicine_batches')->where('batch_number', 'B-2')->update(['expiration_date' => now()->subMonth()->toDateString()]);
        $this->deleteMedicineWithoutChecks($medicine->id);

        $this->artisan('inventory:recover-orphan-batches', ['--apply' => true])->assertSuccessful();

        $recovered = Medicine::findOrFail($medicine->id);
        $this->assertNull($recovered->dosage);
        $this->assertSame(0, (int) DB::table('medicine_batches')->where('medicine_id', $medicine->id)->sum('quantity'));
    }

    /**
     * @return array{0: Medicine, 1: int} a healthy medicine with 10 units, and the id of a deleted one that left two batches
     *                                   (20 + 10 units of "20mg/g") behind
     */
    private function oneHealthyAndOneDeletedMedicine(): array
    {
        $healthy = $this->makeMedicine('Paracetamol');
        $this->stockIn($healthy, 10, now()->addYear()->toDateString());

        $doomed = $this->makeMedicine('Cream', ['dosage' => '20mg/g']);
        $this->stockIn($doomed, 20, now()->addYear()->toDateString(), '20mg/g', 'FA0180');
        $this->stockIn($doomed, 10, now()->addYears(2)->toDateString(), '20mg/g', 'FA0181');
        $this->deleteMedicineWithoutChecks($doomed->id);

        return [$healthy, $doomed->id];
    }

    /**
     * What the old MyISAM database allowed: deleting a medicine that still has batches.
     */
    private function deleteMedicineWithoutChecks(int $medicineId): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('medicines')->where('id', $medicineId)->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
