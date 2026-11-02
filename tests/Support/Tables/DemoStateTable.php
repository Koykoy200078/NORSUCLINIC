<?php

namespace Tests\Support\Tables;

use App\Livewire\Tables\TableComponent;
use App\Models\State;
use App\Support\Tables\Column;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Lazy;

/**
 * A list screen used only by TableComponentTest: the states, with their country joined for sorting and searching.
 * What is switched on is chosen through the mount parameters, so each test shows one behaviour of the base.
 */
#[Lazy]
class DemoStateTable extends TableComponent
{
    /** @var array<int, string> what the test asks the screen to switch on: hidden, callback, attributes, additional, named */
    public array $features = [];

    public bool $showButtonOnHeader = false;

    public string $buttonComponent = 'table-demo-button';

    public bool $showFilterOnHeader = false;

    public array $FilterComponent = ['table-demo-filter', 'Region', 'Island'];

    /** @var array<int, array{int, int}> every (column index, row index) a td callback was asked about */
    public static array $tdCalls = [];

    public function configure(): void
    {
        $this->setPrimaryKey('id');

        if (! in_array('no-default-sort', $this->features, true)) {
            $this->setDefaultSort('states.created_at', 'desc');
        }

        if (in_array('named', $this->features, true)) {
            $this->tableName = 'states';
        }

        if (in_array('attributes', $this->features, true)) {
            $this->setThAttributes(fn (Column $column) => $column->isField('name') ? ['class' => 'text-center'] : []);
            $this->setTdAttributes(function (Column $column, Model $row, int $colIndex, int $rowIndex) {
                self::$tdCalls[] = [$colIndex, $rowIndex];

                return $column->isField('name') ? ['class' => 'pt-5'] : [];
            });
            $this->setTableAttributes(['id' => 'demo-table', 'class' => 'extra-table-class']);
        }

        if (in_array('additional', $this->features, true)) {
            $this->setAdditionalSelects(['states.country_id as demo_country_id']);
        }

        if (in_array('empty-message', $this->features, true)) {
            $this->setEmptyMessage('Nothing here yet');
        }
    }

    public function columns(): array
    {
        $columns = [
            Column::make('Name', 'name')->sortable()->searchable(),
            Column::make('Country', 'country.name')->sortable()->searchable(),
        ];

        if (in_array('hidden', $this->features, true)) {
            // searchable, selected, but not drawn
            $columns[] = Column::make('Id', 'id')->sortable()->searchable()->hideIf(1);
        }

        if (in_array('callback', $this->features, true)) {
            $columns[] = Column::make('Size')
                ->label(fn ($row) => strlen($row->name))
                ->sortable(fn (Builder $query, string $direction) => $query->orderByRaw('LENGTH(states.name) ' . $direction));
        }

        if (in_array('cells', $this->features, true)) {
            $columns[] = Column::make('Escaped', 'name')->format(fn ($value) => '<b>' . $value . '</b>');
            $columns[] = Column::make('Html', 'name')->format(fn ($value) => '<b>' . $value . '</b>')->html();
            $columns[] = Column::make('View', 'name')->view('table-demo-cell');
        }

        return $columns;
    }

    public function builder(): Builder
    {
        return State::with('country')->select('states.*');
    }
}
