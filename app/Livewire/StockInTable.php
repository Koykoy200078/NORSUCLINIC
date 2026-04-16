<?php

namespace App\Livewire;

use App\Models\StockIn;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Renamed from MedicineAvailabilityTable → StockInTable.
 * NO #[Lazy] — this component is mounted inside the tabbed MedicineScreen
 * which uses x-show, so eager loading is correct here.
 */
class StockInTable extends LivewireTableComponent
{
    protected $model = StockIn::class;

    public bool $showButtonOnHeader = true;

    public bool $showFilterOnHeader = false;

    public bool $paginationIsEnabled = true;

    public string $buttonComponent = 'medicine-availabilities.action';

    protected $listeners = ['refresh' => '$refresh', 'changeFilter', 'resetPage'];

    public function configure(): void
    {
        $this->setQueryStringStatus(false);
        $this->setDefaultSort('medicine_availabilities.created_at', 'desc');
        $this->setPrimaryKey('id');

        $this->setThAttributes(function (Column $column) {
            if ($column->isField('id')) {
                return ['class' => 'text-center'];
            }
            return [];
        });
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.medicine_availability.availability_number'), 'availability_no')
                ->sortable()->searchable()->view('medicine-availabilities.columns.availability_number'),
            Column::make(__('messages.medicine_availability.total_medicines'), 'id')
                ->view('medicine-availabilities.columns.total_medicines'),
            Column::make(__('messages.common.action'), 'id')
                ->view('medicine-availabilities.columns.action'),
        ];
    }
}
