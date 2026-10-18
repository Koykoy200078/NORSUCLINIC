<?php

namespace Tests\Feature\Regression;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Role;
use App\Support\InventoryAlerts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Phase 5, row 5.11 Notifications & Alerts: the sidebar alerts (expired, expiring in 7 days, expiring in 30 days, low
 * stock) and the menu entries. They were counted once and kept for five minutes for everybody, so a stock change was
 * not on the badge until the cache ran out, and they were counted for roles that never see the badge.
 */
class NotificationsPassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    /** a medicine with one batch: name, expires in n days (negative = already), units in the batch */
    private function medicineWithBatch(string $name, int $days, int $units, array $attributes = []): Medicine
    {
        $medicine = $this->makeMedicine($name, $attributes);
        MedicineBatch::create([
            'medicine_id' => $medicine->id, 'batch_number' => 'B-' . $medicine->id, 'dosage' => '500mg', 'quantity' => $units, 'date_received' => now()->toDateString(),
            'expiration_date' => now()->addDays($days)->toDateString(),
        ]);

        return $medicine;
    }

    private function badgeNumbers(string $html): array
    {
        $number = function (string $pattern) use ($html) {
            return preg_match($pattern, $html, $m) === 1 ? (int) $m[1] : 0;
        };

        return [
            'expired' => $number('/Expired stock on the shelf: (\d+) medicine/'),
            'critical' => $number('/Critical: (\d+) medicine/'),
            'warning' => $number('/Warning: (\d+) medicine/'),
            'low' => $number('/Low Stock: (\d+) medicine/'),
        ];
    }

    public function test_the_alert_counts_follow_the_expiry_boundaries_and_ignore_empty_batches(): void
    {
        $this->medicineWithBatch('Zz expired yesterday', -1, 5);
        $this->medicineWithBatch('Zz expired but empty', -30, 0);
        $this->medicineWithBatch('Zz expires today', 0, 5);
        $this->medicineWithBatch('Zz in 7 days', 7, 5);
        $this->medicineWithBatch('Zz in 8 days', 8, 5);
        $this->medicineWithBatch('Zz in 30 days', 30, 5);
        $this->medicineWithBatch('Zz in 31 days', 31, 5);

        $counts = InventoryAlerts::counts();

        $this->assertSame(1, $counts['expired'], 'only yesterday: an empty batch and today do not count as expired');
        $this->assertSame(2, $counts['critical'], 'today and 7 days');
        $this->assertSame(2, $counts['warning'], '8 and 30 days');
    }

    public function test_a_medicine_with_two_batches_counts_once_and_the_sidebar_shows_the_same_numbers(): void
    {
        $medicine = $this->medicineWithBatch('Zz two batches', -2, 5);
        MedicineBatch::create(['medicine_id' => $medicine->id, 'batch_number' => 'B-second', 'dosage' => '250mg', 'quantity' => 4, 'date_received' => now()->toDateString(), 'expiration_date' => now()->addDays(3)->toDateString()]);
        // a second expired batch of the same medicine must not make it count twice
        MedicineBatch::create(['medicine_id' => $medicine->id, 'batch_number' => 'B-old', 'dosage' => '100mg', 'quantity' => 2, 'date_received' => now()->toDateString(), 'expiration_date' => now()->subDays(9)->toDateString()]);
        $this->medicineWithBatch('Zz low', 100, 3, ['minimum_stock_alert' => 10, 'available_quantity' => 3]);
        // low by its alert PERCENTAGE of the baseline (the old report rule only knew the minimum alert)
        $this->medicineWithBatch('Zz low by percent', 100, 20, ['available_quantity' => 20, 'baseline_quantity' => 100, 'stock_alert_percentage' => 25]);

        $this->assertSame(['expired' => 1, 'critical' => 1, 'warning' => 0, 'low_stock' => 2], InventoryAlerts::counts());

        $html = $this->actingAs($this->makeAdmin())->get(route('medicine-inventory.index'))->assertOk()->getContent();
        $this->assertSame(['expired' => 1, 'critical' => 1, 'warning' => 0, 'low' => 2], $this->badgeNumbers($html));
    }

    public function test_the_sidebar_follows_a_stock_change_at_once_not_after_five_minutes(): void
    {
        $admin = $this->makeAdmin();
        $medicine = $this->medicineWithBatch('Zz watched', 100, 50, ['minimum_stock_alert' => 10, 'available_quantity' => 50]);
        $this->actingAs($admin);

        $this->assertSame(0, $this->badgeNumbers($this->get(route('medicine-inventory.index'))->getContent())['low'], 'plenty of stock: no badge');

        // the stock falls below the alert, and a batch passes its date
        $medicine->update(['available_quantity' => 4]);
        MedicineBatch::where('medicine_id', $medicine->id)->update(['expiration_date' => now()->subDay()->toDateString()]);

        $numbers = $this->badgeNumbers($this->get(route('medicine-inventory.index'))->getContent());
        $this->assertSame(1, $numbers['low'], 'low stock is on the badge on the very next page');
        $this->assertSame(1, $numbers['expired'], 'and so is the expired batch');
    }

    public function test_a_role_that_cannot_open_the_inventory_does_not_pay_for_the_alert_counts(): void
    {
        $this->medicineWithBatch('Zz alert', -1, 5, ['minimum_stock_alert' => 10, 'available_quantity' => 5]);
        $nurse = $this->makeNurse();
        Role::findByName('staff')->syncPermissions(['manage_patients', 'manage_request_documents']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($nurse);
        $this->get(route('staff.dashboard'))->assertOk(); // warm the permission and settings caches

        DB::flushQueryLog();
        DB::enableQueryLog();
        $html = $this->get(route('staff.dashboard'))->assertOk()->getContent();
        $queries = implode(' ; ', array_column(DB::getQueryLog(), 'query'));
        DB::disableQueryLog();

        $this->assertStringNotContainsString('from `medicine_batches`', $queries, 'no batch is read for a role without the inventory');
        $this->assertStringNotContainsString('from `medicines`', $queries);
        $this->assertSame(['expired' => 0, 'critical' => 0, 'warning' => 0, 'low' => 0], $this->badgeNumbers($html));
    }

    public function test_the_notification_menu_entries_follow_the_permissions(): void
    {
        $nurse = $this->makeNurse();
        $this->actingAs($nurse);

        Role::findByName('staff')->syncPermissions(['manage_patients']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $html = $this->get(route('staff.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString('Low stocks alert', $html, 'the low stock list is a report, open to every staff account');
        $this->assertStringContainsString('System notifications', $html);
        $this->assertStringNotContainsString('Expiry medicine alert', $html, 'it opens the inventory, so it needs the inventory permission');

        Role::findByName('staff')->syncPermissions(['manage_patients', 'manage_medicines']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->assertStringContainsString('Expiry medicine alert', $this->get(route('staff.dashboard'))->getContent());

        // and the pages behind the entries open
        $this->get(route('staff.activity-logs.index', ['tab' => 'inventory', 'status' => 'low_stock']))->assertOk();
        $this->get(route('staff.activity-logs.index', ['tab' => 'logs']))->assertOk();
    }
}
