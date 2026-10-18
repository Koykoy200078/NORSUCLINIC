<?php

namespace Tests\Feature\Regression;

use App\Models\Category;
use App\Models\Generic;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\StockIn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Owner decisions of 2026-10-18 on the Inventory module (plan row 5.6 follow-up):
 *  - "Unit Cost" is gone: no prices anywhere, nothing reads the value, so the medicine form no longer asks for it;
 *  - doctors are read-only in the inventory (medicines, categories, generics, stock-in): they may look, never change.
 */
class InventoryOwnerDecisionsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function medicinePayload(array $overrides = []): array
    {
        return array_merge([
            'generic_name' => 'Zzgeneric', 'brand_name' => 'Zzbrand', 'category' => 'Zzcategory', 'dosage' => '500mg', 'uom' => 'tablet',
            'initial_stock_quantity' => 30, 'batch_number' => 'B-100', 'manufacturing_date' => '2026-01-01',
            'expiration_date' => now()->addYear()->toDateString(), 'supplier_name' => 'Mercury Drug',
        ], $overrides);
    }

    // ------------------------------------------------------------------ Unit Cost

    public function test_the_medicine_form_has_no_unit_cost_box_and_a_posted_one_is_not_saved(): void
    {
        $admin = $this->makeAdmin();

        foreach ([route('medicines.create'), route('medicines.edit', $this->makeMedicine('Costless'))] as $url) {
            $html = $this->actingAs($admin)->get($url)->assertOk()->getContent();
            $this->assertStringNotContainsString('unit_cost', $html, $url);
            $this->assertStringNotContainsString('Unit Cost', $html, $url);
        }

        $this->post(route('medicines.store'), $this->medicinePayload(['unit_cost' => '2.50']))->assertSessionHasNoErrors();
        $batch = MedicineBatch::whereHas('medicine', fn ($q) => $q->where('brand_name', 'Zzbrand'))->firstOrFail();
        $this->assertTrue($batch->unit_cost === null || (float) $batch->unit_cost === 0.0, 'a posted unit cost must not be saved');
        $this->assertSame('Mercury Drug', $batch->supplier_name, 'the rest of the stock-in is saved as before');
    }

    // ------------------------------------------------------------------ doctors are read-only

    public function test_a_doctor_can_look_at_the_inventory_but_not_change_it(): void
    {
        $doctor = $this->makeDoctor();
        $medicine = $this->makeMedicine('Lookonly');
        $this->stockIn($medicine, 10, now()->addYear()->toDateString());
        $category = Category::create(['name' => 'Cat', 'is_active' => 1]);
        $generic = Generic::create(['name' => 'Gen']);
        $this->actingAs($this->makeAdmin())->post(route('stock-in.store'), [
            'medicine' => [$medicine->id], 'quantity' => [5], 'dosage' => ['500mg'],
            'manufacturing_date' => ['2026-01'], 'expiry_date' => [now()->addYear()->format('Y-m')],
        ])->assertSessionHasNoErrors();
        $stockIn = StockIn::firstOrFail();

        $this->actingAs($doctor);
        foreach ([
            route('doctors.medicine-inventory.index'), route('doctors.stock-in.index'), route('doctors.stock-in.show', $stockIn),
            route('doctors.categories.index'), route('doctors.categories.show', $category), route('doctors.generics.index'), route('doctors.generics.show', $generic),
            route('doctors.medicines.show.modal', $medicine),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        // the pages draw no way of changing anything
        $html = $this->get(route('doctors.medicine-inventory.index'))->assertOk()->getContent();
        foreach (['add_medicine_modal', 'add_stock_in_modal'] as $modal) {
            $this->assertStringNotContainsString($modal, $html, 'a doctor gets no "new" form');
        }
        // the lists draw no "new stock-in" button and no delete
        $list = \Livewire\Livewire::withoutLazyLoading()->test(\App\Livewire\StockInTable::class)->html();
        $this->assertStringContainsString('purchaseMedicineDelete', \Livewire\Livewire::actingAs($this->makeAdmin())->withoutLazyLoading()->test(\App\Livewire\StockInTable::class)->html(), 'the administrator keeps the delete button');
        $this->actingAs($doctor);
        $this->assertStringNotContainsString('purchaseMedicineDelete', $list, 'no delete on the stock-in list for a doctor');
        $this->assertStringNotContainsString('add_stock_in_modal', $list, 'no new stock-in button for a doctor');
        $show = $this->get(route('doctors.stock-in.show', $stockIn))->getContent();
        $this->assertStringNotContainsString('stock-in/' . $stockIn->id . '/edit', $show, 'no Edit button on the stock-in page');
        $this->assertStringNotContainsString('edit-btn', $this->get(route('doctors.generics.show', $generic))->getContent(), 'no Edit button on the generic page');
        $this->assertStringNotContainsString('category-edit-btn', $this->get(route('doctors.categories.show', $category))->getContent(), 'no Edit button on the category page');

        // and the server refuses every change: the routes are not there (404 / 405) or answer 403
        $refused = [403, 404, 405];
        $before = [Medicine::count(), StockIn::count(), Category::count(), Generic::count()];
        foreach ([
            ['post', '/doctors/medicines', $this->medicinePayload()],
            ['get', '/doctors/medicines/create', []],
            ['put', '/doctors/medicines/' . $medicine->id, $this->medicinePayload()],
            ['delete', '/doctors/medicines/' . $medicine->id, []],
            ['post', '/doctors/stock-in', ['medicine' => [$medicine->id], 'quantity' => [1], 'manufacturing_date' => ['2026-01'], 'expiry_date' => ['2030-01']]],
            ['get', '/doctors/stock-in/create', []],
            ['put', '/doctors/stock-in/' . $stockIn->id, []],
            ['delete', '/doctors/stock-in/' . $stockIn->id, []],
            ['post', '/doctors/categories', ['name' => 'New']],
            ['put', '/doctors/categories/' . $category->id, ['name' => 'Renamed']],
            ['delete', '/doctors/categories/' . $category->id, []],
            ['post', '/doctors/categories/' . $category->id . '/active-deactive', []],
            ['post', '/doctors/generics', ['name' => 'New']],
            ['put', '/doctors/generics/' . $generic->id, ['name' => 'Renamed']],
            ['delete', '/doctors/generics/' . $generic->id, []],
            ['post', '/doctors/prescription-medicine', $this->medicinePayload()],
        ] as [$method, $url, $data]) {
            $status = $this->json($method, $url, $data)->getStatusCode();
            $this->assertContains($status, $refused, strtoupper($method) . ' ' . $url . ' answered ' . $status);
        }
        $this->assertSame($before, [Medicine::count(), StockIn::count(), Category::count(), Generic::count()], 'nothing was changed');
        $this->assertSame('Cat', $category->fresh()->name);
        $this->assertTrue((bool) $category->fresh()->is_active);
    }

    public function test_staff_and_the_administrator_keep_full_inventory_access(): void
    {
        foreach ([[$this->makeNurse(), 'staff.'], [$this->makeAdmin(), '']] as [$user, $prefix]) {
            $this->actingAs($user);
            $this->get(route($prefix . 'medicines.create'))->assertOk();
            $this->get(route($prefix . 'stock-in.create'))->assertOk();
            $this->get(route($prefix . 'generics.create'))->assertOk();
        }
    }
}
