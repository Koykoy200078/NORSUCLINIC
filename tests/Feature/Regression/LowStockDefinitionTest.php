<?php

namespace Tests\Feature\Regression;

use App\Livewire\MedicineTable;
use App\Models\Medicine;
use App\Services\Reports\ReportFilters;
use App\Services\Reports\ReportQueries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Phase 5, rows 5.10 / 5.11: "low stock" had three definitions: the sidebar badge, the Report inventory tab (and its CSV)
 * and the colour of the quantity in the medicine list each decided differently, so the badge said 3, the report listed
 * 5 and the list warned about 4. One definition now (Medicine::scopeLowStock / isLowStock), used by all of them.
 */
class LowStockDefinitionTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    /** label => [name, attributes, low?] */
    private function cases(): array
    {
        return [
            'below the minimum' => ['Zz Below min', ['available_quantity' => 5, 'minimum_stock_alert' => 10], true],
            'above the minimum' => ['Zz Above min', ['available_quantity' => 50, 'minimum_stock_alert' => 10], false],
            'exactly at the minimum' => ['Zz At min', ['available_quantity' => 10, 'minimum_stock_alert' => 10], true],
            'ran out, no alert set' => ['Zz Ran out', ['available_quantity' => 0, 'baseline_quantity' => 100], false],
            'ran out, alert set' => ['Zz Ran out alert', ['available_quantity' => 0, 'baseline_quantity' => 100, 'minimum_stock_alert' => 10], true],
            'never had stock' => ['Zz Never', ['available_quantity' => 0, 'baseline_quantity' => 0, 'minimum_stock_alert' => 10], false],
            'percent, below' => ['Zz Percent low', ['available_quantity' => 20, 'baseline_quantity' => 100, 'stock_alert_percentage' => 25], true],
            'percent, above' => ['Zz Percent ok', ['available_quantity' => 30, 'baseline_quantity' => 100, 'stock_alert_percentage' => 25], false],
            'percent of the reorder level' => ['Zz Percent reorder', ['available_quantity' => 3, 'reorder_level' => 10, 'stock_alert_percentage' => 50], true],
            'percent with nothing to compare to' => ['Zz Percent nothing', ['available_quantity' => 3, 'stock_alert_percentage' => 200], false],
            'no alert at all' => ['Zz No alert', ['available_quantity' => 1], false],
        ];
    }

    private function makeCases(): array
    {
        $ids = [];
        foreach ($this->cases() as $label => [$name, $attributes, $low]) {
            $medicine = $this->makeMedicine($name, $attributes);
            $ids[$label] = $medicine->id;
        }

        return $ids;
    }

    private function expectedLowIds(array $ids): array
    {
        $low = [];
        foreach ($this->cases() as $label => [, , $isLow]) {
            if ($isLow) {
                $low[] = $ids[$label];
            }
        }
        sort($low);

        return $low;
    }

    public function test_the_scope_and_the_method_agree_with_the_rule_on_every_case(): void
    {
        $ids = $this->makeCases();

        $byScope = Medicine::lowStock()->pluck('id')->sort()->values()->all();
        $byMethod = Medicine::all()->filter(fn (Medicine $m) => $m->isLowStock())->pluck('id')->sort()->values()->all();

        $this->assertSame($this->expectedLowIds($ids), $byScope, 'the SQL definition');
        $this->assertSame($this->expectedLowIds($ids), $byMethod, 'the PHP definition');
    }

    public function test_the_sidebar_the_report_its_csv_and_the_medicine_list_all_show_the_same_medicines(): void
    {
        $admin = $this->makeAdmin();
        $ids = $this->makeCases();
        $expected = $this->expectedLowIds($ids);
        $this->actingAs($admin);
        Cache::flush();

        // the sidebar badge counts them
        $page = $this->get(route('medicine-inventory.index'))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/Low Stock: (\d+) medicine/', $page, $badge), 'the sidebar shows a low stock badge');
        $this->assertSame(count($expected), (int) $badge[1], 'sidebar badge');

        // the report's "Low Stock" filter lists exactly them
        $report = ReportQueries::inventory(ReportFilters::fromArray(['status' => 'low_stock']))->pluck('id')->sort()->values()->all();
        $this->assertSame($expected, $report, 'report filter');

        // and the CSV marks exactly them "Low Stock"
        $csv = $this->get(route('activity-logs.export', ['tab' => 'inventory']))->assertOk()->streamedContent();
        $lowRows = array_values(array_filter(explode("\n", $csv), fn ($line) => str_contains($line, ',Low Stock') || str_contains($line, '"Low Stock"')));
        $lowNames = collect($lowRows)->map(fn ($line) => str_getcsv($line)[0])->sort()->values()->all();
        // a medicine that ran out is "Out of Stock" in the status column (it is still in the Low Stock filter above)
        $expectedNames = collect($this->cases())->filter(fn ($case) => $case[2] && ($case[1]['available_quantity'] ?? 0) > 0)->map(fn ($case) => $case[0])->sort()->values()->all();
        $this->assertSame($expectedNames, $lowNames, 'CSV status column');

        // the medicine list warns about exactly them (the warning text is in each row's icon title)
        $html = Livewire::withoutLazyLoading()->test(MedicineTable::class)->set('perPage', 50)->html();
        $warned = [];
        foreach ($this->cases() as $label => [$name, , $isLow]) {
            $row = $this->rowOf($html, $name);
            $this->assertNotSame('', $row, "{$name} is in the list");
            if (str_contains($row, 'Low stock!')) {
                $warned[] = $name;
            }
        }
        sort($warned);
        // a medicine that ran out is shown as "Out of stock!" instead; the list warns "Low stock!" only for the others
        $expectedWarned = collect($this->cases())->filter(fn ($case) => $case[2] && ($case[1]['available_quantity'] ?? 0) > 0)->map(fn ($case) => $case[0])->sort()->values()->all();
        $this->assertSame($expectedWarned, $warned, 'medicine list warnings');
    }

    /** The html of the list row that holds this medicine name. */
    private function rowOf(string $html, string $name): string
    {
        foreach (preg_split('/<tr\b/i', $html) as $part) {
            if (str_contains($part, $name)) {
                return $part;
            }
        }

        return '';
    }

    public function test_the_inventory_report_page_reads_each_medicines_batches_once_for_the_whole_list(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $add = function (int $i) {
            $medicine = $this->makeMedicine('Zz Report med ' . $i, ['minimum_stock_alert' => 5]);
            $this->stockIn($medicine, 20, now()->addMonths(6)->toDateString());
        };
        $queries = function () {
            $component = Livewire::withoutLazyLoading()->test(\App\Livewire\ReportGeneration::class, [])->set('tab', 'inventory');
            \Illuminate\Support\Facades\DB::flushQueryLog();
            \Illuminate\Support\Facades\DB::enableQueryLog();
            $component->call('$refresh');
            $count = count(\Illuminate\Support\Facades\DB::getQueryLog());
            \Illuminate\Support\Facades\DB::disableQueryLog();

            return $count;
        };

        foreach (range(1, 3) as $i) {
            $add($i);
        }
        $few = $queries();
        foreach (range(4, 12) as $i) {
            $add($i);
        }

        $this->assertSame($few, $queries(), 'the inventory report must not query once per medicine');
    }
}
