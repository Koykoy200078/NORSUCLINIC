<?php

namespace Tests\Feature\Regression;

use App\Livewire\StockInTable;
use App\Models\Category;
use App\Models\Generic;
use App\Models\Medicine;
use App\Models\StockIn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Phase 5, row 5.6 Inventory, Pass A: what the earlier inventory tests (InitialStockRegisterTest, InventoryExpiryTest,
 * PrescriptionInventoryTest, StaleCachesTest, ListQueryCountTest) did not reach.
 */
class InventoryPassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    /** @return array<string, mixed> */
    private function medicinePayload(array $overrides = []): array
    {
        return array_merge([
            'generic_name' => 'Zzgeneric', 'brand_name' => 'Zzbrand', 'category' => 'Zzcategory', 'dosage' => '500mg', 'uom' => 'tablet',
            'initial_stock_quantity' => 30, 'batch_number' => 'B-100', 'manufacturing_date' => '2026-01-01',
            'expiration_date' => now()->addYear()->toDateString(),
        ], $overrides);
    }

    /** @return array<string, mixed> */
    private function stockInPayload(Medicine $medicine, array $overrides = []): array
    {
        return array_merge([
            'medicine' => [$medicine->id], 'quantity' => [10], 'dosage' => ['500mg'],
            'manufacturing_date' => ['2026-01'], 'expiry_date' => [now()->addYear()->format('Y-m')],
        ], $overrides);
    }

    // ------------------------------------------------------------------ all-or-nothing

    public function test_a_medicine_form_that_is_refused_leaves_no_new_generic_or_category_behind(): void
    {
        $admin = $this->makeAdmin();
        $generics = Generic::count();
        $categories = Category::count();

        // stock typed without an expiry date: the whole form is refused
        $this->actingAs($admin)->from(route('medicines.create'))
            ->post(route('medicines.store'), $this->medicinePayload(['expiration_date' => null]))
            ->assertRedirect(route('medicines.create'));

        $this->assertSame(0, Medicine::count());
        $this->assertSame($generics, Generic::count(), 'a refused form must not leave its generic behind');
        $this->assertSame($categories, Category::count(), 'a refused form must not leave its category behind');

        // the same for an edit
        $medicine = $this->makeMedicine('Editme');
        $this->put(route('medicines.update', $medicine), $this->medicinePayload(['generic_name' => 'Zzother', 'category' => 'Zzothercat', 'expiration_date' => null]))->assertRedirect();
        $this->assertSame($generics, Generic::count());
        $this->assertSame($categories, Category::count());
    }

    // ------------------------------------------------------------------ validation: dates that cannot be real

    /** Every error message of the last refused request, in one string. */
    private function refusal(): string
    {
        $bag = session('errors');

        return $bag ? implode(' | ', $bag->getBag('default')->all()) : '';
    }

    public function test_stock_in_refuses_a_manufacturing_or_expiry_date_that_cannot_be_real(): void
    {
        $admin = $this->makeAdmin();
        $medicine = $this->makeMedicine('Datemed');
        $this->actingAs($admin);

        // each case breaks exactly one rule, and that rule's message must be the one shown
        $cases = [
            'manufactured in the future' => [['manufacturing_date' => [now()->addYears(2)->format('Y-m')], 'expiry_date' => [now()->addYears(5)->format('Y-m')]], 'Manufacturing date cannot be in the future'],
            'manufactured before 1990' => [['manufacturing_date' => ['1980-01'], 'expiry_date' => [now()->addYear()->format('Y-m')]], 'Manufacturing date cannot be before 1990'],
            'expiry after 2100' => [['expiry_date' => ['2306-05']], 'Expiry date cannot be after the year 2100'],
        ];
        foreach ($cases as $label => [$override, $message]) {
            $this->post(route('stock-in.store'), $this->stockInPayload($medicine, $override))->assertSessionHasErrors();
            $this->assertStringContainsString($message, $this->refusal(), $label);
            $this->assertSame(0, StockIn::count(), $label . ' must save nothing');
            session()->flush();
        }

        // an ordinary stock-in (this month's manufacture, next year's expiry) still saves
        $this->post(route('stock-in.store'), $this->stockInPayload($medicine))->assertSessionHasNoErrors();
        $this->assertSame(1, StockIn::count());
    }

    public function test_the_medicine_form_refuses_the_same_impossible_dates_when_adding_and_when_editing(): void
    {
        $this->actingAs($this->makeAdmin());
        $existing = $this->makeMedicine('Datedit');

        $cases = [
            'manufactured in the future' => [['manufacturing_date' => now()->addYears(2)->toDateString(), 'expiration_date' => now()->addYears(5)->toDateString()], 'cannot be in the future'],
            'manufactured before 1990' => [['manufacturing_date' => '1980-01-01'], 'manufacturing date cannot be before 1990'],
            'expiry before 1990 (no manufacturing date given)' => [['manufacturing_date' => null, 'expiration_date' => '1985-05-01'], 'expiration date cannot be before 1990'],
            'expiry after 2100' => [['expiration_date' => '2306-05-01'], 'expiration date cannot be after the year 2100'],
        ];
        foreach ($cases as $label => [$override, $message]) {
            $this->post(route('medicines.store'), $this->medicinePayload($override))->assertSessionHasErrors();
            $this->assertStringContainsString($message, $this->refusal(), 'new: ' . $label);
            session()->flush();

            $this->put(route('medicines.update', $existing), $this->medicinePayload($override))->assertSessionHasErrors();
            $this->assertStringContainsString($message, $this->refusal(), 'edit: ' . $label);
            session()->flush();
        }
        $this->assertSame(1, Medicine::count(), 'nothing was added by a refused form');

        $this->post(route('medicines.store'), $this->medicinePayload())->assertSessionHasNoErrors();
        $this->assertSame(2, Medicine::count());
    }

    // ------------------------------------------------------------------ taxonomy

    public function test_a_category_or_generic_can_keep_its_own_name_but_not_take_another_ones(): void
    {
        $this->actingAs($this->makeAdmin());
        $a = Category::create(['name' => 'Alpha', 'is_active' => 1]);
        Category::create(['name' => 'Beta', 'is_active' => 1]);
        $this->putJson(route('categories.update', $a->id), ['name' => 'Alpha', 'is_active' => 1])->assertOk();
        $this->putJson(route('categories.update', $a->id), ['name' => 'beta', 'is_active' => 1])->assertStatus(422);

        $g = Generic::create(['name' => 'Gamma']);
        Generic::create(['name' => 'Delta']);
        $this->put(route('generics.update', $g->id), ['name' => 'Gamma'])->assertSessionHasNoErrors();
        $this->put(route('generics.update', $g->id), ['name' => 'delta'])->assertSessionHasErrors();
    }

    // ------------------------------------------------------------------ lists and pick-lists

    public function test_the_medicines_by_category_list_does_not_query_twice_per_medicine(): void
    {
        $admin = $this->makeAdmin();
        $category = Category::create(['name' => 'Zzcat', 'is_active' => 1]);
        $add = function (int $i) use ($category) {
            $medicine = $this->makeMedicine('Listmed ' . $i, ['category_id' => $category->id]);
            $this->stockIn($medicine, 5, now()->addMonths(6)->toDateString());
        };
        $queries = function () use ($admin) {
            $this->actingAs($admin)->getJson(route('medicines.by.category'))->assertOk(); // warm the caches
            DB::flushQueryLog();
            DB::enableQueryLog();
            $response = $this->actingAs($admin)->getJson(route('medicines.by.category'))->assertOk();
            $count = count(DB::getQueryLog());
            DB::disableQueryLog();

            return [$count, $response->json('data.0.medicines')];
        };

        foreach (range(1, 2) as $i) {
            $add($i);
        }
        [$few] = $queries();
        foreach (range(3, 10) as $i) {
            $add($i);
        }
        [$many, $medicines] = $queries();

        $this->assertCount(10, $medicines);
        $this->assertSame($few, $many, 'the medicine pick-list must not query per medicine');
    }

    public function test_the_stock_in_list_does_not_query_once_per_row_and_finds_a_stock_in_by_its_medicine(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $add = function (int $i, string $name = null) {
            $medicine = $this->makeMedicine($name ?? 'Stockmed ' . $i);
            $this->post(route('stock-in.store'), $this->stockInPayload($medicine))->assertSessionHasNoErrors();
        };
        $queries = function () {
            $component = Livewire::withoutLazyLoading()->test(StockInTable::class);
            DB::flushQueryLog();
            DB::enableQueryLog();
            $component->call('$refresh');
            $count = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $count;
        };

        foreach (range(1, 3) as $i) {
            $add($i);
        }
        $few = $queries();
        foreach (range(4, 10) as $i) {
            $add($i);
        }
        $this->assertSame($few, $queries(), 'the stock-in list must not query once per row');

        $add(11, 'Zzfindable');
        $html = Livewire::withoutLazyLoading()->test(StockInTable::class)->set('search', 'Zzfindable')->html();
        $this->assertSame(1, substr_count($html, 'purchaseMedicineDelete'), 'searching a medicine name finds the stock-in that holds it');
    }

    public function test_the_stock_in_export_opens_with_awkward_data(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->get(route('stock-in.excel'))->assertRedirect(); // nothing to export yet: told so, not a crash

        $medicine = $this->makeMedicine('<b>Markup</b> & "quotes" Ünïcode 🙂');
        $this->post(route('stock-in.store'), $this->stockInPayload($medicine))->assertSessionHasNoErrors();
        ob_start(); // the export controller closes one output buffer, like a real request does
        $response = $this->get(route('stock-in.excel'))->assertOk();
        $this->assertStringContainsString('spreadsheetml', (string) $response->headers->get('Content-Type'));
    }

    public function test_every_way_of_changing_the_inventory_needs_the_medicines_permission(): void
    {
        $medicine = $this->makeMedicine('Guarded');
        $nurse = $this->makeNurse();
        $doctor = $this->makeDoctor();
        foreach (['staff', 'doctor'] as $role) {
            Role::findByName($role)->syncPermissions(['manage_patients', 'manage_request_documents']);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([['staff.', $nurse], ['doctors.', $doctor]] as [$prefix, $user]) {
            $this->actingAs($user);
            $this->post(route($prefix . 'medicines.store'), $this->medicinePayload())->assertForbidden();
            $this->put(route($prefix . 'medicines.update', $medicine), $this->medicinePayload())->assertForbidden();
            $this->deleteJson(route($prefix . 'medicines.destroy', $medicine))->assertForbidden();
            $this->post(route($prefix . 'stock-in.store'), $this->stockInPayload($medicine))->assertForbidden();
            $this->postJson(route($prefix . 'categories.store'), ['name' => 'Nope'])->assertForbidden();
            $this->postJson(route($prefix . 'generics.store'), ['name' => 'Nope'])->assertForbidden();
            $this->getJson(route($prefix . 'medicines.show.modal', $medicine))->assertForbidden();
        }

        $this->assertSame(1, Medicine::count());
        $this->assertSame(0, StockIn::count());
        $this->assertSame(0, Category::where('name', 'Nope')->count() + Generic::where('name', 'Nope')->count());
    }

    public function test_the_medicines_by_category_list_offers_only_the_strengths_that_have_not_expired(): void
    {
        $category = Category::create(['name' => 'Zzmixed', 'is_active' => 1]);
        $medicine = $this->makeMedicine('Mixedmed', ['category_id' => $category->id]);
        $this->stockIn($medicine, 7, now()->subMonth()->toDateString(), '250mg', 'OLD');
        $this->stockIn($medicine, 5, now()->addMonths(6)->toDateString(), '500mg', 'NEW');

        $medicines = $this->actingAs($this->makeAdmin())->getJson(route('medicines.by.category'))->assertOk()->json('data.0.medicines');

        $this->assertCount(1, $medicines);
        $this->assertSame([['dosage' => '500mg', 'available_quantity' => 5]], $medicines[0]['dosages']);
    }

    public function test_the_medicine_form_saves_when_the_optional_numbers_are_left_blank_as_the_real_form_sends_them(): void
    {
        $this->actingAs($this->makeAdmin());
        // the browser sends every empty box as "": reorder level, alert percentage, SKU and the description
        $blank = ['reorder_level' => '', 'stock_alert_percentage' => '', 'sku' => '', 'description' => '', 'minimum_stock_alert' => ''];

        $this->post(route('medicines.store'), $this->medicinePayload($blank))->assertSessionHasNoErrors()->assertRedirect();
        $medicine = Medicine::where('brand_name', 'Zzbrand')->firstOrFail();
        $this->assertSame(0, (int) $medicine->reorder_level, 'a blank reorder level is 0, not a database error');
        $this->assertSame(30, (int) $medicine->fresh()->available_quantity);

        $this->put(route('medicines.update', $medicine), $this->medicinePayload($blank + ['initial_stock_quantity' => 0]))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(0, (int) $medicine->fresh()->reorder_level);

        $this->put(route('medicines.update', $medicine), $this->medicinePayload(['reorder_level' => '15', 'initial_stock_quantity' => 0]))->assertSessionHasNoErrors();
        $this->assertSame(15, (int) $medicine->fresh()->reorder_level);
    }
}
