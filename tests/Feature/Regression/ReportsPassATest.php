<?php

namespace Tests\Feature\Regression;

use App\Livewire\ReportGeneration;
use App\Models\ActivityLog;
use App\Models\DispenseRecord;
use App\Models\DocumentIssuance;
use App\Models\Medicine;
use App\Models\Role;
use App\Services\Reports\ReportFilters;
use App\Services\Reports\ReportQueries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Phase 5, row 5.10 Report Generation, Pass A: what AccomplishmentReportTest, OtherNamesReportTest, ReportCsvParityTest,
 * ReportListsTest, SearchBehaviourTest and YearlyInventoryReportTest did not reach (the low-stock definition is in
 * LowStockDefinitionTest).
 */
class ReportsPassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function visit(string $date, array $attributes = []): DocumentIssuance
    {
        return DocumentIssuance::create(array_merge([
            'document_type' => 'consultation_form', 'document_creator_id' => $this->makeNurse()->id, 'user_id' => $this->makePatient()->user_id,
            'name' => 'Visitor ' . $date, 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => $date,
            'complaints' => 'Cough', 'consult_mode' => 'physical',
        ], $attributes));
    }

    // ------------------------------------------------------------------ date ranges

    public function test_a_date_range_includes_its_first_and_its_last_day_on_every_list(): void
    {
        $this->visit('2026-03-01');
        $this->visit('2026-03-15');
        $this->visit('2026-03-31');
        $this->visit('2026-04-01');
        foreach (['2026-03-01', '2026-03-31', '2026-04-01'] as $day) {
            ActivityLog::create(['user_id' => null, 'user_name' => 'T', 'user_type' => 'staff', 'action' => 'test', 'description' => 'log ' . $day, 'date' => $day, 'time' => '23:59:59']);
        }

        $march = ReportFilters::fromArray(['date_from' => '2026-03-01', 'date_to' => '2026-03-31']);
        $this->assertSame(
            ['Visitor 2026-03-01', 'Visitor 2026-03-15', 'Visitor 2026-03-31'],
            ReportQueries::visits($march)->pluck('name')->sort()->values()->all(),
            'Patient Visits: both end days are in'
        );
        $this->assertSame(
            ['log 2026-03-01', 'log 2026-03-31'],
            ReportQueries::logs($march)->pluck('description')->sort()->values()->all(),
            'Activity log: both end days are in'
        );

        // a one-day range shows that day
        $day = ReportFilters::fromArray(['date_from' => '2026-04-01', 'date_to' => '2026-04-01']);
        $this->assertSame(['Visitor 2026-04-01'], ReportQueries::visits($day)->pluck('name')->all());
    }

    // ------------------------------------------------------------------ the lists do not query once per row

    public function test_the_report_tabs_do_not_query_once_per_row(): void
    {
        $this->actingAs($this->makeAdmin());
        $queriesOf = function (string $tab) {
            $component = Livewire::withoutLazyLoading()->test(ReportGeneration::class)->set('tab', $tab);
            DB::flushQueryLog();
            DB::enableQueryLog();
            $component->call('$refresh');
            $count = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $count;
        };
        $medicine = $this->makeMedicine('Zz Reportmed', ['minimum_stock_alert' => 5]);
        $this->stockIn($medicine, 500, now()->addYear()->toDateString());

        $add = function (int $i) use ($medicine) {
            $this->visit('2026-03-' . str_pad((string) (($i % 27) + 1), 2, '0', STR_PAD_LEFT));
            ActivityLog::create(['user_id' => $this->makeNurse()->id, 'user_name' => 'T' . $i, 'user_type' => 'staff', 'action' => 'test', 'description' => 'log ' . $i, 'date' => '2026-03-01', 'time' => '10:00:00']);
            app(\App\Services\MedicineInventoryService::class)->deductStockFefo($medicine->id, 1, null, DispenseRecord::create([
                'history_number' => 'HIS' . $i . random_int(1000, 9999), 'patient_id' => $this->makePatient()->id, 'model_type' => DispenseRecord::class, 'bill_date' => now(),
            ]), 'test', \App\Models\MedicineTransaction::TYPE_DISPENSE, '500mg');
        };

        foreach (range(1, 3) as $i) {
            $add($i);
        }
        $few = [];
        foreach (['logs', 'visits', 'dispensing', 'inventory'] as $tab) {
            $few[$tab] = $queriesOf($tab);
        }
        foreach (range(4, 12) as $i) {
            $add($i);
        }
        foreach (['logs', 'visits', 'dispensing', 'inventory'] as $tab) {
            $this->assertSame($few[$tab], $queriesOf($tab), "{$tab}: queries must not grow with the number of rows");
        }
    }

    // ------------------------------------------------------------------ who may download what

    public function test_every_download_follows_the_tab_permission_and_a_stranger_gets_none(): void
    {
        $this->visit('2026-03-10');
        $nurse = $this->makeNurse();
        $doctor = $this->makeDoctor();

        // Reports and Notifications are open to every signed-in staff account and doctor (the plan's rule), even with no other permission
        foreach (['staff' => $nurse, 'doctor' => $doctor] as $role => $user) {
            Role::findByName($role)->syncPermissions([]);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([$nurse, $doctor] as $user) {
            $this->actingAs($user);
            foreach (['logs', 'visits', 'inventory', 'dispensing', 'appointments'] as $tab) {
                $route = $user->hasRole('doctor') ? 'doctors.activity-logs.export' : 'staff.activity-logs.export';
                $this->get(route($route, ['tab' => $tab]))->assertOk();
            }
        }

        // a guest and a patient are sent to sign in or refused
        auth()->logout();
        $this->get(route('activity-logs.export', ['tab' => 'visits']))->assertRedirect();
        $this->get(route('activity-logs.accomplishment', ['format' => 'csv']))->assertRedirect();
    }
}
