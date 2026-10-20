<?php

namespace Tests\Feature\Regression;

use App\Models\ConsultationMedicine;
use App\Models\DocumentIssuance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

                $this->assertSame(parse_url(getRouteByRole('medicines.by.category'), PHP_URL_PATH), $config['url']);
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
}
