<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\MedicineTransaction;
use App\Models\User;
use App\Services\MedicineInventoryService;
use App\Services\Reports\YearlyInventoryReportBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

class StockControlTest extends TestCase
{
    use RefreshDatabase, BuildsClinicData;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->admin = $this->makeAdmin();
        $this->actingAs($this->admin);
        $this->travelTo(now()->startOfSecond());
    }

    private function preview($medicine, array $entries, string $mode = 'count', string $reason = 'Physical shelf count'): array
    {
        return $this->post(route('stock-control.preview'), ['medicine_id' => $medicine->id, 'mode' => $mode,
            'entries' => $entries, 'reason' => $reason])->assertOk()->viewData('preview');
    }

    private function confirm(array $preview): \Illuminate\Testing\TestResponse
    {
        return $this->postJson(route('stock-control.store'), ['preview_token' => $preview['token'], 'confirmed' => true]);
    }

    public function test_count_preview_is_read_only_and_adjusts_both_directions_without_changing_batch_identity(): void
    {
        $medicine = $this->makeMedicine();
        $a = $this->stockIn($medicine, 10, today()->addYear()->toDateString(), '500mg', 'A');
        $b = $this->stockIn($medicine, 5, today()->subDay()->toDateString(), '250mg', 'B');
        $preview = $this->preview($medicine, [$a->id => 7, $b->id => 8]);
        $this->assertSame(10, $a->fresh()->quantity);
        $this->assertDatabaseCount('medicine_transactions', 2);
        $this->assertSame([-3, 3], array_column($preview['changes'], 'difference'));
        $this->confirm($preview)->assertOk();
        $this->assertSame(7, $a->fresh()->quantity);
        $this->assertSame(8, $b->fresh()->quantity);
        $this->assertSame(15, $medicine->fresh()->quantity);
        $this->assertSame(7, $medicine->fresh()->available_quantity);
        $this->assertSame('B', $b->fresh()->batch_number);
        $this->assertSame('250mg', $b->fresh()->dosage);
        $this->assertSame([7, 8], MedicineTransaction::where('transaction_type', 'adjustment')->orderBy('batch_id')->pluck('balance_after')->all());
        $this->assertSame([3, 3], MedicineTransaction::where('transaction_type', 'adjustment')->orderBy('batch_id')->pluck('quantity')->all());
        $this->assertDatabaseHas('activity_logs', ['action' => 'stock_count']);
        $this->confirm($preview)->assertUnprocessable();
    }

    public function test_disposal_removes_only_requested_units_including_expired_stock_and_appears_in_yearly_report(): void
    {
        $medicine = $this->makeMedicine();
        $batch = $this->stockIn($medicine, 10, today()->subDay()->toDateString());
        $preview = $this->preview($medicine, [$batch->id => 4], 'expired', 'Expired stock removed from shelf');
        $this->confirm($preview)->assertOk();
        $this->assertSame(6, $batch->fresh()->quantity);
        $this->assertSame(0, $medicine->fresh()->available_quantity);
        $this->assertDatabaseHas('medicine_transactions', ['batch_id' => $batch->id, 'transaction_type' => 'disposal', 'quantity' => 4, 'balance_after' => 6]);
        $report = app(YearlyInventoryReportBuilder::class)->build((int) today()->year);
        $row = collect($report['rows'])->firstWhere('medicine_id', $medicine->id);
        $this->assertSame(4, $row['disposed']);
        $this->assertTrue($row['consistent']);
    }

    public function test_changed_stock_or_actor_or_expiry_invalidates_preview(): void
    {
        $medicine = $this->makeMedicine();
        $batch = $this->stockIn($medicine, 10, today()->addYear()->toDateString());
        $preview = $this->preview($medicine, [$batch->id => 7]);
        app(MedicineInventoryService::class)->increaseBatch($batch->id, 1);
        $this->confirm($preview)->assertUnprocessable();
        $preview = $this->preview($medicine, [$batch->id => 7]);
        $this->actingAs($this->makeAdmin());
        $this->confirm($preview)->assertUnprocessable();
        $this->actingAs($this->admin);
        $this->travel(16)->minutes();
        $this->confirm($preview)->assertUnprocessable();
        $this->assertSame(11, $batch->fresh()->quantity);
    }

    public function test_no_change_count_is_audited_once_and_does_not_create_a_zero_ledger_line(): void
    {
        $medicine = $this->makeMedicine();
        $batch = $this->stockIn($medicine, 3, today()->addYear()->toDateString());
        $preview = $this->preview($medicine, [$batch->id => 3]);
        $this->confirm($preview)->assertOk();
        $this->confirm($preview)->assertUnprocessable();
        $this->assertDatabaseCount('medicine_transactions', 1);
        $this->assertSame(1, ActivityLog::where('action', 'stock_count')->count());
    }

    public function test_invalid_quantity_batch_reason_and_disposal_on_expiry_date_are_refused(): void
    {
        $medicine = $this->makeMedicine();
        $batch = $this->stockIn($medicine, 3, today()->toDateString());
        $other = $this->stockIn($this->makeMedicine('Other'), 2, today()->addDay()->toDateString());
        foreach ([['count', [$batch->id => -1], 'Count'], ['count', [$batch->id => '1.5'], 'Count'],
            ['count', [$other->id => 1], 'Count'], ['damaged', [$batch->id => 4], 'Damaged'],
            ['expired', [$batch->id => 1], 'Expired'], ['count', [$batch->id => 1], '']] as [$mode, $entries, $reason]) {
            $this->postJson(route('stock-control.preview'), ['medicine_id' => $medicine->id, 'mode' => $mode, 'entries' => $entries, 'reason' => $reason])->assertUnprocessable();
        }
        $this->assertSame(3, $batch->fresh()->quantity);
    }

    public function test_staff_write_requires_inventory_permission_and_doctors_cannot_write_inventory_counts(): void
    {
        $staff = $this->makeNurse();
        $this->actingAs($staff)->get(route('staff.stock-control.index'))->assertOk();
        \App\Models\Role::findByName('staff')->revokePermissionTo('manage_medicines');
        $this->actingAs($staff->fresh())->get(route('staff.stock-control.index'))->assertForbidden();
        $this->postJson(route('staff.stock-control.store'), [])->assertForbidden();
        $this->actingAs($this->makeDoctor())->get(route('stock-control.index'))->assertForbidden();
        $this->postJson(route('stock-control.store'), [])->assertForbidden();
    }

    public function test_cancelled_audit_or_ledger_failure_rolls_back_the_stock_change(): void
    {
        $medicine = $this->makeMedicine();
        $batch = $this->stockIn($medicine, 3, today()->addDay()->toDateString());
        $preview = $this->preview($medicine, [$batch->id => 1]);
        ActivityLog::creating(fn () => false);
        try {
            $this->confirm($preview)->assertUnprocessable();
        } finally {
            ActivityLog::flushEventListeners();
        }
        $this->assertSame(3, $batch->fresh()->quantity);
        MedicineTransaction::creating(fn () => throw new \RuntimeException('Synthetic failure'));
        try {
            $this->confirm($preview)->assertUnprocessable();
        } finally {
            MedicineTransaction::flushEventListeners();
        }
        $this->assertSame(3, $batch->fresh()->quantity);
        $this->assertSame(0, ActivityLog::where('action', 'stock_count')->count());
    }

    public function test_stale_html_confirmation_returns_to_a_gettable_form(): void
    {
        $medicine = $this->makeMedicine();
        $batch = $this->stockIn($medicine, 3, today()->addDay()->toDateString());
        $preview = $this->preview($medicine, [$batch->id => 1]);
        app(MedicineInventoryService::class)->increaseBatch($batch->id, 1);
        $this->from(route('stock-control.preview'))->post(route('stock-control.store'), ['preview_token' => $preview['token'], 'confirmed' => true])
            ->assertRedirect(route('stock-control.index'))->assertSessionHasErrors('preview_token');
    }
}
