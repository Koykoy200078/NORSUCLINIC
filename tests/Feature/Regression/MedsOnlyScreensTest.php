<?php

namespace Tests\Feature\Regression;

use App\Livewire\MedicineDispenseTable;
use App\Livewire\MedsOnlyTable;
use App\Livewire\ReportGeneration;
use App\Livewire\StockOutTable;
use App\Models\College;
use App\Models\Course;
use App\Models\Generic;
use App\Models\Medicine;
use App\Models\Role;
use App\Models\YearLevel;
use App\Services\Reports\ReportFilters;
use App\Services\Reports\ReportQueries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * A Meds Only record of a person who is not registered (no patient row) must read right everywhere the dispensing screens and
 * reports show a person: its own list, Dispense History, Stock Out and the Dispensing Reports tab.
 */
class MedsOnlyScreensTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private Medicine $medicine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $generic = Generic::create(['name' => 'Acetaminophen']);
        $this->medicine = $this->makeMedicine('Biogesic', ['dosage' => '500mg', 'generic_id' => $generic->id, 'brand_name' => 'Brandix']);
        $this->stockIn($this->medicine, 30, now()->addYear()->toDateString(), '500mg', 'LOT-1');
        $this->nurse = $this->makeNurse(['first_name' => 'Nora', 'last_name' => 'Nurse']);

        $this->actingAs($this->nurse)->post(route('staff.meds-only.store'), [
            'bill_date' => '2026-03-05', 'patient_name' => 'Juan Typed',
            'college_id' => College::create(['college_name' => 'College of Arts'])->id,
            'course_id' => Course::create(['course_name' => 'BS Biology'])->id,
            'year_level_id' => YearLevel::create(['year_level_name' => '2nd Year'])->id,
            'medicines' => [['medicine_id' => $this->medicine->id, 'dosage' => '500mg', 'quantity' => 6]],
        ])->assertRedirect();
    }

    private \App\Models\User $nurse;

    public function test_the_list_shows_date_name_course_year_college_and_the_drugs_and_finds_them_by_words(): void
    {
        $html = Livewire::withoutLazyLoading()->test(MedsOnlyTable::class)->html();
        foreach (['Mar 05, 2026', 'Juan Typed', 'BS Biology', '2nd Year', 'College of Arts', 'Biogesic 500mg x 6', 'Nora Nurse'] as $shown) {
            $this->assertStringContainsString($shown, $html, "the list shows: $shown");
        }

        foreach (['juan', 'biology', 'arts', 'biogesic', 'acetaminophen', 'brandix', 'juan biogesic'] as $word) {
            $this->assertStringContainsString('Juan Typed', Livewire::withoutLazyLoading()->test(MedsOnlyTable::class)->set('search', $word)->html(), "searching: $word");
        }
        foreach (['someone else', 'juan nothing', 'cetirizine'] as $word) {
            $this->assertStringNotContainsString('Juan Typed', Livewire::withoutLazyLoading()->test(MedsOnlyTable::class)->set('search', $word)->html(), "searching: $word");
        }
    }

    public function test_the_list_never_shows_a_manual_dispense_record_or_a_prescription(): void
    {
        \App\Models\DispenseRecord::create(['history_number' => 'HIS777', 'patient_id' => $this->makePatient(['first_name' => 'Manual', 'last_name' => 'Person'])->id,
            'model_type' => \App\Models\DispenseRecord::class, 'bill_date' => now()]);

        // A legacy record has no stored name, so its row would print blank cells: the records the table queries are what proves it.
        $queried = Livewire::withoutLazyLoading()->test(MedsOnlyTable::class)->instance()->builder()->pluck('medicine_bills.id')->all();
        $this->assertSame([(int) \App\Models\DispenseRecord::medsOnly()->value('id')], array_map('intval', $queried));
        Livewire::withoutLazyLoading()->test(MedsOnlyTable::class)->assertSee('Juan Typed')->assertDontSee('Manual Person');
    }

    /** The shared last sort key (`SortsByUniqueKey`) makes records of one date list newest first and stay put between pages. */
    public function test_records_with_the_same_date_list_the_newest_first(): void
    {
        foreach (['Alpha Same Day', 'Beta Same Day'] as $name) {
            $this->actingAs($this->nurse)->post(route('staff.meds-only.store'), [
                'bill_date' => '2026-03-05', 'patient_name' => $name,
                'medicines' => [['medicine_id' => $this->medicine->id, 'dosage' => '500mg', 'quantity' => 1]],
            ])->assertRedirect();
        }
        // one identical date, time and year for all three, so only the last sort key can decide the order (sorting by the
        // date alone would hand them back newest first anyway, through the date index read backwards)
        \App\Models\DispenseRecord::medsOnly()->update(['bill_date' => '2026-03-05 10:00:00', 'year_level' => '1st Year']);

        foreach ([[], ['year_level']] as $sortBy) {
            $table = Livewire::withoutLazyLoading()->test(MedsOnlyTable::class);
            foreach ($sortBy as $column) {
                $table->call('sortBy', $column);
            }
            $html = $table->html();

            $this->assertLessThan(strpos($html, 'Alpha Same Day'), strpos($html, 'Beta Same Day'), 'the newest of the day first, sorted by ' . ($sortBy[0] ?? 'the date'));
            $this->assertLessThan(strpos($html, 'Juan Typed'), strpos($html, 'Alpha Same Day'));
        }
    }

    public function test_a_role_without_the_medicines_permission_gets_no_list_even_through_livewire(): void
    {
        Role::findByName('staff')->revokePermissionTo('manage_medicines');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->nurse->fresh());
        Livewire::withoutLazyLoading()->test(MedsOnlyTable::class)->assertForbidden();
    }

    public function test_dispense_history_names_the_typed_person_marks_the_source_and_has_no_dead_patient_link(): void
    {
        $this->actingAs($this->makeAdmin());
        $html = Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class)->html();

        $this->assertStringContainsString('Juan Typed', $html);
        $this->assertStringContainsString('Meds Only', $html);
        $this->assertStringContainsString('Nora Nurse', $html);
        $this->assertStringNotContainsString('/patients/', $html, 'a person who is not registered has no patient page to link to');

        // the Source filter offers it
        $this->assertContains('Meds Only', \App\Models\DispenseHistoryEntry::SOURCES);
        Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class)->set('sourceFilter', 'Meds Only')->assertSee('Juan Typed');
        Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class)->set('sourceFilter', 'Dispense Record')->assertDontSee('Juan Typed');
    }

    public function test_dispense_history_marks_a_meds_only_row_with_its_own_badge_and_adds_new_records_in_the_new_module(): void
    {
        // staff and admin add through Meds Only (the older pop-up needed a registered patient and had no college / course / year)
        foreach ([[$this->nurse, 'staff.meds-only.create'], [$this->makeAdmin(), 'meds-only.create']] as [$user, $route]) {
            $this->actingAs($user);
            $html = Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class)->html();
            $this->assertStringContainsString('href="' . route($route) . '"', $html);
            $this->assertStringContainsString('Add Meds Only record', $html);
            $this->assertStringNotContainsString('add_dispense_modal', $html);
            $this->assertStringContainsString('badge bg-light-info">Meds Only</span>', $html);
        }

        // a doctor cannot use the new form, so keeps the older pop-up
        $this->actingAs($this->makeDoctor());
        $html = Livewire::withoutLazyLoading()->test(MedicineDispenseTable::class)->html();
        $this->assertStringContainsString('add_dispense_modal', $html);
        $this->assertStringNotContainsString('meds-only/create', $html);
    }

    public function test_stock_out_shows_it_as_meds_only_with_the_person_and_the_nurse(): void
    {
        $this->actingAs($this->makeAdmin());

        Livewire::withoutLazyLoading()->test(StockOutTable::class)->assertSee('Biogesic')->assertSee('Meds Only')->assertSee('Juan Typed')->assertSee('Nora Nurse');
    }

    public function test_the_dispensing_report_names_the_record_and_the_person_and_finds_it_by_the_words_of_the_record(): void
    {
        $rows = ReportQueries::dispensing(ReportFilters::fromArray([]))->get();
        $this->assertCount(1, $rows);
        $this->assertSame('Meds only #' . \App\Models\DispenseRecord::medsOnly()->value('id'), ReportQueries::referenceLabel($rows->first()));
        $this->assertSame('Juan Typed', ReportQueries::referencePatientName($rows->first()));

        foreach (['meds only', 'juan', 'acetaminophen', 'brandix', 'nora'] as $word) {
            $this->assertCount(1, ReportQueries::dispensing(ReportFilters::fromArray(['search' => $word]))->get(), $word);
        }
        $this->assertCount(0, ReportQueries::dispensing(ReportFilters::fromArray(['search' => 'prescription']))->get());

        $this->actingAs($this->makeAdmin());
        Livewire::withoutLazyLoading()->test(ReportGeneration::class)->set('tab', 'dispensing')->assertSee('Meds only #')->assertSee('Juan Typed');
    }

    public function test_the_menu_offers_the_module_to_the_roles_that_may_use_it(): void
    {
        $this->actingAs($this->nurse)->get(route('staff.meds-only.index'))->assertOk()->assertSee('Meds Only')->assertSee('href="' . route('staff.meds-only.index') . '"', false);
        $this->actingAs($this->makeAdmin())->get(route('admin.dashboard'))->assertOk()->assertSee('href="' . route('meds-only.index') . '"', false);

        Role::findByName('staff')->revokePermissionTo('manage_medicines');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->nurse->fresh())->get(route('staff.dashboard'))->assertOk()->assertDontSee('href="' . route('staff.meds-only.index') . '"', false);
    }
}
