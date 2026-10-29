<?php

namespace App\Http\Controllers;

use App\Models\InventoryCount;
use App\Models\InventoryCountLine;
use App\Models\Medicine;
use App\Services\InventoryCountService;
use App\Services\Reports\YearlyInventoryReportBuilder;
use App\Support\SearchTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Quarterly count sheets (Phase 11 D): the clinic types the final remaining count of each medicine, with a reason and a note.
 * The work and every rule is InventoryCountService; this class opens the pages and answers where to go next. Everything sits
 * behind the `manage_medicines` permission (routes); staff and admin work with sheets, a doctor only reads them.
 */
class InventoryCountController extends Controller
{
    private const FILTERS = ['all', 'uncounted', 'difference'];

    public function __construct(private InventoryCountService $service)
    {
    }

    public function index(): View
    {
        $sheets = InventoryCount::query()->with('creator')
            ->withCount([
                'lines',
                'lines as counted_count' => fn ($query) => $query->whereNotNull('counted_quantity'),
                'lines as difference_count' => fn ($query) => $query->whereNotNull('difference')->where('difference', '!=', 0),
            ])
            ->orderByDesc('counted_on')->orderByDesc('id')->get();

        return view('inventory_counts.index', [
            'sheets' => $sheets,
            'canWrite' => ! isRole('doctor'),
            'years' => YearlyInventoryReportBuilder::years(),
            'medicines' => Medicine::query()->orderBy('name')->orderBy('id')->get(['id', 'name', 'dosage', 'uom']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $sheet = $this->service->create($request->only(['year', 'quarter', 'scope', 'counted_on', 'notes', 'medicine_ids']), $request->user());

        return redirect(getRouteByRole('inventory-counts.show', ['count' => $sheet->id]))
            ->with('success', 'Count sheet ' . $sheet->label() . ' made. Type what you counted.');
    }

    public function show(Request $request, InventoryCount $count): View
    {
        $filter = in_array($request->query('filter'), self::FILTERS, true) ? $request->query('filter') : 'all';
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);

        $lines = $count->lines()->getQuery()
            ->when($filter === 'uncounted', fn ($query) => $query->whereNull('counted_quantity'))
            ->when($filter === 'difference', fn ($query) => $query->whereNotNull('difference')->where('difference', '!=', 0))
            ->orderBy('medicine_name')->orderBy('id');
        $lines = SearchTerm::whereAllWords($lines, $search, ['medicine_name', 'strength', 'unit', 'note'])->get();

        $all = $count->lines()->get();
        $canWrite = ! isRole('doctor');

        return view('inventory_counts.show', [
            'count' => $count->load(['creator', 'finalizer']),
            'lines' => $lines,
            'filter' => $filter,
            'search' => $search,
            'canWrite' => $canWrite,
            'editable' => $canWrite && $count->isDraft(),
            'totals' => ['medicines' => $all->count(), 'counted' => $all->filter->isCounted()->count(), 'with_difference' => $all->filter->hasDifference()->count()],
            // Why the shelf cannot be corrected from a line (null = it can); only a finalized sheet, only for staff and admin.
            'corrections' => $canWrite && $count->isFinalized()
                ? $lines->mapWithKeys(fn (InventoryCountLine $line) => [$line->id => $this->service->correctionBlockedReason($line)])->all()
                : [],
        ]);
    }

    public function update(Request $request, InventoryCount $count): RedirectResponse
    {
        $this->service->saveCounts($count, (array) $request->input('rows', []), $request->user());

        return $this->back($request, $count)->with('success', 'Counts saved.');
    }

    public function refresh(Request $request, InventoryCount $count): RedirectResponse
    {
        $this->service->refreshSystem($count, $request->user());

        return $this->back($request, $count)->with('success', 'System amounts refreshed; the differences were worked out again.');
    }

    public function finalize(Request $request, InventoryCount $count): RedirectResponse
    {
        $this->service->finalize($count, $request->user());

        return redirect(getRouteByRole('inventory-counts.show', ['count' => $count->id]))
            ->with('success', 'Count sheet finalized. It is frozen now and the reports use it.');
    }

    public function destroy(Request $request, InventoryCount $count): RedirectResponse
    {
        $label = $count->label();
        $this->service->delete($count, $request->user());

        return redirect(getRouteByRole('inventory-counts.index'))->with('success', 'Count sheet ' . $label . ' deleted.');
    }

    /** Back to the sheet, with the filter and the search the person was using. */
    private function back(Request $request, InventoryCount $count): RedirectResponse
    {
        $query = array_filter($request->only(['filter', 'search']), fn ($value) => is_string($value) && $value !== '' && $value !== 'all');

        return redirect(getRouteByRole('inventory-counts.show', ['count' => $count->id] + $query));
    }
}
