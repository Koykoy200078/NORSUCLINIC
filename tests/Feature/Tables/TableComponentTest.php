<?php

namespace Tests\Feature\Tables;

use App\Models\Country;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\Concerns\ReadsRenderedTables;
use Tests\Support\Tables\DemoStateTable;
use Tests\TestCase;

/**
 * Upgrade plan U0.4b: every behaviour of the in-house list base (App\Livewire\Tables\TableComponent), one by one, on a
 * small list of states (DemoStateTable). The golden master (TableGoldenMasterTest) proves the real lists look and behave as
 * they did on the old library; these tests prove what the base itself promises, so each promise can be broken on purpose.
 */
class TableComponentTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;
    use ReadsRenderedTables;

    private Country $philippines;

    private Country $japan;

    protected function setUp(): void
    {
        parent::setUp();
        View::addLocation(base_path('tests/Support/views'));
        DemoStateTable::$tdCalls = [];
        $this->seedAccessControl();
        $this->actingAs($this->makeAdmin());
        $this->philippines = Country::forceCreate(['name' => 'Philippines', 'short_code' => 'PH']);
        $this->japan = Country::forceCreate(['name' => 'Japan', 'short_code' => 'JP']);
    }

    private function state(string $name, ?Country $country = null, string $createdAt = '2026-02-01 08:00:00'): State
    {
        return State::forceCreate(['name' => $name, 'country_id' => ($country ?? $this->philippines)->id, 'created_at' => $createdAt, 'updated_at' => $createdAt]);
    }

    /** @param array<int, string> $features */
    private function open(array $features = []): Testable
    {
        return Livewire::withoutLazyLoading()->test(DemoStateTable::class, ['features' => $features]);
    }

    /** @return array<int, string> the first cell of every row on the screen */
    private function names(Testable $component): array
    {
        return array_map(fn ($row) => $row['cells'][0], $this->snapshotOf($component->html())['rows']);
    }

    /** the page with Livewire's block comments removed and the blanks between tags collapsed */
    private function markup(string $html): string
    {
        return preg_replace('/>\s+</', '><', preg_replace('/\s+/', ' ', preg_replace('/<!--.*?-->/s', '', $html)));
    }

    private function alphabet(int $count): void
    {
        foreach (array_slice(['Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo', 'Foxtrot', 'Golf', 'Hotel', 'India', 'Juliet', 'Kilo', 'Lima', 'Mike', 'November', 'Oscar'], 0, $count) as $i => $name) {
            $this->state($name, $i % 2 ? $this->japan : $this->philippines, '2026-02-01 08:' . sprintf('%02d', $i) . ':00');
        }
    }

    public function test_the_rows_of_the_query_are_drawn_with_their_headers_and_a_related_value(): void
    {
        $this->state('Cebu', $this->philippines);
        $this->state('Osaka', $this->japan, '2026-02-02 08:00:00');

        $snapshot = $this->snapshotOf($this->open()->html());

        $this->assertSame(['Name', 'Country'], $snapshot['headers']);
        $this->assertSame([['Osaka', 'Japan'], ['Cebu', 'Philippines']], array_map(fn ($row) => $row['cells'], $snapshot['rows']), 'newest first (the default sort), with the related country name');
        $this->assertSame(['name', 'country.name'], $snapshot['sortable']);
    }

    public function test_a_search_needs_every_word_and_each_word_may_be_found_in_a_different_column(): void
    {
        $this->state('Cebu', $this->philippines);
        $this->state('Cebu', $this->japan);
        $this->state('Osaka', $this->japan);

        $this->assertEqualsCanonicalizing(['Cebu', 'Cebu'], $this->names($this->open()->set('search', 'cebu')));
        $this->assertSame(['Cebu'], $this->names($this->open()->set('search', 'cebu japan')), 'the name word is in one column and the country word in another');
        $this->assertSame(['Cebu'], $this->names($this->open()->set('search', 'japan cebu')), 'the order of the words does not matter');
        $this->assertSame([], $this->names($this->open()->set('search', 'osaka philippines')));
        $this->assertCount(3, $this->names($this->open()->set('search', '   ')), 'blanks alone do not filter');
    }

    public function test_a_search_returns_to_the_first_page(): void
    {
        $this->alphabet(15);
        $component = $this->open();
        $component->call('gotoPage', 2, $component->instance()->getComputedPageName());
        $this->assertSame(2, $component->instance()->getPage());

        $component->set('search', 'a');

        $this->assertSame(1, $component->instance()->getPage());
    }

    public function test_a_column_sorts_ascending_then_descending_then_not_at_all_and_only_one_column_at_a_time(): void
    {
        $this->alphabet(4);
        $component = $this->open();

        $component->call('sortBy', 'name');
        $this->assertSame(['name' => 'asc'], $component->instance()->sorts);
        $this->assertSame(['Alpha', 'Bravo', 'Charlie', 'Delta'], $this->names($component));

        $component->call('sortBy', 'name');
        $this->assertSame(['name' => 'desc'], $component->instance()->sorts);
        $this->assertSame(['Delta', 'Charlie', 'Bravo', 'Alpha'], $this->names($component));

        $component->call('sortBy', 'name');
        $this->assertSame([], $component->instance()->sorts);
        $this->assertSame(['Delta', 'Charlie', 'Bravo', 'Alpha'], $this->names($component), 'back to the default order (newest first)');

        $component->call('sortBy', 'name')->call('sortBy', 'country.name');
        $this->assertSame(['country.name' => 'asc'], $component->instance()->sorts, 'choosing another column replaces the first');
    }

    public function test_sorting_goes_back_to_the_first_page(): void
    {
        $this->alphabet(15);
        $component = $this->open();
        $component->call('gotoPage', 2, $component->instance()->getComputedPageName());

        $component->call('sortBy', 'name');

        $this->assertSame(1, $component->instance()->getPage());
    }

    public function test_a_related_column_sorts_by_the_related_value(): void
    {
        $this->state('Cebu', $this->philippines);
        $this->state('Osaka', $this->japan);
        $this->state('Davao', $this->philippines);

        $component = $this->open()->call('sortBy', 'country.name');
        $this->assertSame('Osaka', $this->names($component)[0], 'Japan comes before Philippines');

        $component->call('sortBy', 'country.name');
        $this->assertSame('Osaka', array_reverse($this->names($component))[0], 'and last when descending');
    }

    public function test_the_default_sort_applies_until_the_user_sorts(): void
    {
        $this->state('Older', $this->philippines, '2026-01-01 08:00:00');
        $this->state('Newer', $this->philippines, '2026-03-01 08:00:00');
        $this->state('Middle', $this->philippines, '2026-02-01 08:00:00');

        $this->assertSame(['Newer', 'Middle', 'Older'], $this->names($this->open()));
        $this->assertSame(['Middle', 'Newer', 'Older'], $this->names($this->open()->call('sortBy', 'name')));
    }

    public function test_rows_that_tie_on_the_sorted_value_end_with_the_rows_own_key_so_no_page_repeats_or_loses_a_row(): void
    {
        $ids = [];
        foreach (range(1, 25) as $unused) {
            $ids[] = $this->state('Same name', $this->philippines, '2026-02-01 08:00:00')->id;
        }

        foreach ([[], ['no-default-sort']] as $features) {
            $component = $this->open($features)->call('sortBy', 'name');
            $seen = [];
            foreach ([1, 2, 3] as $page) {
                $component->call('gotoPage', $page, $component->instance()->getComputedPageName());
                foreach ($component->instance()->getRows() as $row) {
                    $seen[] = $row->id;
                }
            }

            $this->assertSame(array_reverse($ids), $seen, 'every row on exactly one page, the newest key first among equals');
        }
    }

    public function test_a_per_page_choice_that_is_not_offered_falls_back_to_the_first_and_returns_to_page_one(): void
    {
        $this->alphabet(15);
        $component = $this->open();

        $component->set('perPage', 25);
        $this->assertSame(25, $component->instance()->perPage);
        $this->assertCount(15, $this->names($component));

        $component->call('gotoPage', 2, $component->instance()->getComputedPageName())->set('perPage', 10);
        $this->assertSame(1, $component->instance()->getPage(), 'a new page size starts from the first page');

        $component->set('perPage', 7);
        $this->assertSame(10, $component->instance()->perPage);
        $this->assertSame(['10', '25', '50'], $this->snapshotOf($component->html())['perPage']);
    }

    public function test_a_hidden_column_is_not_drawn_but_it_is_still_searched(): void
    {
        $target = $this->state('Cebu');
        $this->state('Osaka', $this->japan);

        $snapshot = $this->snapshotOf($this->open(['hidden'])->html());
        $this->assertSame(['Name', 'Country'], $snapshot['headers'], 'no "Id" header');
        $this->assertSame(2, count($snapshot['rows'][0]['cells']), 'and no id cell');

        $this->assertSame(['Cebu'], $this->names($this->open(['hidden'])->set('search', (string) $target->id)), 'the hidden column still takes part in the search');
        $this->assertSame([], $this->names($this->open()->set('search', (string) $target->id)), 'without the hidden column the id is not searched');
    }

    public function test_a_column_with_no_field_of_its_own_is_sorted_by_its_callback_under_its_slug(): void
    {
        $this->state('Cebu');
        $this->state('Manila');
        $this->state('Ho');

        $snapshot = $this->snapshotOf($this->open(['callback'])->html());
        $this->assertContains('size', $snapshot['sortable'], 'the header of a computed column sorts under its slug');

        $component = $this->open(['callback'])->call('sortBy', 'size');
        $this->assertSame(['Ho', 'Cebu', 'Manila'], $this->names($component));
        $component->call('sortBy', 'size');
        $this->assertSame(['Manila', 'Cebu', 'Ho'], $this->names($component));
    }

    public function test_asking_to_sort_by_a_column_that_does_not_exist_changes_nothing(): void
    {
        $this->alphabet(3);

        $this->assertSame(['Charlie', 'Bravo', 'Alpha'], $this->names($this->open()->call('sortBy', 'nonsense')));
    }

    public function test_text_is_escaped_unless_the_column_says_it_is_html_and_a_view_cell_gets_the_value_the_row_and_the_column(): void
    {
        $state = $this->state('Cebu');

        $html = $this->open(['cells'])->html();

        $this->assertStringContainsString('&lt;b&gt;Cebu&lt;/b&gt;', $html, 'escaped');
        $this->assertStringContainsString('<b>Cebu</b>', $html, 'html()');
        $this->assertStringContainsString('<span class="demo-cell" data-row="' . $state->id . '" data-column="View">cell of Cebu</span>', preg_replace('/\s+/', ' ', $html));
    }

    public function test_th_td_and_table_attributes_come_from_the_list(): void
    {
        $this->state('Cebu');
        $this->state('Osaka', $this->japan);
        $component = $this->open(['attributes']);
        $html = $this->markup($component->html());

        $this->assertMatchesRegularExpression('/<table[^>]*\bid="demo-table"/', $html);
        $this->assertMatchesRegularExpression('/<table[^>]*class="[^"]*\bextra-table-class\b[^"]*"/', $html);
        $this->assertMatchesRegularExpression('/<table[^>]*class="[^"]*\btable table-striped\b/', $html, 'the default classes stay');
        $this->assertMatchesRegularExpression('/<th[^>]*class="text-center"[^>]*><div[^>]*><span>Name/', $html);
        // isField() compares the field name only, so the related column "country.name" is a "name" field too (as on the old library)
        $this->assertSame(4, substr_count($html, 'class="pt-5"'), 'both name fields, once per row');
        $this->assertContains([0, 0], DemoStateTable::$tdCalls);
        $this->assertContains([1, 1], DemoStateTable::$tdCalls, 'the callback gets the column index and the row index');
    }

    public function test_the_default_table_has_an_id_of_its_own(): void
    {
        $this->assertMatchesRegularExpression('/<table[^>]*\bid="table-table"/', $this->open()->html());
        $this->assertMatchesRegularExpression('/<table[^>]*\bid="table-states"/', $this->open(['named'])->html());
    }

    public function test_header_buttons_and_filters_are_drawn_only_when_the_list_asks_for_them(): void
    {
        $this->assertStringNotContainsString('id="demo-add-button"', $this->open()->html());
        $this->assertStringNotContainsString('id="demo-filter"', $this->open()->html());

        $html = Livewire::withoutLazyLoading()->test(DemoStateTable::class, ['showButtonOnHeader' => true, 'showFilterOnHeader' => true])->html();

        $this->assertStringContainsString('id="demo-add-button"', $html);
        $this->assertStringContainsString('Filter: Region / Island', $html);
    }

    public function test_an_empty_list_says_so_across_the_whole_table_and_a_list_may_say_something_else(): void
    {
        $this->assertSame('No data available in table', $this->snapshotOf($this->open()->html())['empty']);
        $this->assertSame('Nothing here yet', $this->snapshotOf($this->open(['empty-message'])->html())['empty']);
        $this->assertStringContainsString('colspan="100"', $this->open()->html());
    }

    public function test_a_list_with_a_table_name_pages_through_its_own_page_name(): void
    {
        $this->alphabet(15);
        $component = $this->open(['named']);

        $this->assertSame('statesPage', $component->instance()->getComputedPageName());
        $this->assertSame('page', $this->open()->instance()->getComputedPageName());
        $this->assertStringContainsString("gotoPage(2, 'statesPage')", $component->html());

        $component->call('gotoPage', 2, 'statesPage');
        $this->assertCount(5, $this->names($component));
    }

    public function test_the_screen_is_lazy_and_draws_a_skeleton_until_it_loads(): void
    {
        $this->state('Cebu');

        $html = Livewire::test(DemoStateTable::class, ['features' => []])->html();

        $this->assertStringContainsString('listing-skeleton', $html);
        $this->assertStringNotContainsString('<table', $html);
        $this->assertStringNotContainsString('Cebu', $html);
    }

    public function test_the_refresh_event_shows_the_rows_added_since(): void
    {
        $component = $this->open();
        $this->assertSame([], $this->names($component));

        $this->state('Cebu');
        $component->dispatch('refresh');

        $this->assertSame(['Cebu'], $this->names($component));
    }

    public function test_asking_for_the_rows_again_does_not_stack_the_search_on_the_query(): void
    {
        $this->state('Cebu');
        $component = $this->open()->set('search', 'cebu');

        DB::flushQueryLog();
        DB::enableQueryLog();
        $component->instance()->getRows();
        $component->instance()->getRows();
        $selects = array_values(array_filter(DB::getQueryLog(), fn ($query) => str_contains($query['query'], 'as `country.name`') && ! str_contains($query['query'], 'count(*)')));
        DB::disableQueryLog();

        $this->assertCount(2, $selects, 'one select per call (the count is a different query)');
        $this->assertSame($selects[0]['query'], $selects[1]['query'], 'the second call asked exactly what the first one did');
        $this->assertSame(2, substr_count($selects[0]['query'], ' like '), 'one like per searchable column, once');
    }

    public function test_the_columns_the_list_asked_for_are_selected_under_their_own_name_and_extra_selects_are_added(): void
    {
        $this->state('Cebu');
        $row = $this->open(['additional'])->instance()->getRows()->first();

        $this->assertSame('Philippines', $row->{'country.name'}, 'a related column is selected as "country.name"');
        $this->assertSame($this->philippines->id, (int) $row->demo_country_id, 'setAdditionalSelects()');
    }
}
