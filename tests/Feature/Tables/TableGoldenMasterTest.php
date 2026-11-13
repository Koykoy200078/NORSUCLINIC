<?php

namespace Tests\Feature\Tables;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\BuildsClinicData;
use Tests\Concerns\ReadsRenderedTables;
use Tests\Concerns\SeedsClinicTableLists;
use Tests\Concerns\SeedsTableLists;
use Tests\TestCase;

/**
 * Upgrade plan U0.4a: the golden master of every list screen. It was recorded on the old table library
 * (rappasoft/laravel-livewire-tables) so that the in-house tables which replaced it could be proved to behave and to look
 * the same, list by list: the fixtures are that library's screens, and TableComponent has to keep matching them.
 *
 * For each list a fixed set of 35 rows is shown and the screen is read the way a person sees it (ReadsRenderedTables):
 * the first page, pages 2 and 4, sorted by the first and the last sortable header (up, down, cleared), searched for a word
 * that matches a tenth of the rows, searched for nothing that exists, 25 per page and a per-page value that is not offered.
 * The result is compared with tests/Fixtures/tables/<List>.json.
 *
 * Record or re-record a fixture (and read the diff in git before committing it):
 *     UPDATE_TABLE_GOLDEN=1 vendor/bin/phpunit --filter TableGoldenMasterTest
 */
class TableGoldenMasterTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;
    use ReadsRenderedTables;
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

    /** @return array<string, array{0: string}> every list screen, by class name (the names do not change when the library does) */
    public static function lists(): array
    {
        $names = [
            // lookups
            'BarangayTable', 'CityTable', 'CountriesTable', 'StateTable', 'SpecializationTable', 'SliderTable', 'RoleTable',
            'MedicineCategoryTable', 'MedicineCategoryDetailsTable', 'MedicineGenericTable', 'MedicineGenericDetailsTable',
            // inventory
            'MedicineTable', 'StockInTable', 'StockOutTable', 'MedicineDispenseTable',
            // people
            'StaffTable', 'DoctorTable', 'PatientTable',
            // clinical
            'PrescriptionTable', 'PrescriptionVerificationTable', 'DocumentIssuanceTable', 'LabRequestTable', 'MedsOnlyTable',
        ];

        return array_combine($names, array_map(fn ($name) => [$name], $names));
    }

    public function test_every_list_screen_has_a_golden_master(): void
    {
        // A list screen is a Livewire class that declares columns().
        $screens = [];
        foreach (glob(app_path('Livewire') . '/*.php') as $file) {
            if (str_contains(file_get_contents($file), 'function columns(')) {
                $screens[] = basename($file, '.php');
            }
        }

        $this->assertEqualsCanonicalizing($screens, array_keys(self::lists()), 'a list screen has no golden master (or a recorded one no longer exists)');
        foreach ($screens as $screen) {
            $this->assertFileExists(base_path("tests/Fixtures/tables/{$screen}.json"), "{$screen} has no recorded fixture");
        }
    }

    #[DataProvider('lists')]
    public function test_the_list_looks_and_behaves_as_recorded(string $list): void
    {
        $class = 'App\\Livewire\\' . $list;
        $seed = $this->{'seed' . $list}();

        $snapshots = [];
        $open = fn (array $more = []) => Livewire::withoutLazyLoading()->test($class, $more + $seed['params']);
        $read = fn (Testable $component) => $this->snapshotOf($component->html());

        $component = $open();
        $snapshots['first page'] = $read($component);
        $pageName = $component->instance()->getComputedPageName();
        foreach ([2, 4] as $page) {
            $component->call('gotoPage', $page, $pageName);
            $snapshots["page {$page}"] = $read($component);
        }

        $sortable = $snapshots['first page']['sortable'];
        foreach (array_values(array_unique([$sortable[0] ?? null, $sortable[count($sortable) - 1] ?? null])) as $field) {
            if ($field === null) {
                continue;
            }
            $component = $open();
            foreach (['ascending', 'descending', 'cleared'] as $step) {
                $component->call('sortBy', $field);
                $snapshots["sorted by {$field}, {$step}"] = $read($component);
            }
        }

        $component = $open()->set('search', $seed['search']);
        $snapshots['searched for the word'] = $read($component);

        $component = $open()->set('search', 'nothing-has-this-text');
        $snapshots['searched for nothing'] = $read($component);

        $component = $open()->set('perPage', 25);
        $snapshots['25 per page'] = $read($component);

        $component = $open()->set('perPage', 7);
        $snapshots['7 per page (not offered)'] = $read($component);

        // the filters a list has in its header (status, source ...), each on a fresh screen
        // (an array is a set of mount parameters, for a property the screen locks after it is mounted)
        foreach ($seed['extras'] ?? [] as $name => $apply) {
            if (is_array($apply)) {
                $component = $open($apply);
            } else {
                $component = $open();
                $apply($component);
            }
            $snapshots[$name] = $read($component);
        }

        $this->assertMatchesRecordedList($list, $snapshots);
    }

    /** @param array<string, array<string, mixed>> $snapshots */
    private function assertMatchesRecordedList(string $list, array $snapshots): void
    {
        $path = base_path("tests/Fixtures/tables/{$list}.json");
        $json = json_encode($snapshots, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

        if (getenv('UPDATE_TABLE_GOLDEN') === '1') {
            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }
            file_put_contents($path, $json);
            $this->addToAssertionCount(1);

            return;
        }

        $this->assertFileExists($path, "no golden master recorded for {$list}: run with UPDATE_TABLE_GOLDEN=1 and read the result");
        $this->assertSame(json_decode(file_get_contents($path), true), json_decode($json, true), "{$list} no longer looks or behaves as recorded");
    }
}
