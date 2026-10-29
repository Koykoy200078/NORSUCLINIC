<?php

namespace Tests\Feature\Regression;

use App\Models\Category;
use App\Models\DispenseRecord;
use App\Models\DocumentIssuance;
use App\Models\Generic;
use App\Models\MedicineTransaction;
use App\Services\MedicineInventoryService;
use App\Services\Reports\ReportFilters;
use App\Services\Reports\ReportQueries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Report Generation > Dispensing Reports: the search keyword finds a medicine by its name, its generic, its brand, its
 * category, its strength and the other words that describe it, not only by name / batch / patient (owner request 2026-10-29).
 * Every searchable field gets its own unique token, so a field that stops being searched fails exactly one case.
 */
class DispensingReportSearchTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();

        $generic = Generic::create(['name' => 'Genrel']);
        $category = Category::create(['name' => 'Catrel', 'is_active' => 1]);
        $target = $this->makeMedicine('Biogesic', [
            'generic_id' => $generic->id, 'generic_name' => 'Paragen', 'brand_name' => 'Brandix',
            'category_id' => $category->id, 'category' => 'Catplain', 'category_name' => 'Catcol',
            'dosage' => '650mg', 'salt_composition' => 'Saltix', 'sku' => 'SKU-777',
        ]);
        $other = $this->makeMedicine('Cetirizine', ['dosage' => '10mg']);

        $this->stockIn($target, 50, now()->addYear()->toDateString(), '500mg', 'LOT-ZZ9');
        $this->stockIn($other, 50, now()->addYear()->toDateString(), '10mg', 'LOT-OTHER');

        $zeta = $this->makePatient(['first_name' => 'Zeta']);
        $omega = $this->makeNurse(['first_name' => 'Omega']);
        $record = DispenseRecord::create(['history_number' => 'HIS1', 'patient_id' => $zeta->id, 'model_type' => DispenseRecord::class, 'bill_date' => now()]);
        app(MedicineInventoryService::class)->deductStockFefo($target->id, 2, $omega->id, $record, 'test', MedicineTransaction::TYPE_DISPENSE, '500mg');

        $visit = DocumentIssuance::create(['user_id' => $this->makePatient(['first_name' => 'Kappa'])->user_id, 'document_creator_id' => $this->makeNurse(['first_name' => 'Sigma'])->id,
            'name' => 'Kappa Visit', 'age' => 22, 'gender' => 'Male', 'address' => 'Synthetic', 'requested_at' => today(), 'document_type' => 'consultation_form']);
        app(MedicineInventoryService::class)->deductStockFefo($other->id, 1, $visit->document_creator_id, $visit, 'test', MedicineTransaction::TYPE_DISPENSE, '10mg');
    }

    /** @return array<int, string> the medicines of the rows the search keeps */
    private function found(string $search): array
    {
        return ReportQueries::dispensing(ReportFilters::fromArray(['search' => $search]))
            ->get()->map(fn ($row) => $row->batch->medicine->name)->sort()->values()->all();
    }

    public function test_the_medicine_is_found_by_each_word_that_describes_it(): void
    {
        $fields = [
            'name' => 'Biogesic',
            'generic name column' => 'Paragen',
            'generic of the medicine (its list)' => 'Genrel',
            'brand' => 'Brandix',
            'category of the medicine (its list)' => 'Catrel',
            'category column' => 'Catplain',
            'category name column' => 'Catcol',
            "the medicine's strength" => '650mg',
            "the batch's strength" => '500mg',
            'salt composition' => 'Saltix',
            'SKU' => 'SKU-777',
            'batch number' => 'LOT-ZZ9',
        ];

        foreach ($fields as $label => $word) {
            $this->assertSame(['Biogesic'], $this->found($word), "searching the $label ($word)");
        }
    }

    public function test_the_person_and_the_kind_of_record_are_still_searchable(): void
    {
        $this->assertSame(['Biogesic'], $this->found('Zeta'), 'the patient of a dispense record');
        $this->assertSame(['Biogesic'], $this->found('Omega'), 'who handed it out');
        $this->assertSame(['Cetirizine'], $this->found('Kappa'), 'the patient of a consultation');
        $this->assertSame(['Cetirizine'], $this->found('Sigma'), 'the nurse of a consultation');
        $this->assertSame(['Cetirizine'], $this->found('consultation'), 'the kind of record');
        $this->assertSame(['Biogesic'], $this->found('dispense record'), 'the kind of record');
    }

    public function test_every_word_must_match_something_and_case_does_not_matter(): void
    {
        $this->assertSame(['Biogesic'], $this->found('BIOGESIC zeta'), 'a name and a patient together');
        $this->assertSame(['Biogesic'], $this->found('brandix 500MG lot-zz9'), 'three fields together');
        $this->assertSame([], $this->found('Biogesic Kappa'), 'a medicine and the patient of ANOTHER row');
        $this->assertSame([], $this->found('Biogesic Cetirizine'), 'two different medicines never match one row');
        $this->assertSame([], $this->found('nothing-like-this'));
        $this->assertSame(['Biogesic', 'Cetirizine'], $this->found(''), 'no keyword: every row');
    }

    public function test_the_screen_tells_what_can_be_searched(): void
    {
        $this->actingAs($this->makeAdmin());
        \Livewire\Livewire::withoutLazyLoading()->test(\App\Livewire\ReportGeneration::class)->set('tab', 'dispensing')
            ->assertSeeHtml('placeholder="Medicine, generic, brand, category, batch, patient...')
            ->set('search', 'Paragen')
            ->assertSee('Biogesic')
            ->assertDontSee('Cetirizine');
    }
}
