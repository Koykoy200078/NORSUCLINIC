<?php

namespace App\Livewire;

use App\Livewire\Tables\ResetsPageOnEvent;
use App\Livewire\Tables\TableComponent;
use App\Models\State;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Tables\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class StateTable extends TableComponent
{
    protected $model = State::class;

    public bool $showButtonOnHeader = true;

    public string $buttonComponent = 'states.components.add_button';

    use ResetsPageOnEvent;

    public function configure(): void
    {
        $this->sortNewestFirst();
    }

    public function placeholder(): string
    {
        return view('livewire.staff_skeleton')->render();
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.common.name'), 'name')->view('states.components.name')
                ->sortable()
                ->searchable(),
            Column::make(__('messages.country.country'), 'country_id')->view('states.components.country')
                ->sortable()
                ->searchable(),
            Column::make(__('messages.common.action'), 'id')->view('states.components.action'),
        ];
    }

    public function builder(): Builder
    {
        return State::with('country')->select('states.*');
    }
}
