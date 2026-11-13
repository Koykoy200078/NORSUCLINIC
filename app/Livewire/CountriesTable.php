<?php

namespace App\Livewire;

use App\Livewire\Tables\ResetsPageOnEvent;
use App\Livewire\Tables\TableComponent;
use App\Models\Country;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Tables\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class CountriesTable extends TableComponent
{
    protected $model = Country::class;

    public bool $showButtonOnHeader = true;

    public string $buttonComponent = 'countries.components.add_button';

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
            Column::make(__('messages.common.name'), 'name')->view('countries.components.name')
                ->searchable()
                ->sortable(),
            Column::make(__('messages.country.short_code'), 'short_code')->view('countries.components.short_code')
                ->sortable()
                ->searchable(),
            Column::make(__('messages.common.action'), 'id')->view('countries.components.action'),
        ];
    }

    public function builder(): Builder
    {
        return Country::query();
    }
}
