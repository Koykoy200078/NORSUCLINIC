<?php

namespace Tests\Feature\Regression;

use App\Models\DispenseRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Re-audit 2026-11-13 (DRY pass): the edit page of a dispense record printed an empty heading, while the add page
 * shows its title there. The owner chose to show the title on both.
 */
class DispenseRecordEditPageTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    public function test_the_edit_page_shows_its_title_in_the_heading(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();
        $record = DispenseRecord::create([
            'history_number' => 'HIS' . generateUniqueHistoryNumber(),
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->doctor->id,
            'model_type' => DispenseRecord::class,
            'model_id' => (string) $patient->id,
            'bill_date' => now(),
        ]);

        $edit = $this->actingAs($this->makeAdmin())->get(route('dispense-records.edit', $record))->assertOk();
        $this->assertMatchesRegularExpression('/<h1 class="mb-0 me-1">\s*Edit Dispense Record\s*<\/h1>/', $edit->getContent());

        $create = $this->get(route('dispense-records.create'))->assertOk();
        $this->assertMatchesRegularExpression('/<h1 class="mb-0 me-1">\s*Add Dispense Record\s*<\/h1>/', $create->getContent());
    }
}
