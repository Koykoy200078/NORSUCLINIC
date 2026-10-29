<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\InventoryCount;
use App\Models\InventoryCountLine;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineTransaction;
use App\Models\User;
use App\Services\InventoryCountService;
use App\Services\MedicineInventoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Correcting the shelf from a finalized count sheet (Phase 11 D): the owner chose "record the count, then apply it to the stock
 * on confirm". The correction is the existing previewed, confirmed and audited batch count of the Stock control screen, opened
 * from a sheet line; the batch counts must add up to what the sheet counted, and a line is corrected once. Only a finalized
 * line with a difference whose system amount is still what the shelf holds can be corrected: after that the shelf moved on.
 *
 * Fixture (all counted today, 2026-07-15, a count of everything): A has two batches (50 + 35 = 85), counted 80 (lost);
 * B has 40, counted 40 (no difference); C has one batch of 30, counted 26 (damaged).
 */
class InventoryCountApplyTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $nurse;

    private Medicine $a;

    private Medicine $b;

    private Medicine $c;

    private MedicineBatch $a1;

    private MedicineBatch $a2;

    private MedicineBatch $c1;

    private InventoryCount $sheet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse']);
        $this->a = $this->makeMedicine('Alpha', ['dosage' => '500mg', 'uom' => 'tablet']);
        $this->b = $this->makeMedicine('Beta', ['dosage' => '10mg', 'uom' => 'capsule']);
        $this->c = $this->makeMedicine('Gamma', ['dosage' => '5mg', 'uom' => 'tablet']);

        Carbon::setTestNow('2026-06-01 09:00:00');
        $this->a1 = $this->stockIn($this->a, 50, '2028-01-01', '500mg', 'A1');
        $this->a2 = $this->stockIn($this->a, 35, '2028-06-01', '500mg', 'A2');
        $this->stockIn($this->b, 40, '2028-01-01', '10mg', 'B1');
        $this->c1 = $this->stockIn($this->c, 30, '2028-01-01', '5mg', 'C1');
        Carbon::setTestNow('2026-07-15 12:00:00');

        $this->actingAs($this->nurse);
        $service = app(InventoryCountService::class);
        $this->sheet = $service->create(['year' => 2026, 'quarter' => null, 'scope' => 'all', 'counted_on' => '2026-07-15'], $this->nurse);
        $service->saveCounts($this->sheet, [
            $this->a->id => ['counted' => '80', 'reason' => 'lost', 'note' => 'two boxes missing'],
            $this->b->id => ['counted' => '40'],
            $this->c->id => ['counted' => '26', 'reason' => 'damaged', 'note' => 'wet'],
        ], $this->nurse);
        $service->finalize($this->sheet, $this->nurse);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function line(Medicine $medicine): InventoryCountLine
    {
        return $this->sheet->lines()->where('medicine_id', $medicine->id)->firstOrFail();
    }

    private function preview(Medicine $medicine, array $entries, ?int $lineId, array $over = [])
    {
        return $this->post(route('staff.stock-control.preview'), $over + ['medicine_id' => $medicine->id, 'mode' => 'count', 'entries' => $entries,
            'reason' => 'Count sheet correction', 'count_line_id' => $lineId]);
    }

    private function confirm(array $preview)
    {
        return $this->postJson(route('staff.stock-control.store'), ['preview_token' => $preview['token'], 'confirmed' => true]);
    }

    private function shelf(Medicine $medicine): int
    {
        return (int) MedicineBatch::where('medicine_id', $medicine->id)->sum('quantity');
    }

    // ------------------------------------------------------------------ the sheet offers it

    public function test_the_sheet_offers_correct_stock_only_where_the_shelf_still_matches_and_never_to_a_doctor(): void
    {
        $page = $this->get(route('staff.inventory-counts.show', $this->sheet));
        $page->assertOk()->assertSee('href="' . e(route('staff.stock-control.index', ['medicine_id' => $this->a->id, 'count_line_id' => $this->line($this->a)->id])) . '"', false)
            ->assertSee('Correct stock');
        $this->assertSame(2, substr_count($page->getContent(), 'Correct stock'), 'A and C have a difference; B has none');

        $this->actingAs($this->makeDoctor())->get(route('doctors.inventory-counts.show', $this->sheet))->assertOk()->assertDontSee('Correct stock');

        // the shelf moves on: A is dispensed after the count
        $this->actingAs($this->nurse);
        app(MedicineInventoryService::class)->deductStockFefo($this->a->id, 1, $this->nurse->id, null, 'later dispense');
        $page = $this->get(route('staff.inventory-counts.show', $this->sheet));
        $this->assertSame(1, substr_count($page->getContent(), 'Correct stock'), 'only C is left');
        $page->assertSee('The shelf changed since this count (system 85, shelf now 84)');
    }

    public function test_the_stock_control_page_opened_from_a_line_explains_the_count_and_fills_a_single_batch(): void
    {
        $this->get(route('staff.stock-control.index', ['medicine_id' => $this->c->id, 'count_line_id' => $this->line($this->c)->id]))->assertOk()
            ->assertSee('Count sheet 2026, all medicines counted 2026-07-15')->assertSee('counted 26')->assertSee('system 30')
            ->assertSee('name="count_line_id"', false)->assertSee('value="' . $this->line($this->c)->id . '"', false)
            ->assertSee('name="entries[' . $this->c1->id . ']"', false)->assertSee('value="26"', false);

        $page = $this->get(route('staff.stock-control.index', ['medicine_id' => $this->a->id, 'count_line_id' => $this->line($this->a)->id]));
        $page->assertOk()->assertSee('counted 80')->assertSee('add up to 80');
        $this->assertStringNotContainsString('value="80"', $page->getContent(), 'two batches: the person splits the count');

        $this->get(route('staff.stock-control.index', ['medicine_id' => $this->b->id]))->assertOk()->assertDontSee('Count sheet 2026');
    }

    // ------------------------------------------------------------------ doing it

    public function test_correcting_one_batch_sets_the_shelf_to_the_count_and_marks_the_line_corrected(): void
    {
        $line = $this->line($this->c);
        $preview = $this->preview($this->c, [$this->c1->id => 26], $line->id)->assertOk()->viewData('preview');
        $this->assertSame(30, $this->shelf($this->c), 'a preview changes nothing');
        $this->assertNull($line->fresh()->adjusted_at);

        $this->confirm($preview)->assertOk();

        $this->assertSame(26, $this->shelf($this->c));
        $this->assertSame(1, MedicineTransaction::where('batch_id', $this->c1->id)->where('transaction_type', 'adjustment')->count());
        $fresh = $line->fresh();
        $this->assertNotNull($fresh->adjusted_at);
        $this->assertSame($this->nurse->id, (int) $fresh->adjusted_by);
        $audit = ActivityLog::where('action', 'stock_count')->latest('id')->firstOrFail();
        $this->assertSame($audit->id, (int) $fresh->adjusted_audit_id);
        $this->assertSame($line->id, (int) $audit->properties['count_line_id']);
        $this->assertSame($this->sheet->label(), $audit->properties['count_sheet']);

        $this->get(route('staff.inventory-counts.show', $this->sheet))->assertSee('Corrected 2026-07-15')->assertSee('Correct stock');
        $this->assertSame(1, substr_count($this->get(route('staff.inventory-counts.show', $this->sheet))->getContent(), 'Correct stock'), 'only A is left to correct');
    }

    public function test_with_two_batches_the_batch_counts_must_add_up_to_what_the_sheet_counted(): void
    {
        $line = $this->line($this->a);

        foreach ([[$this->a1->id => 50], [$this->a1->id => 40, $this->a2->id => 35], [$this->a1->id => 45, $this->a2->id => 40]] as $wrong) {
            $this->preview($this->a, $wrong, $line->id)->assertSessionHasErrors();
            $this->assertSame(85, $this->shelf($this->a));
        }

        // batch 1 counted 45, batch 2 left as it is (35): 45 + 35 = 80
        $preview = $this->preview($this->a, [$this->a1->id => 45], $line->id)->assertOk()->viewData('preview');
        $this->confirm($preview)->assertOk();

        $this->assertSame([45, 35], [$this->a1->fresh()->quantity, $this->a2->fresh()->quantity]);
        $this->assertNotNull($line->fresh()->adjusted_at);
    }

    public function test_a_line_is_corrected_once_and_a_confirmation_cannot_be_replayed(): void
    {
        $line = $this->line($this->c);
        $preview = $this->preview($this->c, [$this->c1->id => 26], $line->id)->viewData('preview');
        $this->confirm($preview)->assertOk();

        $this->confirm($preview)->assertStatus(422);
        $this->preview($this->c, [$this->c1->id => 26], $line->id)->assertSessionHasErrors();
        $this->assertSame(26, $this->shelf($this->c));
        $this->assertSame(1, MedicineTransaction::where('batch_id', $this->c1->id)->where('transaction_type', 'adjustment')->count());
    }

    public function test_a_line_that_cannot_be_corrected_is_refused_and_nothing_moves(): void
    {
        $draft = app(InventoryCountService::class)->create(['year' => 2026, 'quarter' => null, 'scope' => 'all', 'counted_on' => '2026-07-15'], $this->nurse);
        app(InventoryCountService::class)->saveCounts($draft, [$this->c->id => ['counted' => '20', 'reason' => 'lost']], $this->nurse);
        $draftLine = $draft->lines()->where('medicine_id', $this->c->id)->firstOrFail();

        $cases = [
            'a draft sheet' => [$this->c, [$this->c1->id => 20], $draftLine->id, [], 'Finalize the sheet first'],
            'a line without a difference' => [$this->b, [MedicineBatch::where('medicine_id', $this->b->id)->value('id') => 40], $this->line($this->b)->id, [], 'no difference'],
            "another medicine's line" => [$this->c, [$this->c1->id => 26], $this->line($this->a)->id, [], 'not for this medicine'],
            'a disposal instead of a count' => [$this->c, [$this->c1->id => 4], $this->line($this->c)->id, ['mode' => 'damaged'], 'not a disposal'],
            'a line that does not exist' => [$this->c, [$this->c1->id => 26], 999999, [], 'not for this medicine'],
        ];
        foreach ($cases as $why => [$medicine, $entries, $lineId, $over, $says]) {
            $this->preview($medicine, $entries, $lineId, $over)->assertSessionHasErrors();
            $this->assertStringContainsString($says, session('errors')->first(), $why);
            $this->assertSame(30, $this->shelf($this->c), $why);
        }
        $this->assertSame(0, InventoryCountLine::whereNotNull('adjusted_at')->count());

        // a line that was corrected already (the shelf still matches, so only this guard can refuse it)
        $this->line($this->c)->update(['adjusted_at' => now()]);
        $this->preview($this->c, [$this->c1->id => 26], $this->line($this->c)->id)->assertSessionHasErrors();
        $this->assertStringContainsString('already corrected', session('errors')->first());
        $this->assertSame(30, $this->shelf($this->c));
        $this->line($this->c)->update(['adjusted_at' => null]);

        // the shelf moved on after the count
        app(MedicineInventoryService::class)->deductStockFefo($this->c->id, 1, $this->nurse->id, null, 'later dispense');
        $this->preview($this->c, [$this->c1->id => 26], $this->line($this->c)->id)->assertSessionHasErrors();
        $this->assertStringContainsString('The shelf changed since this count', session('errors')->first());
        $this->assertSame(29, $this->shelf($this->c));
    }
}
