<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\MedicineTransaction;
use App\Models\User;
use App\Services\MedicineInventoryService;
use App\Services\Reports\YearlyInventoryReportBuilder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
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

    /**
     * The replay check used to be `ActivityLog::where('properties->stock_preview_nonce', ...)->lockForUpdate()`: an
     * unindexed JSON scan that locked the whole audit table until the confirmation committed, so every other audit
     * write in the clinic (a sign-in, a patient save, ...) waited on it. The medicine row lock already serializes
     * confirmations, so the check must not lock the audit table at all.
     */
    public function test_confirming_does_not_lock_the_audit_table_against_other_audit_writes(): void
    {
        $medicine = $this->makeMedicine();
        $batch = $this->stockIn($medicine, 3, today()->addYear()->toDateString());
        $preview = $this->preview($medicine, [$batch->id => 2]);
        config(['database.connections.audit_probe' => array_merge(DB::connection()->getConfig(), ['name' => 'audit_probe'])]);
        $other = DB::connection('audit_probe');
        $other->statement('SET SESSION innodb_lock_wait_timeout = 1');
        $blocked = null;
        // Runs while confirm() is inside its transaction, after the replay check: a second connection writes an
        // unrelated audit row, as another user's request would.
        ActivityLog::creating(function () use ($other, &$blocked) {
            try {
                $other->table('activity_logs')->insert(['action' => 'login', 'description' => 'unrelated audit row']);
                $other->table('activity_logs')->where('description', 'unrelated audit row')->delete();
                $blocked = false;
            } catch (QueryException) {
                $blocked = true;
            }
        });
        try {
            $this->confirm($preview)->assertOk();
        } finally {
            ActivityLog::flushEventListeners();
            DB::purge('audit_probe');
        }

        $this->assertFalse($blocked, 'an unrelated audit write must not wait for a stock confirmation');
        $this->assertSame(2, $batch->fresh()->quantity);
    }

    public function test_a_preview_already_claimed_by_a_confirmation_in_flight_is_refused_without_writing_anything(): void
    {
        $medicine = $this->makeMedicine();
        $batch = $this->stockIn($medicine, 3, today()->addYear()->toDateString());
        $preview = $this->preview($medicine, [$batch->id => 3]);
        // The other confirmation of this same preview claimed it before this one got the medicine lock. Its audit row is not
        // visible to this transaction's snapshot yet, so only the claim can tell them apart.
        $nonce = json_decode(Crypt::decryptString($preview['token']), true)['nonce'];
        Cache::add('stock-preview-nonce:'.$nonce, true, now()->addMinutes(20));

        $this->confirm($preview)->assertUnprocessable();

        $this->assertSame(0, ActivityLog::where('action', 'stock_count')->count());
        $this->assertDatabaseCount('medicine_transactions', 1);
        $this->assertSame(3, $batch->fresh()->quantity);
    }

    public function test_a_confirmed_preview_stays_used_up_even_if_the_cache_is_cleared(): void
    {
        $medicine = $this->makeMedicine();
        $batch = $this->stockIn($medicine, 3, today()->addYear()->toDateString());
        $preview = $this->preview($medicine, [$batch->id => 3]);
        $this->confirm($preview)->assertOk();

        // The claim lives in the cache; the audit row is the durable record. A `cache:clear` within the preview's 15 minutes
        // must not let the same preview be confirmed (and audited) a second time.
        Cache::flush();
        $this->confirm($preview)->assertUnprocessable();

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

    public function test_the_repository_refuses_an_actor_who_may_not_change_stock_even_when_it_is_called_directly(): void
    {
        $medicine = $this->makeMedicine();
        $batch = $this->stockIn($medicine, 10, today()->addYear()->toDateString());
        $data = ['medicine_id' => $medicine->id, 'mode' => 'count', 'entries' => [$batch->id => 7], 'reason' => 'Physical shelf count'];
        $repository = app(\App\Repositories\StockControlRepository::class);
        $preview = $repository->preview($data, $this->admin);
        // The routes already check the role and the permission; this is the second layer, so it is called without them.
        $refuses = function (User $actor) use ($repository, $data, $preview): void {
            foreach ([fn () => $repository->preview($data, $actor), fn () => $repository->confirm($preview['token'], $actor)] as $call) {
                try {
                    $call();
                    $this->fail('The repository accepted '.$actor->email);
                } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
                    $this->assertSame(403, $error->getStatusCode());
                }
            }
        };

        $staff = $this->makeNurse();
        $this->assertArrayHasKey('token', $repository->preview($data, $staff));

        $refuses($this->makeDoctor());
        $refuses($this->makeNurse(['status' => false]));
        $archived = $this->makeNurse();
        $archived->delete();
        $refuses($archived);
        \App\Models\Role::findByName('staff')->revokePermissionTo('manage_medicines');
        $refuses($staff->fresh());

        $this->assertSame(10, $batch->fresh()->quantity);
        $this->assertDatabaseMissing('activity_logs', ['action' => 'stock_count']);
    }

    public function test_the_inventory_page_offers_its_tools_as_real_buttons_in_one_row_only_to_the_roles_that_may_use_them(): void
    {
        // 2026-10-28 owner report: both links were drawn as plain text (btn-light-primary is in no stylesheet), one above the other.
        $this->get(route('medicine-inventory.index'))->assertOk()
            ->assertSee('id="inventory-tools"', false)
            ->assertSee('<a class="btn btn-outline-primary" href="'.route('stock-control.index').'">', false)
            ->assertSee('<a class="btn btn-outline-primary" href="'.route('inventory-merge.index').'">', false)
            ->assertSee('Stock disposal and physical count')
            ->assertSee('Merge duplicates')
            ->assertDontSee('btn-light-primary');

        $this->actingAs($this->makeNurse())->get(route('staff.medicine-inventory.index'))->assertOk()
            ->assertSee('<a class="btn btn-outline-primary" href="'.route('staff.stock-control.index').'">', false)
            ->assertDontSee('Merge duplicates');

        // A doctor is read-only in the inventory: no count or merge tool, only the read-only list of the count sheets (Phase 11 D).
        $this->actingAs($this->makeDoctor())->get(route('doctors.medicine-inventory.index'))->assertOk()
            ->assertSee('<a class="btn btn-outline-primary" href="'.route('doctors.inventory-counts.index').'">', false)
            ->assertDontSee('Stock disposal and physical count')
            ->assertDontSee('Merge duplicates');
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
