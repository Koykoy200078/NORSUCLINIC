<?php

namespace App\Livewire;

use App\Models\UsedMedicineView;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

#[Lazy]
class UsedMedicineTable extends LivewireTableComponent
{
    protected $model = UsedMedicineView::class;

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
            Column::make('Id', 'id')
                ->sortable()->hideIf(1),
            Column::make(__('messages.medicines'), 'medicine_name')
                ->sortable()->searchable(),
            Column::make(__('messages.used_medicine.used_quantity'), 'quantity')
                ->sortable()->searchable(),
            Column::make(__('messages.used_medicine.used_at'), 'source')
                ->sortable(),
            Column::make('Patient', 'patient_name')
                ->sortable()->searchable(),
            Column::make('Nurse In Charge', 'nurse_incharged')
                ->sortable()->searchable(),
            Column::make(__('messages.appointment.date'), 'created_at')
                ->sortable()->searchable(),
        ];
    }

    public function builder(): Builder
    {
        return UsedMedicineView::query();
    }
}
