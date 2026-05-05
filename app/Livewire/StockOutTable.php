<?php

namespace App\Livewire;

use App\Models\StockOutView;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Livewire\Attributes\Lazy;
use Illuminate\Database\Eloquent\Builder;

/**
 * Renamed from UsedMedicineTable → StockOutTable.
 * Displays stock-out records from the used_medicines_view DB view.
 */
#[Lazy]
class StockOutTable extends LivewireTableComponent
{
    protected $model = StockOutView::class;

    public bool $showFilterOnHeader = false;

    public bool $showButtonOnHeader = false;

    protected $listeners = ['refresh' => '$refresh', 'resetPage'];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('created_at', 'desc')
            ->setQueryStringStatus(false);
    }

    public function placeholder()
    {
        return view('livewire.used_medicine_skeleton');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id')->sortable()->hideIf(1),
            Column::make(__('messages.medicines'), 'medicine_name')->sortable()->searchable(),
            Column::make(__('messages.medicine_availability.dosage'), 'dosage')->sortable()->searchable(),
            Column::make(__('messages.used_medicine.used_quantity'), 'quantity')->sortable()->searchable(),
            Column::make(__('messages.used_medicine.used_at'), 'source')->sortable(),
            Column::make('Patient', 'patient_name')->sortable()->searchable(),
            Column::make('Nurse In Charge', 'nurse_incharged')->sortable()->searchable(),
            Column::make(__('messages.common.date'), 'created_at')->sortable()->searchable(),
        ];
    }

    public function builder(): Builder
    {
        return StockOutView::query();
    }
}
