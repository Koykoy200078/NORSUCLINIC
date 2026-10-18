<?php

namespace App\Livewire;

use App\Models\StockIn;
use App\Support\SearchTerm;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Renamed from MedicineAvailabilityTable → StockInTable.
 * NO #[Lazy] — this component is mounted inside the tabbed MedicineScreen
 * which uses x-show, so eager loading is correct here.
 */
class StockInTable extends LivewireTableComponent
{
    protected $model = StockIn::class;

    protected string $tableName = 'stock-in-table';

    public bool $showButtonOnHeader = true;

    public bool $showFilterOnHeader = false;

    public bool $paginationIsEnabled = true;

    public string $buttonComponent = 'medicine-availabilities.action';

    protected $listeners = ['refresh' => '$refresh', 'changeFilter', 'clearSearch', 'resetPage'];

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

    public function builder(): Builder
    {
        // The "N medicines" badge counts each row's lines: load them with the list, not one query per row.
        return StockIn::query()->with('purchasedMedcines');
    }

    public function columns(): array
    {
        return [
            // The search finds a stock-in by its number or by a medicine (name, brand, generic) that is in it.
            Column::make(__('messages.medicine_availability.availability_number'), 'availability_no')
                ->sortable()
                ->searchable(function (Builder $query, $word) {
                    $like = SearchTerm::like($word);
                    $query->where('medicine_availabilities.availability_no', 'like', $like)
                        ->orWhereHas('purchasedMedcines.medicines', function (Builder $medicine) use ($like) {
                            $medicine->where(function (Builder $names) use ($like) {
                                $names->where('medicines.name', 'like', $like)
                                    ->orWhere('medicines.brand_name', 'like', $like)
                                    ->orWhere('medicines.generic_name', 'like', $like);
                            });
                        });
                })
                ->view('medicine-availabilities.columns.availability_number'),
            Column::make(__('messages.medicine_availability.total_medicines'), 'id')
                ->view('medicine-availabilities.columns.total_medicines'),
            Column::make(__('messages.common.action'), 'id')
                ->view('medicine-availabilities.columns.action'),
        ];
    }
}
