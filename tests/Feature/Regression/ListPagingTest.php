<?php

namespace Tests\Feature\Regression;

use App\Livewire\DoctorTable;
use App\Livewire\DocumentIssuanceTable;
use App\Livewire\LivewireTableComponent;
use App\Livewire\MedicineDispenseTable;
use App\Livewire\MedicineTable;
use App\Livewire\RoleTable;
use App\Livewire\SpecializationTable;
use App\Models\Doctor;
use App\Models\DocumentIssuance;
use App\Models\Role;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use ReflectionClass;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 5, Pass A item 4: a list sorted by a value that repeats (a date, a name, a status) needs a unique LAST sort
 * key. Without one MySQL orders the tied rows differently for each page query, so one page repeats rows that another page
 * never shows (found on Dispense History in the Phase 2 re-check: 60 same-day records listed 48 different rows).
 * Every list now ends its ORDER BY with the row's own key (LivewireTableComponent::applySorting()).
 */
class ListPagingTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private const INSTANT = '2026-03-01 10:00:00';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->actingAs($this->makeAdmin());
    }

    /**
     * @param  class-string  $table
     * @return array<int, mixed> the ids of every page, in the order the pages show them
     */
    private function idsOfEveryPage(string $table, int $pages, ?string $sortBy = null): array
    {
        $component = Livewire::withoutLazyLoading()->test($table);
        if ($sortBy !== null) {
            $component->call('sortBy', $sortBy);
        }

        $seen = [];
        foreach (range(1, $pages) as $page) {
            // A list with a table name of its own (the medicines) pages through "<name>Page", not "page".
            $component->call('gotoPage', $page, $component->instance()->getComputedPageName());
            foreach ($component->instance()->getRows()->pluck('id') as $id) {
                $seen[] = $id;
            }
        }

        return $seen;
    }

    private function assertEveryRowOnExactlyOnePage(array $expectedIds, array $seen, string $what): void
    {
        $this->assertCount(count($expectedIds), $seen, "{$what}: the pages hold the wrong number of rows");
        $this->assertCount(count($expectedIds), array_unique($seen), "{$what}: a row repeated on another page");
        $this->assertEqualsCanonicalizing($expectedIds, $seen, "{$what}: a row never appeared on any page");
    }

    public function test_specializations_that_share_a_date_or_a_name_never_repeat_or_vanish_between_pages(): void
    {
        for ($i = 0; $i < 60; $i++) {
            Specialization::create(['name' => 'Specialty ' . ($i % 4)]);
        }
        DB::table('specializations')->update(['created_at' => self::INSTANT]);
        $ids = Specialization::pluck('id')->all();

        $this->assertEveryRowOnExactlyOnePage($ids, $this->idsOfEveryPage(SpecializationTable::class, 6), 'default order (created_at)');

        $seen = $this->idsOfEveryPage(SpecializationTable::class, 6, 'name');
        $this->assertEveryRowOnExactlyOnePage($ids, $seen, 'sorted by name (4 different names)');

        // The unique key only settles ties: the column the user chose still decides the order.
        $names = array_map(fn ($id) => Specialization::find($id)->name, $seen);
        $sorted = $names;
        sort($sorted);
        $this->assertSame($sorted, $names, 'the chosen sort column comes first');
    }

    public function test_a_list_sorted_by_a_column_of_another_table_ends_with_the_qualified_key(): void
    {
        // The doctors list sorts by the user's status: the library joins `users`, so a bare `id` would be ambiguous.
        $doctors = collect(range(1, 3))->map(fn () => $this->makeDoctor());
        $expected = Doctor::pluck('id')->all();
        $this->assertGreaterThanOrEqual(3, count($expected));

        $component = Livewire::withoutLazyLoading()->test(DoctorTable::class)->call('sortBy', 'user.status');

        $this->assertEqualsCanonicalizing($expected, $component->instance()->getRows()->pluck('id')->all());
    }

    public function test_roles_that_share_a_date_never_repeat_or_vanish_between_pages(): void
    {
        for ($i = 0; $i < 40; $i++) {
            Role::create(['name' => 'tie_role_' . $i, 'guard_name' => 'web', 'display_name' => 'Tie role ' . ($i % 3)]);
        }
        DB::table('roles')->update(['created_at' => self::INSTANT]);
        $ids = Role::whereNotNull('display_name')->where('display_name', '!=', '')->where('name', '!=', 'nurse')->pluck('id')->all();

        $this->assertEveryRowOnExactlyOnePage($ids, $this->idsOfEveryPage(RoleTable::class, (int) ceil(count($ids) / 10)), 'roles, default order');
    }

    public function test_medicines_that_share_a_date_never_repeat_or_vanish_between_pages(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->makeMedicine('Med ' . ($i % 5));
        }
        DB::table('medicines')->update(['created_at' => self::INSTANT]);
        $ids = DB::table('medicines')->pluck('id')->all();

        $this->assertEveryRowOnExactlyOnePage($ids, $this->idsOfEveryPage(MedicineTable::class, 6), 'medicines, default order');
        $this->assertEveryRowOnExactlyOnePage($ids, $this->idsOfEveryPage(MedicineTable::class, 6, 'name'), 'medicines sorted by name (5 different names)');
    }

    public function test_the_consultation_list_has_no_default_order_of_its_own_so_it_shows_the_newest_record_first(): void
    {
        $nurse = $this->makeNurse();
        $patient = $this->makePatient();
        $ids = [];
        for ($i = 0; $i < 25; $i++) {
            $ids[] = DocumentIssuance::create([
                'document_type' => 'consultation_form', 'document_creator_id' => $nurse->id, 'user_id' => $patient->user_id,
                'name' => 'Juan Dela Cruz', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
            ])->id;
        }
        DB::table('document_issuances')->update(['created_at' => self::INSTANT]);

        $seen = $this->idsOfEveryPage(DocumentIssuanceTable::class, 3);

        $this->assertEveryRowOnExactlyOnePage($ids, $seen, 'consultations, default order');
        $this->assertSame(array_reverse($ids), $seen, 'a list nobody sorted shows the newest record first, not the oldest');
    }

    public function test_every_list_gets_the_shared_unique_last_sort_key_except_dispense_history_which_has_two_of_its_own(): void
    {
        $others = [];
        $tables = 0;
        foreach (glob(app_path('Livewire') . '/*.php') as $file) {
            $class = 'App\\Livewire\\' . basename($file, '.php');
            if (! is_subclass_of($class, DataTableComponent::class)) {
                continue;
            }
            $tables++;

            // A trait's method reports the trait's file: this is how a list that brings its own ordering shows up.
            $declaredIn = basename((new ReflectionClass($class))->getMethod('applySorting')->getFileName());
            if ($declaredIn !== 'SortsByUniqueKey.php') {
                $others[] = $class;
            }
        }

        $this->assertGreaterThan(20, $tables, 'the scan found the lists');
        // The Dispense History view has no single unique column, so it orders by the record number and then the row id.
        $this->assertSame([MedicineDispenseTable::class], $others, 'a list that orders rows its own way must end with a unique key too');
        $this->assertTrue(is_subclass_of(DocumentIssuanceTable::class, DataTableComponent::class) && ! is_subclass_of(DocumentIssuanceTable::class, LivewireTableComponent::class), 'the consultation list extends the library table directly and still gets the key');
    }
}
