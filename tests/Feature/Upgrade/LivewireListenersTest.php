<?php

namespace Tests\Feature\Upgrade;

use App\Livewire\Dashboard;
use App\Livewire\ReportGeneration;
use App\Livewire\StaffDashboard;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Exceptions\EventHandlerDoesNotExist;
use Livewire\Features\SupportEvents\SupportEvents;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\BuildsClinicData;
use Tests\Concerns\SeedsClinicTableLists;
use Tests\Concerns\SeedsTableLists;
use Tests\Feature\Tables\TableGoldenMasterTest;
use Tests\TestCase;

/**
 * Upgrade plan U0.8: the Livewire components listen for their events with attributes (#[On]) and not with the `$listeners`
 * property. A class-level #[On('refresh')] is Livewire's own form of `'refresh' => '$refresh'` and is inherited, a
 * method-level one names the method that handles the event.
 *
 * What a component listens for decides which of the page's JavaScript calls (Livewire.dispatch("refresh") ...) reach it, so the
 * set of events of every screen is pinned here exactly. Five lists also named events whose handler method never existed
 * (`changeFilter` on three of the lookup lists, `changeFilter` and `clearSearch` on the stock-in list): Livewire answers such
 * an event with EventHandlerDoesNotExist, so those names were dead and are not carried over.
 */
class LivewireListenersTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;
    use SeedsTableLists;
    use SeedsClinicTableLists;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-03-01 10:00:00');
        Model::unguard();
        $this->seedAccessControl();
        $this->actingAs($this->makeAdmin());
    }

    protected function tearDown(): void
    {
        Model::reguard();
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** @return array<string, array<int, string>> list => the events it listens for */
    private static function expectedEvents(): array
    {
        $both = ['refresh', 'resetPage'];
        $expected = [];
        foreach ([
            'BarangayTable', 'CityTable', 'CountriesTable', 'StateTable', 'SpecializationTable', 'SliderTable', 'RoleTable',
            'MedicineTable', 'StockOutTable', 'MedicineDispenseTable', 'StaffTable',
            'PrescriptionTable', 'PrescriptionVerificationTable', 'MedsOnlyTable',
            // these four named `changeFilter` (and StockIn `clearSearch`) without a method to handle it
            'MedicineCategoryDetailsTable', 'MedicineGenericTable', 'MedicineGenericDetailsTable', 'StockInTable',
        ] as $list) {
            $expected[$list] = $both;
        }

        return $expected + [
            'MedicineCategoryTable' => ['refresh', 'resetPage', 'changeFilter'],
            'DoctorTable' => ['refresh', 'resetPage', 'changeStatusFilter'],
            'PatientTable' => ['refresh', 'resetPage', 'changeStatusFilter', 'changeDateFilter'],
            // these two never listened for a page reset: a delete on their page keeps the page the person is on
            'DocumentIssuanceTable' => ['refresh'],
            'LabRequestTable' => ['refresh'],
        ];
    }

    /** @return array<string, array{0: string}> */
    public static function lists(): array
    {
        return TableGoldenMasterTest::lists();
    }

    public function test_every_list_is_in_the_pinned_set(): void
    {
        $this->assertEqualsCanonicalizing(array_keys(self::lists()), array_keys(self::expectedEvents()));
    }

    #[DataProvider('lists')]
    public function test_the_list_listens_for_exactly_its_events(string $list): void
    {
        $seed = $this->{'seed' . $list}();
        $component = Livewire::withoutLazyLoading()->test('App\\Livewire\\' . $list, $seed['params']);

        $listensFor = SupportEvents::getListenerEventNames($component->instance());

        $this->assertEqualsCanonicalizing(self::expectedEvents()[$list], $listensFor, "{$list} listens for other events than it did");
    }

    #[DataProvider('lists')]
    public function test_the_events_are_handled_and_the_others_refused(string $list): void
    {
        $seed = $this->{'seed' . $list}();
        $component = Livewire::withoutLazyLoading()->test('App\\Livewire\\' . $list, $seed['params']);
        $events = self::expectedEvents()[$list];

        $component->dispatch('refresh')->assertOk();

        if (in_array('resetPage', $events, true)) {
            // the event carries no page name, so it resets the default paginator ("page"), as the page's script expects
            $component->call('gotoPage', 2, 'page')->dispatch('resetPage');
            $this->assertSame(1, $component->instance()->getPage('page'), "{$list} did not go back to page 1 on resetPage");
        } else {
            $this->expectException(EventHandlerDoesNotExist::class);
            $component->dispatch('resetPage');
        }
    }

    public function test_the_dashboards_and_the_report_page_still_redraw_on_refresh(): void
    {
        foreach ([Dashboard::class, StaffDashboard::class, ReportGeneration::class] as $class) {
            $component = Livewire::withoutLazyLoading()->test($class);

            $this->assertSame(['refresh'], SupportEvents::getListenerEventNames($component->instance()), "{$class} listens for other events");
            $component->dispatch('refresh')->assertOk();
        }
    }

    public function test_no_component_uses_the_listeners_property_any_more(): void
    {
        $found = [];
        foreach (File::allFiles(app_path('Livewire')) as $file) {
            $text = file_get_contents($file->getPathname());
            if (preg_match('/\$listeners\b|function getListeners\b/', $text)) {
                $found[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $found, 'these components still declare their events with the $listeners property');
    }
}
