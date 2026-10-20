<?php

namespace Tests\Feature\Regression;

use App\Models\Category;
use App\Models\ConsultationMedicine;
use App\Models\DocumentIssuance;
use App\Models\Generic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

class ConsultationMedicinePickerTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function document(User $creator, string $type = 'consultation_form'): DocumentIssuance
    {
        return DocumentIssuance::create([
            'document_type' => $type, 'document_creator_id' => $creator->id,
            'user_id' => $this->makePatient()->user_id, 'name' => 'Picker Patient', 'age' => 23,
            'gender' => 'Male', 'address' => 'Test Address', 'requested_at' => now()->toDateString(),
        ]);
    }

    private function config(string $html): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $nodes = (new \DOMXPath($dom))->query('//script[@id="consultation_medicine_picker_config"]');
        $this->assertCount(1, $nodes, 'The form configures one shared medicine picker.');

        return json_decode($nodes->item(0)->textContent, true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_create_and_edit_keep_the_panel_endpoint_and_plan_permission(): void
    {
        foreach ([$this->makeAdmin(), $this->makeNurse(), $this->makeDoctor()] as $actor) {
            $document = $this->document($actor);
            $this->actingAs($actor);
            foreach ([false, true] as $editing) {
                $url = $editing ? getRouteByRole('document-issuances.edit', [$document])
                    : getRouteByRole('document-issuances.create', ['document_type' => 'consultation_form']);
                $config = $this->config($this->get($url)->assertOk()->getContent());

                $this->assertSame(parse_url(getRouteByRole('medicines.by.category'), PHP_URL_PATH).'?include_unavailable=1', $config['url']);
                $this->assertSame($editing, $config['editing']);
                $this->assertSame($actor->hasRole('doctor'), $config['canManagePlanMedicines']);
                $this->assertSame([], $config['existingMedicines']);
            }
        }
    }

    public function test_saved_out_of_stock_rows_keep_ids_sections_and_safely_encoded_text(): void
    {
        $doctor = $this->makeDoctor();
        $document = $this->document($doctor);
        $medicine = $this->makeMedicine('Empty </script><img src=x onerror=alert(1)> café');
        $row = ConsultationMedicine::create([
            'request_document_id' => $document->id, 'medicine_id' => $medicine->id,
            'dosage' => '500mg', 'quantity' => 4, 'used_for' => 'nursing', 'dosage_instructions' => 'Take "one" <tag>',
        ]);
        $html = $this->actingAs($doctor)->get(route('doctors.document-issuances.edit', $document))->assertOk()->getContent();
        $config = $this->config($html);

        $this->assertSame($row->id, $config['existingMedicines'][0]['id']);
        $this->assertSame($medicine->id, $config['existingMedicines'][0]['medicine_id']);
        $this->assertSame('nursing', $config['existingMedicines'][0]['used_for']);
        $this->assertSame(4, $config['existingMedicines'][0]['quantity']);
        $this->assertSame($medicine->name, $config['existingMedicines'][0]['medicine']['name']);
        $this->assertSame('Take "one" <tag>', $config['existingMedicines'][0]['dosage_instructions']);
        $this->assertStringNotContainsString('</script><img src=x', $html);
    }

    public function test_certificate_and_excuse_slip_forms_do_not_configure_a_medicine_picker(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        foreach (['medical_certificate', 'excuse_slip'] as $type) {
            $document = $this->document($admin, $type);
            $this->get(route('document-issuances.create', ['document_type' => $type]))->assertOk()
                ->assertDontSee('id="consultation_medicine_picker_config"', false);
            $this->get(route('document-issuances.edit', $document))->assertOk()
                ->assertDontSee('id="consultation_medicine_picker_config"', false);
        }
    }

    public function test_search_catalog_exposes_labels_and_current_usable_stock_without_changing_inventory(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 20)->startOfDay());
        $category = Category::create(['name' => 'Pain relief', 'is_active' => 1]);
        $generic = Generic::create(['name' => 'Paracetamol', 'is_active' => 1]);
        $medicine = $this->makeMedicine('Brand display', ['category_id' => $category->id, 'generic_id' => $generic->id,
            'brand_name' => 'Brand label', 'generic_name' => 'Saved generic', 'dosage' => '500mg', 'sku' => 'QA-123']);
        $this->stockIn($medicine, 7, '2026-10-23', '250mg', 'TODAY');
        $this->stockIn($medicine, 5, '2026-11-20', '500mg', 'LATER');
        $this->stockIn($medicine, 20, '2026-10-22', '100mg', 'EXPIRED');
        $this->travelTo(now()->addDays(3)); // The stored total still includes the batch that expired yesterday.
        $before = $medicine->fresh()->getAttributes();
        $response = $this->actingAs($this->makeNurse())->getJson(route('staff.medicines.by.category', ['include_unavailable' => 1]))->assertOk();
        $item = collect($response->json('data'))->pluck('medicines')->flatten(1)->firstWhere('id', $medicine->id);

        $this->assertArrayHasKey('brand_name', $item);
        $this->assertSame('Brand label', $item['brand_name']);
        $this->assertSame('Saved generic', $item['generic_name']);
        $this->assertSame('Paracetamol', $item['generic_label']);
        $this->assertSame('QA-123', $item['sku']);
        $this->assertSame('500mg', $item['strength']);
        $this->assertSame(12, $item['available_quantity']);
        $this->assertSame('2026-10-23', $item['nearest_expiry']);
        $this->assertFalse($item['disabled']);
        $this->assertEqualsCanonicalizing(['250mg', '500mg'], array_column($item['dosages'], 'dosage'));
        $this->assertSame($before, $medicine->fresh()->getAttributes(), 'Reading the catalog must not refresh stored stock.');
    }

    public function test_search_catalog_keeps_unavailable_choices_and_uncategorized_legacy_stock_visible(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 20)->startOfDay());
        $category = Category::create(['name' => 'Unavailable', 'is_active' => 1]);
        $empty = $this->makeMedicine('Empty', ['category_id' => $category->id]);
        $expired = $this->makeMedicine('Expired', ['category_id' => $category->id]);
        $this->stockIn($expired, 9, '2026-10-21');
        $legacy = $this->makeMedicine('Legacy', ['available_quantity' => 4, 'dosage' => '20mg']);
        $this->travelTo(now()->addDays(2));
        $items = collect($this->actingAs($this->makeDoctor())->getJson(route('doctors.medicines.by.category', ['include_unavailable' => 1]))
            ->assertOk()->json('data'))->pluck('medicines')->flatten(1)->keyBy('id');

        foreach ([$empty, $expired] as $medicine) {
            $this->assertTrue($items->has($medicine->id), 'Unavailable medicines must remain visible in the search catalog.');
            $this->assertTrue($items[$medicine->id]['disabled']);
            $this->assertSame(0, $items[$medicine->id]['available_quantity']);
            $this->assertNull($items[$medicine->id]['nearest_expiry']);
            $this->assertSame([], $items[$medicine->id]['dosages']);
        }
        $this->assertSame('expired', $items[$expired->id]['stock_status']);
        $this->assertSame('out_of_stock', $items[$empty->id]['stock_status']);
        $this->assertSame(4, $items[$legacy->id]['available_quantity']);
        $this->assertFalse($items[$legacy->id]['disabled']);
        $this->assertNull($items[$legacy->id]['nearest_expiry']);
        $this->assertSame([['dosage' => '20mg', 'available_quantity' => 4]], $items[$legacy->id]['dosages']);
        // Existing callers still receive only categorized, dispensable choices.
        $this->assertSame([], $this->getJson(route('doctors.medicines.by.category'))->assertOk()->json('data'));
    }

    public function test_search_catalog_query_count_does_not_grow_with_medicines(): void
    {
        $this->actingAs($this->makeAdmin());
        $category = Category::create(['name' => 'Catalog', 'is_active' => 1]);
        $generic = Generic::create(['name' => 'Generic', 'is_active' => 1]);
        $counts = [];
        foreach ([2, 10] as $size) {
            foreach (range(1, $size) as $index) {
                $this->makeMedicine('Catalog '.$size.' '.$index, ['category_id' => $category->id, 'generic_id' => $generic->id]);
            }
            $url = route('medicines.by.category', ['include_unavailable' => 1]);
            $this->getJson($url)->assertOk();
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->getJson($url)->assertOk();
            $counts[] = count(DB::getQueryLog());
            DB::disableQueryLog();
        }
        $this->assertSame($counts[0], $counts[1], 'Search metadata must be eager loaded.');
    }
}
