<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\InventoryCount;
use App\Models\InventoryCountLine;
use App\Models\Medicine;
use App\Models\Role;
use App\Models\User;
use App\Services\InventoryCountService;
use App\Services\MedicineInventoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Quarterly count sheets (Phase 11 D): the clinic types the final remaining count of each medicine for a quarter (or for
 * everything on the day it counted). The system amount a sheet starts from is the stock ledger's figure at the end of that
 * quarter (the very figure of the yearly report) or, for a count of everything, the shelf today. Every movement here goes
 * through the real inventory service on a fixed day, so the ledger really crosses the quarter boundaries.
 *
 * Ledger of the fixture: medicine A in 100 on 2026-02-10, 10 out on 03-05, 5 out on 04-10 (Q1 ends at 90, now 85);
 * medicine B in 40 on 05-01. "Today" is 2026-07-15.
 */
class InventoryCountServiceTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private InventoryCountService $service;

    private User $nurse;

    private User $admin;

    private Medicine $a;

    private Medicine $b;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->admin = $this->makeAdmin();
        $this->nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse']);
        $this->a = $this->makeMedicine('Alpha', ['dosage' => '500mg', 'uom' => 'tablet']);
        $this->b = $this->makeMedicine('Beta', ['dosage' => '10mg', 'uom' => 'capsule']);

        Carbon::setTestNow('2026-02-10 10:00:00');
        $this->stockIn($this->a, 100, '2028-01-01', '500mg', 'LOT-A');
        Carbon::setTestNow('2026-03-05 10:00:00');
        app(MedicineInventoryService::class)->deductStockFefo($this->a->id, 10, $this->nurse->id, null, 'test dispense');
        Carbon::setTestNow('2026-04-10 10:00:00');
        app(MedicineInventoryService::class)->deductStockFefo($this->a->id, 5, $this->nurse->id, null, 'test dispense');
        Carbon::setTestNow('2026-05-01 10:00:00');
        $this->stockIn($this->b, 40, '2028-01-01', '10mg', 'LOT-B');
        Carbon::setTestNow('2026-07-15 12:00:00');

        $this->service = app(InventoryCountService::class);
        $this->actingAs($this->nurse);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function sheet(array $over = [], ?User $actor = null): InventoryCount
    {
        return $this->service->create($over + ['year' => 2026, 'quarter' => 1, 'scope' => 'all', 'counted_on' => '2026-07-15', 'notes' => null], $actor ?? $this->nurse);
    }

    private function system(InventoryCount $sheet): array
    {
        return $sheet->lines()->orderBy('medicine_name')->pluck('system_quantity', 'medicine_name')->map(fn ($v) => (int) $v)->all();
    }

    private function line(InventoryCount $sheet, Medicine $medicine): InventoryCountLine
    {
        return $sheet->lines()->where('medicine_id', $medicine->id)->firstOrFail();
    }

    private function actions(): array
    {
        return ActivityLog::whereIn('action', ['inventory_count_created', 'inventory_count_saved', 'inventory_count_refreshed', 'inventory_count_finalized', 'inventory_count_deleted'])
            ->orderBy('id')->pluck('action')->all();
    }

    // ------------------------------------------------------------------ making a sheet

    public function test_a_past_quarter_starts_from_the_ledger_figure_at_the_end_of_that_quarter(): void
    {
        $q1 = $this->sheet(['quarter' => 1]);
        $this->assertSame(['Alpha' => 90, 'Beta' => 0], $this->system($q1), 'end of March: 100 - 10; Beta not stocked yet');

        $q2 = $this->sheet(['quarter' => 2]);
        $this->assertSame(['Alpha' => 85, 'Beta' => 40], $this->system($q2), 'end of June');

        $this->assertSame('draft', $q1->status);
        $this->assertSame('all', $q1->scope);
        $this->assertSame($this->nurse->id, $q1->created_by);
        $this->assertSame(1, $q1->quarter);
        $line = $this->line($q1, $this->a);
        $this->assertSame(['Alpha', '500mg', 'tablet'], [$line->medicine_name, $line->strength, $line->unit]);
        $this->assertNull($line->counted_quantity);
    }

    public function test_the_quarter_in_progress_and_a_count_of_everything_start_from_the_shelf_today(): void
    {
        $this->assertSame(['Alpha' => 85, 'Beta' => 40], $this->system($this->sheet(['quarter' => 3])));

        $all = $this->sheet(['quarter' => null, 'year' => 1999, 'counted_on' => '2026-07-14']);
        $this->assertNull($all->quarter);
        $this->assertSame(2026, $all->year, 'a count of everything belongs to the year it was counted in');
        $this->assertSame(['Alpha' => 85, 'Beta' => 40], $this->system($all));
    }

    public function test_a_quarter_that_has_not_started_a_future_day_and_a_silly_year_are_refused(): void
    {
        foreach ([['quarter' => 4], ['quarter' => 1, 'year' => 2030], ['quarter' => 5], ['quarter' => 0], ['quarter' => 1, 'year' => 1800], ['counted_on' => '2026-07-16'], ['counted_on' => 'not a date'], ['scope' => 'everything']] as $bad) {
            try {
                $this->sheet($bad);
                $this->fail('a sheet was made from ' . json_encode($bad));
            } catch (ValidationException) {
                $this->assertSame(0, InventoryCount::count(), json_encode($bad));
            }
        }
    }

    public function test_a_selected_sheet_holds_only_the_chosen_medicines(): void
    {
        $sheet = $this->sheet(['scope' => 'selected', 'medicine_ids' => [$this->b->id]]);

        $this->assertSame(['Beta' => 0], $this->system($sheet));
        $this->assertSame('selected', $sheet->scope);

        foreach ([[], [999999], ['x']] as $ids) {
            try {
                $this->sheet(['scope' => 'selected', 'medicine_ids' => $ids]);
                $this->fail('a selected sheet was made from ' . json_encode($ids));
            } catch (ValidationException) {
                $this->assertSame(1, InventoryCount::count());
            }
        }
    }

    public function test_a_medicine_that_was_merged_away_is_not_on_a_new_sheet(): void
    {
        $this->b->forceFill(['merged_into_id' => $this->a->id])->save();

        $this->assertSame(['Alpha' => 90], $this->system($this->sheet()));
    }

    // ------------------------------------------------------------------ who may

    public function test_only_staff_and_admin_with_the_medicines_permission_may_make_change_and_finalize_a_sheet(): void
    {
        $sheet = $this->sheet();
        $nobody = [
            'a doctor' => $this->makeDoctor(),
            'a patient' => $this->makePatient()->patientUser,
        ];
        $off = $this->makeNurse(['status' => 0]);
        $nobody['a deactivated nurse'] = $off;

        foreach ($nobody as $who => $actor) {
            $this->actingAs($actor);
            foreach ([
                fn () => $this->service->create(['year' => 2026, 'quarter' => 1, 'scope' => 'all', 'counted_on' => '2026-07-15'], $actor),
                fn () => $this->service->saveCounts($sheet, [], $actor),
                fn () => $this->service->refreshSystem($sheet, $actor),
                fn () => $this->service->finalize($sheet, $actor),
                fn () => $this->service->delete($sheet, $actor),
            ] as $action) {
                try {
                    $action();
                    $this->fail("$who was allowed");
                } catch (HttpException $e) {
                    $this->assertSame(403, $e->getStatusCode(), $who);
                }
            }
        }

        Role::findByName('staff')->revokePermissionTo('manage_medicines');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->nurse->fresh());
        $this->expectException(HttpException::class);
        $this->service->saveCounts($sheet, [], $this->nurse->fresh());
    }

    // ------------------------------------------------------------------ saving counts

    public function test_saving_stores_the_count_and_the_difference_and_a_blank_count_means_not_counted(): void
    {
        $sheet = $this->sheet(['quarter' => 1]);

        $this->service->saveCounts($sheet, [
            $this->a->id => ['counted' => '82', 'reason' => 'damaged', 'note' => 'wet blister packs'],
            $this->b->id => ['counted' => '', 'reason' => 'lost', 'note' => 'not counted yet'],
        ], $this->nurse);

        $a = $this->line($sheet, $this->a);
        $this->assertSame([82, -8, 'damaged', 'wet blister packs'], [$a->counted_quantity, $a->difference, $a->reason, $a->note]);
        $b = $this->line($sheet, $this->b);
        $this->assertSame([null, null, null, 'not counted yet'], [$b->counted_quantity, $b->difference, $b->reason, $b->note], 'no count: no difference and no reason');

        $this->service->saveCounts($sheet, [$this->a->id => ['counted' => '90', 'reason' => 'damaged', 'note' => 'checked again']], $this->nurse);
        $a = $this->line($sheet, $this->a);
        $this->assertSame([90, 0, null], [$a->counted_quantity, $a->difference, $a->reason], 'a count that matches the system needs no reason');
        $this->assertSame('not counted yet', $this->line($sheet, $this->b)->note, 'a row that is not sent is left as it was');
    }

    public function test_a_bad_row_refuses_the_whole_save_and_changes_nothing(): void
    {
        $sheet = $this->sheet(['quarter' => 1]);
        $this->service->saveCounts($sheet, [$this->a->id => ['counted' => '85', 'reason' => 'lost', 'note' => 'first']], $this->nurse);

        foreach ([
            [$this->b->id => ['counted' => '-1']],
            [$this->b->id => ['counted' => '1.5']],
            [$this->b->id => ['counted' => 'abc']],
            [$this->b->id => ['counted' => '2147483648']],
            [$this->b->id => ['counted' => '5', 'reason' => 'made up']],
            [$this->b->id => ['counted' => '5', 'reason' => 'lost', 'note' => str_repeat('x', 501)]],
            [999999 => ['counted' => '5']],
        ] as $rows) {
            try {
                $this->service->saveCounts($sheet, [$this->a->id => ['counted' => '1', 'reason' => 'lost', 'note' => 'changed']] + $rows, $this->nurse);
                $this->fail('a bad save was accepted: ' . json_encode($rows));
            } catch (ValidationException) {
                $a = $this->line($sheet, $this->a);
                $this->assertSame([85, 'first'], [$a->counted_quantity, $a->note], 'nothing of a refused save is kept: ' . json_encode($rows));
            }
        }
    }

    // ------------------------------------------------------------------ refreshing the system amounts

    public function test_refreshing_takes_the_new_system_amounts_and_recomputes_the_differences(): void
    {
        $sheet = $this->sheet(['quarter' => null, 'counted_on' => '2026-07-15']);
        $this->service->saveCounts($sheet, [$this->a->id => ['counted' => '80', 'reason' => 'lost', 'note' => 'n']], $this->nurse);
        $this->assertSame(-5, $this->line($sheet, $this->a)->difference);

        app(MedicineInventoryService::class)->deductStockFefo($this->a->id, 2, $this->nurse->id, null, 'later dispense');
        $this->assertSame(85, $this->line($sheet, $this->a)->system_quantity, 'the sheet keeps the amount of the day it was made until it is refreshed');

        $this->service->refreshSystem($sheet, $this->nurse);

        $a = $this->line($sheet, $this->a);
        $this->assertSame([83, 80, -3, 'lost', 'n'], [$a->system_quantity, $a->counted_quantity, $a->difference, $a->reason, $a->note]);
        $this->assertNull($this->line($sheet, $this->b)->difference, 'an uncounted line stays uncounted');

        // a later movement can make the count right: the difference is gone and so is its reason
        app(MedicineInventoryService::class)->deductStockFefo($this->a->id, 3, $this->nurse->id, null, 'another dispense');
        $this->service->refreshSystem($sheet, $this->nurse);
        $a = $this->line($sheet, $this->a);
        $this->assertSame([80, 80, 0, null], [$a->system_quantity, $a->counted_quantity, $a->difference, $a->reason]);
    }

    // ------------------------------------------------------------------ finalizing

    public function test_a_sheet_is_finalized_only_with_a_count_and_a_reason_for_every_difference(): void
    {
        $sheet = $this->sheet(['quarter' => 1]);

        $refuse = function (string $why) use ($sheet) {
            try {
                $this->service->finalize($sheet, $this->nurse);
                $this->fail("finalized although $why");
            } catch (ValidationException $e) {
                $this->assertSame('draft', $sheet->fresh()->status, $why);

                return implode(' ', $e->errors()['finalize'] ?? []);
            }
        };

        $refuse('nothing was counted');

        $this->service->saveCounts($sheet, [$this->a->id => ['counted' => '88', 'reason' => '', 'note' => '']], $this->nurse);
        $this->assertStringContainsString('Alpha', $refuse('a difference has no reason'));

        $this->service->saveCounts($sheet, [$this->a->id => ['counted' => '88', 'reason' => 'other', 'note' => '']], $this->nurse);
        $this->assertStringContainsString('Alpha', $refuse('"other" has no note'));

        $this->service->saveCounts($sheet, [$this->a->id => ['counted' => '88', 'reason' => 'other', 'note' => 'two boxes sent to the dental clinic']], $this->nurse);
        $done = $this->service->finalize($sheet, $this->nurse);

        $this->assertSame('finalized', $done->status);
        $this->assertSame($this->nurse->id, $done->finalized_by);
        $this->assertEquals('2026-07-15 12:00:00', $done->finalized_at->toDateTimeString());
        $this->assertNull($this->line($sheet, $this->b)->counted_quantity, 'a medicine that was not counted stays out of the count');
    }

    public function test_a_finalized_sheet_cannot_be_changed_refreshed_finalized_again_or_deleted(): void
    {
        $sheet = $this->sheet(['quarter' => 1]);
        $this->service->saveCounts($sheet, [$this->a->id => ['counted' => '90']], $this->nurse);
        $this->service->finalize($sheet, $this->nurse);

        foreach ([
            fn () => $this->service->saveCounts($sheet, [$this->a->id => ['counted' => '1', 'reason' => 'lost']], $this->nurse),
            fn () => $this->service->refreshSystem($sheet, $this->nurse),
            fn () => $this->service->finalize($sheet, $this->nurse),
            fn () => $this->service->delete($sheet, $this->nurse),
        ] as $action) {
            try {
                $action();
                $this->fail('a finalized sheet was changed');
            } catch (ValidationException $e) {
                $this->assertStringContainsString('finalized', implode(' ', collect($e->errors())->flatten()->all()));
            }
        }
        $this->assertSame(90, $this->line($sheet, $this->a)->counted_quantity);
        $this->assertSame(1, InventoryCount::count());
    }

    public function test_a_draft_can_be_deleted_with_its_lines_and_every_step_leaves_an_audit_line(): void
    {
        $sheet = $this->sheet(['quarter' => 1]);
        $this->service->saveCounts($sheet, [$this->a->id => ['counted' => '90']], $this->nurse);
        $this->service->refreshSystem($sheet, $this->nurse);
        $this->service->finalize($sheet, $this->nurse);
        $other = $this->sheet(['quarter' => 2]);
        $this->service->delete($other, $this->nurse);

        $this->assertSame(0, InventoryCount::whereKey($other->id)->count());
        $this->assertSame(0, InventoryCountLine::where('inventory_count_id', $other->id)->count());
        $this->assertSame(['inventory_count_created', 'inventory_count_saved', 'inventory_count_refreshed', 'inventory_count_finalized', 'inventory_count_created', 'inventory_count_deleted'], $this->actions());
    }
}
