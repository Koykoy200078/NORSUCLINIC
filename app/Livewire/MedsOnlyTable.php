<?php

namespace App\Livewire;

use App\Models\DispenseRecord;
use App\Services\Reports\ReportQueries;
use App\Support\SearchTerm;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Lazy;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Meds Only: the patients who only came for medicine. Date, name, course, year, college and the drugs, as the clinic keeps
 * them on paper; the search finds a person, a college or course, or a drug by its name / generic / brand.
 */
#[Lazy]
class MedsOnlyTable extends LivewireTableComponent
{
    protected $model = DispenseRecord::class;

    protected $listeners = ['refresh' => '$refresh', 'resetPage'];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('medicine_bills.bill_date', 'desc')
            ->setTableAttributes(['class' => 'table table-striped table-bordered'])
            // Rappasoft only SELECTs the mapped columns; the label columns need these to load their relations.
            ->setAdditionalSelects(['medicine_bills.id', 'medicine_bills.recorded_by', 'medicine_bills.patient_id']);
    }

    public function placeholder(): string
    {
        return view('livewire.loading_skeleton')->render();
    }

    public function builder(): Builder
    {
        // The page route checks this too; a Livewire request skips route middleware, so it is checked here as well.
        abort_unless(canUseModule('meds_only'), 403);

        return DispenseRecord::query()->medsOnly()->with(['dispenseItems.medicine:id,name,generic_name,brand_name', 'recorder']);
    }

    public function columns(): array
    {
        return [
            Column::make('Date', 'bill_date')
                ->sortable()
                ->format(fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('M d, Y') : 'N/A'),
            Column::make('Name', 'patient_name')
                ->sortable()
                ->searchable(),
            Column::make('Course', 'course')
                ->sortable()
                ->searchable()
                ->format(fn ($value) => $value ?: '—'),
            Column::make('Year', 'year_level')
                ->sortable()
                ->format(fn ($value) => $value ?: '—'),
            Column::make('College', 'college')
                ->sortable()
                ->searchable()
                ->format(fn ($value) => $value ?: '—'),
            Column::make('Drugs')
                ->label(fn ($row) => e($row->medicineSummary()))
                ->html()
                ->searchable(function (Builder $query, $word) {
                    $query->whereHas('dispenseItems.medicine', fn ($medicine) => $medicine->where(
                        fn ($group) => SearchTerm::wordInColumns($group, $word, ReportQueries::medicineSearchColumns())
                    ));
                }),
            Column::make('Recorded by')
                ->label(fn ($row) => e($row->recorder?->full_name ?? 'N/A')),
            Column::make(__('messages.common.action'))
                ->label(fn ($row) => view('meds_only.columns.action', ['row' => $row]))
                ->html(),
        ];
    }
}
