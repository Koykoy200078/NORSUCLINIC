<?php

namespace App\Livewire;

use App\Livewire\Tables\ResetsPageOnEvent;
use App\Livewire\Tables\TableComponent;
use App\Models\StockOutView;
use App\Support\Tables\Column;
use Livewire\Attributes\Lazy;
use Illuminate\Database\Eloquent\Builder;

/**
 * Renamed from UsedMedicineTable → StockOutTable.
 * Displays stock-out records from the used_medicines_view DB view, on the Stock-out tab of the Medicine Dispensing screen
 * (the old Used Medicine address redirects there). Its copy UsedMedicineTable, without the Dosage column, was mounted only
 * by a view no route rendered any more; it was removed in the 2026-11-13 DRY pass (the owner: Dosage everywhere).
 */
#[Lazy]
class StockOutTable extends TableComponent
{
    protected $model = StockOutView::class;

    public bool $showFilterOnHeader = false;

    public bool $showButtonOnHeader = false;

    use ResetsPageOnEvent;

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('created_at', 'desc');
    }

    public function placeholder(): string
    {
        return view('livewire.used_medicine_skeleton')->render();
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
