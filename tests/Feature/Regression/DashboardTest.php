<?php

namespace Tests\Feature\Regression;

use App\Livewire\AdminDashBoardTable;
use App\Livewire\Dashboard;
use App\Livewire\DoctorDashboardTable;
use App\Livewire\StaffDashboard;
use App\Livewire\StaffDashBoardTable;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use App\Repositories\DashboardRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 5, row 5.1 (Dashboard, Pass A): the figures of the three dashboards must follow the data at once and agree
 * with the module lists they summarise, the "recent registrations" list must not be cut or crash, its Day / Week / Month
 * ranges must be the real ones, and every role must be able to use its own tabs and links.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function registeredAt(string $when, array $userAttributes = []): Patient
    {
        $patient = $this->makePatient($userAttributes);
        $patient->forceFill(['created_at' => $when])->saveQuietly();

        return $patient->fresh();
    }

    /** @return array<int, int> patient ids of the rows the dashboard list shows */
    private function rowIds(array $rows): array
    {
        return array_map(fn ($row) => (int) $row['id'], $rows);
    }

    // ------------------------------------------------------------------ figures

    public function test_the_admin_and_staff_figures_follow_new_patients_and_doctors_at_once(): void
    {
        $admin = $this->makeAdmin();
        $nurse = $this->makeNurse();

        foreach ([[$admin, Dashboard::class], [$nurse, StaffDashboard::class]] as [$user, $component]) {
            $this->actingAs($user);
            $before = Livewire::test($component);
            $this->assertSame(0, (int) $before->get('totalPatientCount'));
            $doctors = (int) $before->get('totalDoctorCount');
            $patients = Patient::count();

            $this->makePatient();
            $this->makeDoctor();

            $after = Livewire::test($component);
            $this->assertSame($patients + 1, (int) $after->get('totalPatientCount'), class_basename($component) . ': total patients');
            $this->assertSame($doctors + 1, (int) $after->get('totalDoctorCount'), class_basename($component) . ': active doctors');
            $this->assertSame($patients + 1, (int) $after->get('totalRegisteredPatientCount'), class_basename($component) . ': registered today');
            Patient::query()->forceDelete();
            User::query()->whereIn('type', [User::PATIENT, User::DOCTOR])->forceDelete();
        }
    }

    public function test_the_figures_count_patient_records_like_the_patients_module_does(): void
    {
        $admin = $this->makeAdmin();
        $this->registeredAt(now()->toDateTimeString());
        $this->registeredAt(now()->toDateTimeString());
        $this->registeredAt(now()->subDay()->toDateTimeString());
        $archived = $this->registeredAt(now()->toDateTimeString());
        $archived->delete(); // archived with its account
        $bare = User::create($this->baseUser('Bare', 'Account', User::PATIENT)); // an account without a patient record
        $this->assertNotNull($bare->id);

        $this->actingAs($admin);
        $dashboard = Livewire::test(Dashboard::class);
        $this->assertSame(Patient::count(), (int) $dashboard->get('totalPatientCount'), 'total = the Patients list');
        $this->assertSame(3, (int) $dashboard->get('totalPatientCount'));
        $this->assertSame(2, (int) $dashboard->get('totalRegisteredPatientCount'), 'new today = patient records created today, archived left out');

        $list = Livewire::withoutLazyLoading()->test(AdminDashBoardTable::class)->get('data')['patients'];
        $this->assertCount(2, $list, 'the list shows exactly the patients the "new today" figure counts');

        $doctor = Livewire::test(DoctorDashboardTable::class);
        $this->assertSame(3, (int) $doctor->get('patientsCount'));
    }

    public function test_active_doctors_leaves_out_deactivated_and_archived_doctors(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->makeDoctor(['email' => 'on@qa.test']);
        $this->makeDoctor(['email' => 'off@qa.test', 'status' => 0]);
        $gone = $this->makeDoctor(['email' => 'gone@qa.test']);
        $gone->delete();

        $this->assertSame(1, (int) Livewire::test(Dashboard::class)->get('totalDoctorCount'));
        $this->assertSame(1, (int) Livewire::test(StaffDashboard::class)->get('totalDoctorCount'));
    }

    public function test_the_doctor_dashboard_counts_the_catalog_and_todays_queue(): void
    {
        $this->actingAs($this->makeDoctor());
        $this->makeMedicine('Alpha');
        $this->makeMedicine('Beta');

        $doctor = Livewire::test(DoctorDashboardTable::class);
        $this->assertSame(Medicine::count(), (int) $doctor->get('medicinesCount'));
        $this->assertSame(2, (int) $doctor->get('medicinesCount'));
        $this->assertSame(0, (int) $doctor->get('patientQueuesCount'));
    }

    // ------------------------------------------------------------------ the list

    public function test_the_list_shows_every_patient_of_the_day_newest_first_and_follows_new_ones_at_once(): void
    {
        $this->actingAs($this->makeAdmin());
        foreach (range(1, 7) as $i) {
            $this->registeredAt(now()->subMinutes(10 - $i)->toDateTimeString(), ['first_name' => 'P' . $i]);
        }

        $rows = Livewire::withoutLazyLoading()->test(AdminDashBoardTable::class)->get('data')['patients'];
        $this->assertCount(7, $rows, 'not cut at five');
        $this->assertStringStartsWith('P7', $rows[0]['user']['full_name'], 'newest first');

        $new = $this->makePatient(['first_name' => 'Late']);
        $rows = Livewire::withoutLazyLoading()->test(AdminDashBoardTable::class)->get('data')['patients'];
        $this->assertCount(8, $rows, 'a patient registered a second ago is in the list');
        $this->assertContains($new->id, $this->rowIds($rows));

        $new->delete();
        $rows = Livewire::withoutLazyLoading()->test(AdminDashBoardTable::class)->get('data')['patients'];
        $this->assertCount(7, $rows, 'an archived patient leaves the list at once');
    }

    public function test_a_patient_whose_account_was_archived_on_its_own_does_not_break_the_dashboard(): void
    {
        $this->actingAs($this->makeAdmin());
        $patient = $this->makePatient();
        $patient->user->delete(); // the account only, the patient record is still active

        $rows = Livewire::withoutLazyLoading()->test(AdminDashBoardTable::class)->get('data')['patients'];
        $this->assertSame([], $rows);

        $this->actingAs($this->makeNurse());
        $rows = Livewire::withoutLazyLoading()->test(StaffDashBoardTable::class)->get('data')['patients'];
        $this->assertSame([], $rows);
    }

    public function test_the_first_view_is_the_day_tab_and_its_rows_sit_in_the_day_pane(): void
    {
        $this->makePatient(['first_name' => 'Todayperson']);

        foreach ([[$this->makeAdmin(), AdminDashBoardTable::class], [$this->makeNurse(), StaffDashBoardTable::class]] as [$user, $component]) {
            $this->actingAs($user);
            $html = Livewire::withoutLazyLoading()->test($component)->html();

            $this->assertMatchesRegularExpression('/class="tab-pane fade show active" id="day"/', $html, class_basename($component) . ': the pane that is shown is the Day pane');
            $this->assertDoesNotMatchRegularExpression('/class="tab-pane fade show active" id="month"/', $html);
            $this->assertMatchesRegularExpression('/id="dailyReport"[^>]*>(?:(?!<\/tbody>).)*Todayperson/s', $html);
            $this->assertDoesNotMatchRegularExpression('/id="monthlyReport"[^>]*>(?:(?!<\/tbody>).)*Todayperson/s', $html);
        }
    }

    // ------------------------------------------------------------------ ranges (Day / Week / Month tabs)

    public function test_the_day_week_and_month_ranges_are_the_real_ones(): void
    {
        Carbon::setTestNow('2026-10-14 12:00:00'); // a Wednesday
        $today = $this->registeredAt('2026-10-14 09:00:00');
        $weekStart = $this->registeredAt('2026-10-12 00:00:00');
        $weekEnd = $this->registeredAt('2026-10-18 23:59:30');
        $lastWeek = $this->registeredAt('2026-10-11 23:59:59');
        $monthStart = $this->registeredAt('2026-10-01 00:00:00');
        $lastMonth = $this->registeredAt('2026-09-30 23:59:59');
        $lastYear = $this->registeredAt('2025-10-20 10:00:00');

        $admin = $this->makeAdmin();
        $ids = function (string $range) use ($admin) {
            $json = $this->actingAs($admin)->getJson(route('patientData.dashboard', [$range => $range]))->assertOk()->json('data.patients.data');

            return collect($json)->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        };

        $this->assertSame([$today->id], $ids('day'));
        $this->assertSame(collect([$today, $weekStart, $weekEnd])->pluck('id')->sort()->values()->all(), $ids('week'), 'Monday 00:00 to Sunday 23:59:59');
        $this->assertSame(collect([$today, $weekStart, $weekEnd, $lastWeek, $monthStart])->pluck('id')->sort()->values()->all(), $ids('month'), 'this month of this year only');
        $this->assertNotContains($lastYear->id, $ids('month'));
        $this->assertNotContains($lastMonth->id, $ids('month'));
    }

    public function test_a_range_is_not_cut_at_five_and_says_how_many_there_are(): void
    {
        $this->registeredAt(now()->subMinute()->toDateTimeString());
        foreach (range(1, 54) as $i) {
            $this->registeredAt(now()->toDateTimeString());
        }

        $response = $this->actingAs($this->makeAdmin())->getJson(route('patientData.dashboard', ['day' => 'day']))->assertOk();
        $this->assertCount(50, $response->json('data.patients.data'), 'the 50 newest');
        $this->assertSame(55, $response->json('data.patients.total'), 'and the real number, so the page can say "50 of 55"');
    }

    public function test_the_registration_time_is_the_clinic_clock_in_the_list_and_in_the_tabs(): void
    {
        Carbon::setTestNow('2026-10-14 12:00:00');
        $patient = $this->registeredAt('2026-10-14 09:05:00');

        $row = $this->actingAs($this->makeAdmin())->getJson(route('patientData.dashboard', ['day' => 'day']))->json('data.patients.data.0');
        $this->assertSame('14 Oct 2026 09:05 AM', $row['registered_label'], 'not 01:05 AM (UTC)');

        $first = Livewire::withoutLazyLoading()->test(AdminDashBoardTable::class)->get('data')['patients'][0];
        $this->assertSame('14 Oct 2026 09:05 AM', $first['registered_label']);
        $this->assertSame($patient->id, (int) $first['id']);
    }

    public function test_an_unknown_range_answers_with_an_empty_list_not_an_error(): void
    {
        $this->makePatient();
        $response = $this->actingAs($this->makeAdmin())->getJson(route('patientData.dashboard', ['year' => 'year']))->assertOk();
        $this->assertSame([], $response->json('data.patients.data'));
        $this->assertSame(0, $response->json('data.patients.total'));
    }

    // ------------------------------------------------------------------ roles: endpoints and links

    public function test_the_staff_tabs_have_their_own_endpoint_and_the_admin_one_stays_admin_only(): void
    {
        $nurse = $this->makeNurse();
        $patient = $this->makePatient(['first_name' => 'Visible']);

        $this->actingAs($nurse);
        $rows = $this->getJson(route('staff.patientData.dashboard', ['day' => 'day']))->assertOk()->json('data.patients.data');
        $this->assertSame([$patient->id], $this->rowIds($rows));
        $this->getJson(route('patientData.dashboard', ['day' => 'day']))->assertForbidden();

        $this->actingAs($this->makeDoctor());
        $this->getJson(route('staff.patientData.dashboard', ['day' => 'day']))->assertForbidden();
        $this->getJson(route('patientData.dashboard', ['day' => 'day']))->assertForbidden();

        $this->actingAs($this->makeAdmin());
        $this->getJson(route('staff.patientData.dashboard', ['day' => 'day']))->assertForbidden();

        auth()->logout();
        $this->getJson(route('staff.patientData.dashboard', ['day' => 'day']))->assertUnauthorized();
        $this->getJson(route('patientData.dashboard', ['day' => 'day']))->assertUnauthorized();
    }

    public function test_every_row_carries_the_link_its_own_role_may_open(): void
    {
        $patient = $this->makePatient();

        $admin = $this->makeAdmin();
        $row = $this->actingAs($admin)->getJson(route('patientData.dashboard', ['day' => 'day']))->json('data.patients.data.0');
        $this->assertSame(route('patients.show', $patient->id), $row['url']);

        $nurse = $this->makeNurse();
        $row = $this->actingAs($nurse)->getJson(route('staff.patientData.dashboard', ['day' => 'day']))->json('data.patients.data.0');
        $this->assertSame(route('staff.patients.show', $patient->id), $row['url']);
        $first = Livewire::withoutLazyLoading()->test(StaffDashBoardTable::class)->get('data')['patients'][0];
        $this->assertSame(route('staff.patients.show', $patient->id), $first['url']);

        // Without Manage Patients the name is shown but there is no link (the page would answer 403).
        Role::findByName('staff')->revokePermissionTo('manage_patients');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $row = $this->actingAs($nurse->fresh())->getJson(route('staff.patientData.dashboard', ['day' => 'day']))->assertOk()->json('data.patients.data.0');
        $this->assertNull($row['url']);
        $this->assertSame($patient->id, (int) $row['id']);
        $first = Livewire::withoutLazyLoading()->test(StaffDashBoardTable::class)->get('data')['patients'][0];
        $this->assertNull($first['url']);
    }

    public function test_the_tabs_script_uses_the_panel_route_and_the_link_the_server_sent(): void
    {
        $js = file_get_contents(base_path('resources/assets/js/dashboard/dashboard.js'));

        $this->assertStringNotContainsString('route("patientData.dashboard")', $js, 'the admin-only route would answer 403 to a staff member');
        $this->assertSame(1, substr_count($js, 'panelRoute("patientData.dashboard")'), 'one loader for day, week and month');
        $this->assertSame(4, substr_count($js, 'loadRecentPatients('), 'defined once and used by the day, week and month tabs');
        $this->assertStringNotContainsString('route("patients.show"', $js, 'the server decides the link (it depends on the role and the permission)');
        $this->assertStringContainsString('route: value.url', $js);
        $this->assertStringContainsString('registered: value.registered_label', $js, 'the clinic-timezone time of the server, not a UTC date parsed in the browser');
        $this->assertStringNotContainsString('moment', $js);
    }

    public function test_the_dashboard_pages_no_longer_load_data_their_views_never_use(): void
    {
        $this->assertFalse(method_exists(DashboardRepository::class, 'getData'));
        $this->assertFalse(method_exists(DashboardRepository::class, 'getStaffData'));
        $this->assertFalse(method_exists(DashboardRepository::class, 'getDoctorData'));

        $this->actingAs($this->makeAdmin())->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($this->makeNurse())->get(route('staff.dashboard'))->assertOk();
        $this->actingAs($this->makeDoctor())->get(route('doctors.dashboard'))->assertOk();
    }
}
