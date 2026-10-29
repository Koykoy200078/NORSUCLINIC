<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\InventoryCount;
use App\Models\InventoryCountLine;
use App\Models\Medicine;
use App\Models\Role;
use App\Models\User;
use App\Services\MedicineInventoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * The count-sheet pages (Phase 11 D): the list and the form that makes a sheet, the sheet itself (type the counts, refresh,
 * finalize, delete), what a finalized sheet shows, and who sees what: staff and admin work with sheets, a doctor only reads them.
 * The ledger is the one of InventoryCountServiceTest: medicine A ends March at 90 and is at 85 today (2026-07-15), B is at 40.
 */
class InventoryCountScreensTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $nurse;

    private User $admin;

    private User $doctor;

    private Medicine $a;

    private Medicine $b;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->admin = $this->makeAdmin();
        $this->nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse']);
        $this->doctor = $this->makeDoctor();
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
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function make(array $over = []): InventoryCount
    {
        $this->actingAs($this->nurse)->post(route('staff.inventory-counts.store'), $over + ['year' => 2026, 'quarter' => 1, 'scope' => 'all', 'counted_on' => '2026-07-15'])
            ->assertSessionHasNoErrors();

        return InventoryCount::latest('id')->firstOrFail();
    }

    private function line(InventoryCount $sheet, Medicine $medicine): InventoryCountLine
    {
        return $sheet->lines()->where('medicine_id', $medicine->id)->firstOrFail();
    }

    // ------------------------------------------------------------------ the list and the form

    public function test_the_list_shows_the_sheets_and_staff_and_admin_get_the_form_that_makes_one(): void
    {
        $sheet = $this->make(['quarter' => 2]);

        foreach ([[$this->nurse, 'staff.inventory-counts.index'], [$this->admin, 'inventory-counts.index']] as [$user, $route]) {
            $this->actingAs($user)->get(route($route))->assertOk()
                ->assertSee('Quarterly count sheets')->assertSee('2026 Q2')->assertSee('Draft')->assertSee('New count sheet')
                ->assertSee('name="quarter"', false)->assertSee('name="scope"', false)->assertSee('name="counted_on"', false);
        }

        $this->actingAs($this->doctor)->get(route('doctors.inventory-counts.index'))->assertOk()->assertSee('2026 Q2')
            ->assertDontSee('New count sheet')->assertDontSee('name="counted_on"', false);
        $this->get(route('doctors.inventory-counts.show', $sheet))->assertOk()->assertSee('Alpha');
    }

    public function test_a_sheet_is_made_from_the_form_and_a_refused_one_makes_nothing_and_says_why(): void
    {
        $this->actingAs($this->nurse);
        $made = $this->post(route('staff.inventory-counts.store'), ['year' => 2026, 'quarter' => 1, 'scope' => 'all', 'counted_on' => '2026-07-15', 'notes' => 'End of March']);
        $first = InventoryCount::firstOrFail();
        $made->assertRedirect(route('staff.inventory-counts.show', $first));
        $this->assertSame('End of March', $first->notes);

        $this->post(route('staff.inventory-counts.store'), ['year' => 2026, 'quarter' => 4, 'scope' => 'all', 'counted_on' => '2026-07-15'])
            ->assertSessionHasErrors();
        $this->post(route('staff.inventory-counts.store'), ['year' => 2026, 'quarter' => 1, 'scope' => 'selected', 'counted_on' => '2026-07-15'])
            ->assertSessionHasErrors();
        $this->assertSame(1, InventoryCount::count());

        $this->post(route('staff.inventory-counts.store'), ['year' => 2026, 'quarter' => '', 'scope' => 'selected', 'medicine_ids' => [$this->b->id], 'counted_on' => '2026-07-14'])->assertRedirect();
        $this->assertSame(['Beta'], InventoryCount::latest('id')->first()->lines()->pluck('medicine_name')->all());
    }

    // ------------------------------------------------------------------ the sheet

    public function test_a_draft_sheet_shows_the_system_amounts_and_the_boxes_to_type_the_counts(): void
    {
        $sheet = $this->make(['quarter' => 1, 'notes' => 'End of March']);

        $this->actingAs($this->nurse)->get(route('staff.inventory-counts.show', $sheet))->assertOk()
            ->assertSee('Count sheet 2026 Q1')->assertSee('Draft')->assertSee('End of March')
            ->assertSee('Alpha')->assertSee('500mg')->assertSee('tablet')->assertSee('Beta')
            ->assertSee('name="rows[' . $this->a->id . '][counted]"', false)->assertSee('name="rows[' . $this->a->id . '][reason]"', false)->assertSee('name="rows[' . $this->a->id . '][note]"', false)
            ->assertSee('Save counts')->assertSee('Refresh system amounts')->assertSee('Finalize')->assertSee('Delete sheet')
            ->assertDontSee('Correct stock');
    }

    public function test_the_counts_are_typed_saved_and_shown_with_their_difference(): void
    {
        $sheet = $this->make(['quarter' => 1]);

        $this->actingAs($this->nurse)->put(route('staff.inventory-counts.update', $sheet), ['rows' => [
            $this->a->id => ['counted' => '82', 'reason' => 'damaged', 'note' => 'wet packs'],
            $this->b->id => ['counted' => '', 'reason' => '', 'note' => ''],
        ]])->assertRedirect(route('staff.inventory-counts.show', $sheet))->assertSessionHas('success');

        $a = $this->line($sheet, $this->a);
        $this->assertSame([82, -8, 'damaged'], [$a->counted_quantity, $a->difference, $a->reason]);

        $this->get(route('staff.inventory-counts.show', $sheet))->assertSee('value="82"', false)->assertSee('wet packs')->assertSee('-8');
    }

    public function test_a_bad_count_is_refused_with_the_reason_and_the_typed_values_stay_in_the_boxes(): void
    {
        $sheet = $this->make(['quarter' => 1]);

        $this->actingAs($this->nurse)->from(route('staff.inventory-counts.show', $sheet))
            ->put(route('staff.inventory-counts.update', $sheet), ['rows' => [$this->a->id => ['counted' => '-4', 'reason' => '', 'note' => 'kept']]])
            ->assertRedirect(route('staff.inventory-counts.show', $sheet))->assertSessionHasErrors();

        $this->assertNull($this->line($sheet, $this->a)->counted_quantity);
        $this->get(route('staff.inventory-counts.show', $sheet))->assertSee('the count must be a whole number')->assertSee('value="-4"', false);
    }

    public function test_the_filters_show_what_is_not_counted_yet_or_has_a_difference_and_the_search_finds_a_medicine(): void
    {
        $sheet = $this->make(['quarter' => 1]);
        $this->actingAs($this->nurse)->put(route('staff.inventory-counts.update', $sheet), ['rows' => [$this->a->id => ['counted' => '88', 'reason' => 'lost']]]);
        $url = fn (array $query) => route('staff.inventory-counts.show', $sheet) . '?' . http_build_query($query);

        $this->get($url(['filter' => 'uncounted']))->assertSee('Beta')->assertDontSee('name="rows[' . $this->a->id . '][counted]"', false);
        $this->get($url(['filter' => 'difference']))->assertSee('Alpha')->assertDontSee('name="rows[' . $this->b->id . '][counted]"', false);
        $this->get($url(['search' => 'bet']))->assertSee('Beta')->assertDontSee('name="rows[' . $this->a->id . '][counted]"', false);
        $this->get($url(['filter' => 'bogus']))->assertOk()->assertSee('Alpha')->assertSee('Beta');
    }

    public function test_refreshing_and_deleting_a_draft_work_from_the_sheet(): void
    {
        $sheet = $this->make(['quarter' => null, 'counted_on' => '2026-07-14']);
        app(MedicineInventoryService::class)->deductStockFefo($this->a->id, 2, $this->nurse->id, null, 'later dispense');

        $this->actingAs($this->nurse)->post(route('staff.inventory-counts.refresh', $sheet))->assertRedirect(route('staff.inventory-counts.show', $sheet))->assertSessionHas('success');
        $this->assertSame(83, $this->line($sheet, $this->a)->system_quantity);

        $this->delete(route('staff.inventory-counts.destroy', $sheet))->assertRedirect(route('staff.inventory-counts.index'))->assertSessionHas('success');
        $this->assertSame(0, InventoryCount::count());
        $this->assertSame(0, InventoryCountLine::count());
    }

    public function test_finalizing_is_refused_with_the_reason_until_every_difference_has_one_then_the_sheet_is_frozen(): void
    {
        $sheet = $this->make(['quarter' => 1]);
        $this->actingAs($this->nurse);
        $this->put(route('staff.inventory-counts.update', $sheet), ['rows' => [$this->a->id => ['counted' => '88']]]);

        $this->from(route('staff.inventory-counts.show', $sheet))->post(route('staff.inventory-counts.finalize', $sheet))
            ->assertRedirect(route('staff.inventory-counts.show', $sheet))->assertSessionHasErrors();
        $this->assertSame('draft', $sheet->fresh()->status);

        $this->put(route('staff.inventory-counts.update', $sheet), ['rows' => [$this->a->id => ['counted' => '88', 'reason' => 'lost']]]);
        $this->post(route('staff.inventory-counts.finalize', $sheet))->assertRedirect(route('staff.inventory-counts.show', $sheet))->assertSessionHas('success');
        $this->assertSame('finalized', $sheet->fresh()->status);

        $page = $this->get(route('staff.inventory-counts.show', $sheet));
        $page->assertOk()->assertSee('Finalized')->assertDontSee('name="rows[', false)->assertDontSee('Save counts')->assertDontSee('Delete sheet')->assertSee('Lost or missing');

        $this->put(route('staff.inventory-counts.update', $sheet), ['rows' => [$this->a->id => ['counted' => '1']]])->assertSessionHasErrors();
        $this->delete(route('staff.inventory-counts.destroy', $sheet))->assertSessionHasErrors();
        $this->assertSame(88, $this->line($sheet, $this->a)->counted_quantity);
    }

    // ------------------------------------------------------------------ who may

    public function test_a_doctor_reads_a_sheet_and_cannot_write_to_it_and_without_the_permission_nobody_gets_in(): void
    {
        $sheet = $this->make(['quarter' => 1]);

        $this->actingAs($this->doctor)->get(route('doctors.inventory-counts.show', $sheet))->assertOk()
            ->assertDontSee('Save counts')->assertDontSee('name="rows[', false)->assertDontSee('Delete sheet')->assertDontSee('Finalize');
        $this->post('/doctors/inventory-counts', [])->assertStatus(405);
        $this->put('/doctors/inventory-counts/' . $sheet->id, [])->assertStatus(405);
        $this->delete('/doctors/inventory-counts/' . $sheet->id)->assertStatus(405);
        $this->post('/staff/inventory-counts', [])->assertForbidden();

        Role::findByName('staff')->revokePermissionTo('manage_medicines');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->nurse->fresh());
        $this->get(route('staff.inventory-counts.index'))->assertForbidden();
        $this->get(route('staff.inventory-counts.show', $sheet))->assertForbidden();
        $this->put(route('staff.inventory-counts.update', $sheet), ['rows' => []])->assertForbidden();

        auth()->logout();
        $this->get(route('inventory-counts.index'))->assertRedirect();
    }

    public function test_every_change_of_a_sheet_from_the_pages_is_audited(): void
    {
        $sheet = $this->make(['quarter' => 1]);
        $this->actingAs($this->nurse)->put(route('staff.inventory-counts.update', $sheet), ['rows' => [$this->a->id => ['counted' => '90']]]);
        $this->post(route('staff.inventory-counts.refresh', $sheet));
        $this->post(route('staff.inventory-counts.finalize', $sheet));

        $this->assertSame(['inventory_count_created', 'inventory_count_saved', 'inventory_count_refreshed', 'inventory_count_finalized'],
            ActivityLog::where('action', 'like', 'inventory_count_%')->orderBy('id')->pluck('action')->all());
    }

    // ------------------------------------------------------------------ the door in the Inventory page

    public function test_the_inventory_page_offers_the_count_sheets_to_everyone_who_may_open_the_inventory(): void
    {
        $this->actingAs($this->nurse)->get(route('staff.medicines.index'))->assertOk()->assertSee('Quarterly count sheets')
            ->assertSee('href="' . route('staff.inventory-counts.index') . '"', false);
        $this->actingAs($this->admin)->get(route('medicines.index'))->assertOk()->assertSee('href="' . route('inventory-counts.index') . '"', false);
        $this->actingAs($this->doctor)->get(route('doctors.medicines.index'))->assertOk()->assertSee('href="' . route('doctors.inventory-counts.index') . '"', false);
    }
}
