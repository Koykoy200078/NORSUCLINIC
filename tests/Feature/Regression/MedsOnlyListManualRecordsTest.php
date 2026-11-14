<?php

namespace Tests\Feature\Regression;

use App\Livewire\MedsOnlyTable;
use App\Models\College;
use App\Models\Course;
use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\User;
use App\Models\YearLevel;
use App\Services\Reports\MedsOnlyReportBuilder;
use App\Services\Reports\ReportFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * The owner's decision of 2026-11-14 (Meds Only / Phase 11 default 5): the manual dispense records (the older "Add Dispense
 * Record" pop-up of Dispense History: a registered patient and the doctor who gave the medicine) show in the Meds Only list
 * too, tagged "Dispense record", with the name, course, year and college from the patient's profile and the doctor as who gave
 * it. They stay managed in Dispense History (View opens them there; no Meds Only edit, delete or PDF), and the Meds Only report
 * and the Accomplishment "Medicine only" line still count Meds Only records only. A prescription's record is not listed.
 */
class MedsOnlyListManualRecordsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private User $nurse;

    private DispenseRecord $manual;

    private DispenseRecord $medsOnly;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse']);
        $medicine = $this->makeMedicine('Biogesic', ['dosage' => '500mg']);
        $this->stockIn($medicine, 50, now()->addYear()->toDateString(), '500mg', 'LOT-1');

        $this->actingAs($this->nurse)->post(route('staff.meds-only.store'), [
            'bill_date' => now()->toDateString(), 'patient_name' => 'Juan Typed',
            'medicines' => [['medicine_id' => $medicine->id, 'dosage' => '500mg', 'quantity' => 2]],
        ])->assertRedirect();
        $this->medsOnly = DispenseRecord::medsOnly()->firstOrFail();

        $college = College::create(['college_name' => 'College of Nursing']);
        $course = Course::create(['course_name' => 'BS Nursing']);
        $year = YearLevel::create(['year_level_name' => '3rd Year']);
        $patient = $this->makePatient(['first_name' => 'Manual', 'last_name' => 'Person', 'college_id' => $college->id, 'course_id' => $course->id, 'year_level_id' => $year->id]);
        $doctor = $this->makeDoctor(['first_name' => 'Dora', 'last_name' => 'Doctor']);
        $this->manual = DispenseRecord::create(['history_number' => 'HIS-MANUAL', 'patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id,
            'model_type' => DispenseRecord::class, 'bill_date' => now()->subDay()]);
        DispenseRecordItem::create(['dispense_id' => $this->manual->id, 'medicine_id' => $medicine->id, 'dosage' => '500mg', 'quantity' => 3]);

        $prescribed = DispenseRecord::create(['history_number' => 'HIS-RX', 'patient_id' => $this->makePatient(['first_name' => 'Rx', 'last_name' => 'Patient'])->id,
            'model_type' => Prescription::class, 'model_id' => 999, 'bill_date' => now()]);
        DispenseRecordItem::create(['dispense_id' => $prescribed->id, 'medicine_id' => $medicine->id, 'dosage' => '500mg', 'quantity' => 1]);
    }

    public function test_the_list_shows_a_manual_dispense_record_tagged_with_the_patients_profile_and_the_doctor(): void
    {
        Livewire::withoutLazyLoading()->test(MedsOnlyTable::class)
            ->assertSee('Juan Typed')
            ->assertSee('Manual Person')
            ->assertSee('Dispense record')
            ->assertSee('BS Nursing')
            ->assertSee('College of Nursing')
            ->assertSee('3rd Year')
            ->assertSee('Dora Doctor')
            ->assertSee('Biogesic 500mg x 3')
            ->assertDontSee('Rx Patient');
    }

    public function test_a_manual_record_opens_in_dispense_history_and_has_no_meds_only_edit_delete_or_pdf(): void
    {
        $html = view('meds_only.columns.action', ['row' => $this->manual])->render();

        $this->assertStringContainsString(route('staff.dispense-records.show', $this->manual), $html);
        foreach (['meds-only.show', 'meds-only.edit', 'meds-only.destroy', 'meds-only.pdf'] as $route) {
            $this->assertStringNotContainsString(route('staff.' . $route, $this->manual), $html, $route);
        }
        $this->get(route('staff.meds-only.show', $this->manual))->assertNotFound();
    }

    public function test_the_search_and_the_sorting_reach_the_manual_records(): void
    {
        Livewire::withoutLazyLoading()->test(MedsOnlyTable::class)->set('search', 'Manual')->assertSee('Manual Person')->assertDontSee('Juan Typed');
        Livewire::withoutLazyLoading()->test(MedsOnlyTable::class)->set('search', 'Nursing')->assertSee('Manual Person')->assertDontSee('Juan Typed');

        $list = Livewire::withoutLazyLoading()->test(MedsOnlyTable::class);
        $list->call('sortBy', 'patient_name');
        $this->assertLessThan(strpos($list->html(), 'Manual Person'), strpos($list->html(), 'Juan Typed'), 'A to Z: Juan before Manual');
        $list->call('sortBy', 'patient_name');
        $this->assertGreaterThan(strpos($list->html(), 'Manual Person'), strpos($list->html(), 'Juan Typed'), 'Z to A: Manual before Juan');

        // A to Z puts a record without a college first (as before); the manual record sorts by its profile's college. Without
        // that, both would have none and the newest (the manual record) would come first.
        $list->call('sortBy', 'college');
        $this->assertLessThan(strpos($list->html(), 'Manual Person'), strpos($list->html(), 'Juan Typed'), 'none, then College of Nursing');
    }

    public function test_the_meds_only_report_still_counts_only_meds_only_records(): void
    {
        $report = app(MedsOnlyReportBuilder::class)->build(ReportFilters::fromArray([]), $this->nurse);

        $this->assertSame(1, $report['records']);
        $this->assertSame(['Juan Typed'], collect($report['rows'])->pluck('name')->all());
    }
}
