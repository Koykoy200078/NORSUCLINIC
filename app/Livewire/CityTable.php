<?php

namespace App\Livewire;

use App\Livewire\Tables\ResetsPageOnEvent;
use App\Livewire\Tables\TableComponent;
use App\Models\City;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Tables\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class CityTable extends TableComponent
{
    protected $model = City::class;

    public bool $showButtonOnHeader = true;

    public string $buttonComponent = 'cities.components.add_button';

    use ResetsPageOnEvent;

    public function configure(): void
    {
        $this->sortNewestFirst();
    }

    public function placeholder(): string
    {
        return view('livewire.staff_skeleton')->render();
    }

    public function builder(): Builder
    {
        return City::with('state')->select('cities.*');
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.common.name'), 'name')->view('cities.components.name')
                ->sortable()->searchable(),
            Column::make('Type', 'type')->view('cities.components.type')
                ->sortable()->searchable(),
            Column::make(__('messages.city.province'), 'state.name')->view('cities.components.state')
                ->sortable()->searchable(),
            Column::make(__('messages.common.action'), 'id')->view('cities.components.action'),
        ];
    }
}
