<?php

namespace App\Livewire;

use App\Models\Barangay;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class BarangayTable extends LivewireTableComponent
{
    protected $model = Barangay::class;

    public bool $showButtonOnHeader = true;

    public string $buttonComponent = 'barangays.components.add_button';

    protected $listeners = ['refresh' => '$refresh', 'resetPage'];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('created_at', 'desc')
            ->setQueryStringStatus(false);

        $this->setThAttributes(function (Column $column) {
            if ($column->isField('id')) {
                return [
                    'class' => 'text-center',
                ];
            }

            return [];
        });
    }

    public function placeholder()
    {
        return view('livewire.staff_skeleton');
    }

    public function builder(): Builder
    {
        return Barangay::with('city.state')->select('barangays.*');
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.common.name'), 'name')->view('barangays.components.name')
                ->sortable()->searchable(),
            Column::make(__('messages.barangay.city'), 'city.name')->view('barangays.components.city')
                ->sortable()->searchable(),
            Column::make(__('messages.common.action'), 'id')->view('barangays.components.action'),
        ];
    }
}
