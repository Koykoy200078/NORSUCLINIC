<?php

namespace Tests\Support\Tables;

use App\Livewire\Tables\TableComponent;
use App\Support\Tables\Column;

/** A list that says neither which model nor which query it shows: it must refuse to draw. Used by TableComponentTest. */
class DemoNoQueryTable extends TableComponent
{
    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public function columns(): array
    {
        return [Column::make('Name', 'name')];
    }
}
