<?php

namespace Tests\Feature\Regression;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * `php artisan inventory:reconcile` is the read-only stock report the clinic runs after an upgrade. Its stock checks are
 * shared with `db:integrity` (App\Services\InventoryConsistency), so what it prints is pinned here.
 */
class InventoryReconcileCommandTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    public function test_consistent_stock_is_reported_as_consistent(): void
    {
        $medicine = $this->makeMedicine('Paracetamol');
        $this->stockIn($medicine, 20, now()->addYear()->toDateString());

        $this->artisan('inventory:reconcile')
            ->expectsOutputToContain('Medicines whose total differs from the sum of their batches: 0')
            ->expectsOutputToContain('Batches whose quantity differs from the last ledger balance: 0')
            ->expectsOutputToContain('Inventory is consistent.')
            ->assertSuccessful();
    }

    public function test_a_total_and_a_batch_that_disagree_are_listed_with_their_numbers(): void
    {
        $medicine = $this->makeMedicine('Paracetamol');
        $batch = $this->stockIn($medicine, 20, now()->addYear()->toDateString());
        DB::table('medicines')->where('id', $medicine->id)->update(['quantity' => 25]);
        DB::table('medicine_batches')->where('id', $batch->id)->update(['quantity' => 23]);

        $this->artisan('inventory:reconcile')
            ->expectsOutputToContain('Medicines whose total differs from the sum of their batches: 1')
            ->expectsOutputToContain('Batches whose quantity differs from the last ledger balance: 1')
            ->expectsOutputToContain('2 item(s) need attention. Nothing was changed.')
            ->assertSuccessful();
    }
}
