<?php

namespace Tests\Support\Tables;

use App\Livewire\Tables\TableComponent;
use App\Models\State;
use App\Support\Tables\Column;

/** A list that only names its model (no builder()) and switches its search off: used by TableComponentTest. */
class DemoModelOnlyTable extends TableComponent
{
    protected $model = State::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id')->setDefaultSort('name', 'asc')->setSearchDisabled();
    }

    public function columns(): array
    {
        return [Column::make('Name', 'name')->sortable()->searchable()];
    }
}
