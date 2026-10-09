<?php

namespace App\Livewire;

use App\Models\DispenseHistoryEntry;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Lazy;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Renamed from MedicineBillTable → MedicineDispenseTable.
 * Displays the dispensing history (Tab 4 of MedicineScreen).
 *
 * Reads `dispense_history_view` (plan Phase 2): dispense records, dispensed prescriptions AND the medicines
 * recorded inside consultations, told apart by the Source column and the Source filter in the header.
 */
#[Lazy]
class MedicineDispenseTable extends LivewireTableComponent
{
    public bool $showButtonOnHeader = true;

    public string $buttonComponent = 'medicine-history.add-button';

    public bool $showFilterOnHeader = true;

    public array $FilterComponent = ['medicine-history.components.source_filter', DispenseHistoryEntry::SOURCES];

    public string $sourceFilter = '';

    protected $listeners = ['refresh' => '$refresh', 'resetPage'];

    protected $model = DispenseHistoryEntry::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('dispensed_at', 'desc');

        $this->setThAttributes(function (Column $column) {
            if ($column->isField('id')) {
                return ['class' => 'text-center ml-5'];
            }

            return [];
        });
    }

    public function placeholder(): string
    {
        return view('livewire.loading_skeleton')->render();
    }

    public function columns(): array
    {
        return [
            Column::make('Dispense ID', 'history_number')
                ->sortable()->searchable()
                ->view('medicine-history.columns.bill_id'),
            Column::make('Source', 'source')
                ->sortable()->searchable()
                ->view('medicine-history.columns.source'),
            Column::make('Dispensed At', 'dispensed_at')
                ->sortable()->searchable()
                ->view('medicine-history.columns.bill_date'),
            Column::make(__('messages.prescription.patient'), 'patient_id')->hideIf(1),
            Column::make(__('messages.prescription.patient'), 'patient_name')
                ->sortable()
                ->searchable(function (Builder $query, $word) {
                    $query->where(fn (Builder $person) => $person
                        ->where('patient_name', 'like', \App\Support\SearchTerm::like($word))
                        ->orWhere('patient_email', 'like', \App\Support\SearchTerm::like($word)));
                })->view('medicine-history.columns.patient'),
            Column::make('Doctor / Recorded by', 'given_by')
                ->sortable()
                ->searchable(function (Builder $query, $word) {
                    $query->where(fn (Builder $person) => $person
                        ->where('given_by', 'like', \App\Support\SearchTerm::like($word))
                        ->orWhere('given_by_email', 'like', \App\Support\SearchTerm::like($word)));
                })->view('medicine-history.columns.doctor'),
            Column::make('Quantity Dispensed', 'quantity')
                ->sortable()
                ->view('medicine-history.columns.dispensed_quantity'),
            Column::make(__('messages.common.action'), 'record_id')
                ->view('medicine-history.columns.action'),
        ];
    }

    public function builder(): Builder
    {
        $query = DispenseHistoryEntry::query()
            ->select('dispense_history_view.*')
            ->with(['patient:id,user_id', 'patient.user:id,gender']);

        if (in_array($this->sourceFilter, DispenseHistoryEntry::SOURCES, true)) {
            $query->where('dispense_history_view.source', $this->sourceFilter);
        }

        return $query;
    }

    public function updatedSourceFilter(): void
    {
        $this->setBuilder($this->builder());
        $this->resetPage();
    }
}
