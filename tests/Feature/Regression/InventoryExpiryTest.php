<?php

namespace Tests\Feature\Regression;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * R3-H5 (2026-10-01 re-audit): medicines.available_quantity was only recalculated when stock moved, so a
 * batch that expired kept counting as available for days, and nothing warned about expired stock on the shelf.
 */
class InventoryExpiryTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function medicineWithExpiredBatch(string $name = 'Ibuprofen', int $quantity = 5): Medicine
    {
        $medicine = $this->makeMedicine($name);
        $this->stockIn($medicine, $quantity, now()->addDay()->toDateString());
        $this->travelTo(now()->addDays(3)); // the batch has expired; the stored available_quantity is stale

        return $medicine;
    }

    public function test_the_sync_command_recalculates_available_stock_after_a_batch_expires(): void
    {
        $expired = $this->medicineWithExpiredBatch();
        $mixed = $this->makeMedicine('Mixed');
        $this->stockIn($mixed, 4, now()->subDay()->toDateString(), '500mg', 'OLD');
        $this->stockIn($mixed, 6, now()->addMonths(3)->toDateString(), '500mg', 'NEW');

        $this->assertSame(5, (int) $expired->fresh()->available_quantity, 'stale before the command runs');

        $this->artisan('inventory:sync-expiry')->assertSuccessful();

        $this->assertSame(0, (int) $expired->fresh()->available_quantity);
        $this->assertSame(5, (int) $expired->fresh()->quantity, 'the expired units are still physically there');
        $this->assertSame(6, (int) $mixed->fresh()->available_quantity);
    }

    public function test_the_dry_run_reports_but_changes_nothing(): void
    {
        $expired = $this->medicineWithExpiredBatch();

        $this->artisan('inventory:sync-expiry', ['--dry-run' => true])
            ->expectsOutputToContain('Ibuprofen')
            ->assertSuccessful();

        $this->assertSame(5, (int) $expired->fresh()->available_quantity);
    }

    public function test_the_command_is_scheduled_every_day(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('inventory:sync-expiry')->assertSuccessful();
    }

    public function test_the_sidebar_warns_about_expired_stock_and_refreshes_the_stored_quantity(): void
    {
        $expired = $this->medicineWithExpiredBatch();
        $admin = $this->makeAdmin();

        $html = $this->actingAs($admin)->get(route('medicine-inventory.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Expired stock on the shelf: 1 medicine(s)', $html);
        // The page render already corrected the stored figure, even though the scheduler has not run.
        $this->assertSame(0, (int) $expired->fresh()->available_quantity);
    }

    public function test_an_out_of_stock_medicine_that_had_stock_counts_as_low_stock_in_the_sidebar(): void
    {
        $medicine = $this->makeMedicine('Almostgone', ['minimum_stock_alert' => 10]);
        $this->stockIn($medicine, 3, now()->addMonths(3)->toDateString());
        // Dispense everything: the medicine is now at zero.
        MedicineBatch::where('medicine_id', $medicine->id)->update(['quantity' => 0]);
        app(\App\Services\MedicineInventoryService::class)->syncMedicineTotals($medicine->id);
        $this->assertSame(0, (int) $medicine->fresh()->available_quantity);

        $never = $this->makeMedicine('Nevercamein', ['minimum_stock_alert' => 10]); // catalogue entry, never stocked

        $html = $this->actingAs($this->makeAdmin())->get(route('medicine-inventory.index'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/Low Stock: 1 medicine\(s\)/', $html);
    }
}
