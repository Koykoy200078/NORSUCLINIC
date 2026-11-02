<?php

namespace App\Livewire\Tables;

use App\Support\SearchTerm;
use App\Support\Tables\Column;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Component;
use Livewire\WithPagination;
use LogicException;

/**
 * The base of every list screen: a searchable, sortable, paged table of the rows of one Eloquent query.
 *
 * A list extends it and declares what it shows:
 *
 *   configure()  sets the primary key, the default sort, the attributes of the th / td cells ...
 *   columns()    the Column objects (App\Support\Tables\Column)
 *   builder()    the Eloquent query of the rows
 *
 * It replaces rappasoft/laravel-livewire-tables, which it follows on purpose where the screens depend on it: the query
 * joins the related tables of the columns, selects each column under its own name, sorts by the column's select name and
 * puts the row's own key last (so a page never repeats or loses a row), and searches word by word (SearchTerm). The
 * markup is the one the old library drew (resources/views/livewire/tables/table.blade.php).
 *
 * A list that should load after the page (a skeleton first) declares #[Lazy] on its own class: Livewire reads the
 * attributes of the class itself, not of its parents.
 */
abstract class TableComponent extends Component
{
    use WithPagination;

    public string $search = '';

    /** @var array<string, string> the sorted column's select name and its direction: at most one entry */
    public array $sorts = [];

    public int $perPage = 10;

    public string $tableName = 'table';

    public string $emptyMessage = 'No data available in table';

    /** a "Filter" box in the header of the list: [view, label, optional second label] */
    public bool $showFilterOnHeader = false;

    /** an "Add ..." button in the header of the list: the view that draws it */
    public bool $showButtonOnHeader = false;

    public string $buttonComponent = '';

    protected $listeners = ['refresh' => '$refresh'];

    /** @var class-string<Model>|null */
    protected $model = null;

    protected string $paginationTheme = 'bootstrap';

    /** @var array<int, int> */
    protected array $perPageAccepted = [10, 25, 50];

    protected string $primaryKey = 'id';

    protected bool $searchEnabled = true;

    protected ?string $defaultSortColumn = null;

    protected string $defaultSortDirection = 'asc';

    /** @var array<int, string> */
    protected array $additionalSelects = [];

    /** @var array<string, mixed> */
    protected array $tableAttributes = [];

    /** @var (callable(Column): array<string, mixed>)|null */
    protected $thAttributesCallback = null;

    /** @var (callable(Column, Model, int, int): array<string, mixed>)|null */
    protected $tdAttributesCallback = null;

    protected ?Builder $builder = null;

    /** @var Collection<int, Column> */
    protected Collection $columns;

    // ---- what a list declares -------------------------------------------------------------------------------

    abstract public function configure(): void;

    /** @return array<int, Column> */
    abstract public function columns(): array;

    /** The rows of the list. A list with nothing special to say names its model ($model) and gets all of its rows. */
    public function builder(): Builder
    {
        if ($this->model === null) {
            throw new LogicException(static::class . ' must name its $model or declare builder().');
        }

        return $this->model::query();
    }

    protected function setPrimaryKey(string $key): static
    {
        $this->primaryKey = $key;

        return $this;
    }

    /** A list nobody needs to search (the home page sliders): no search box, and a typed search finds nothing to filter. */
    protected function setSearchDisabled(): static
    {
        $this->searchEnabled = false;

        return $this;
    }

    public function searchIsEnabled(): bool
    {
        return $this->searchEnabled;
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    protected function setDefaultSort(string $column, string $direction = 'asc'): static
    {
        $this->defaultSortColumn = $column;
        $this->defaultSortDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $this;
    }

    /** @param array<int, string> $selects extra "table.column" selects a cell view needs and no column names */
    protected function setAdditionalSelects(array $selects): static
    {
        $this->additionalSelects = $selects;

        return $this;
    }

    /** @param callable(Column): array<string, mixed> $callback */
    protected function setThAttributes(callable $callback): static
    {
        $this->thAttributesCallback = $callback;

        return $this;
    }

    /** @param callable(Column, Model, int, int): array<string, mixed> $callback */
    protected function setTdAttributes(callable $callback): static
    {
        $this->tdAttributesCallback = $callback;

        return $this;
    }

    /** @param array<string, mixed> $attributes */
    protected function setTableAttributes(array $attributes): static
    {
        $this->tableAttributes = $attributes;

        return $this;
    }

    protected function setEmptyMessage(string $message): static
    {
        $this->emptyMessage = $message;

        return $this;
    }

    protected function setBuilder(Builder $builder): static
    {
        $this->builder = $builder;

        return $this;
    }

    protected function getBuilder(): Builder
    {
        return $this->builder;
    }

    // ---- the life of the screen -----------------------------------------------------------------------------

    /** After the mount parameters are set (and on every later request): the list's own set-up, its query and its columns. */
    public function booted(): void
    {
        $this->configure();
        $this->setBuilder($this->builder());
        $this->setUpColumns();
    }

    public function placeholder(): string
    {
        return view('livewire.loading_skeleton')->render();
    }

    public function render()
    {
        return view('livewire.tables.table', [
            'rows' => $this->getRows(),
            'columns' => $this->columns,
        ]);
    }

    public function refreshDataTable(): void
    {
        $this->dispatch('refresh');
    }

    public function updatedSearch(): void
    {
        $this->resetComputedPage();
    }

    /** A per-page value that is not offered (a hand-edited request) falls back to the first one. */
    public function updatedPerPage($value): void
    {
        if (! in_array((int) $value, $this->perPageAccepted, true)) {
            $this->perPage = $this->perPageAccepted[0];
        }

        $this->resetComputedPage();
    }

    public function getPerPageAccepted(): array
    {
        return $this->perPageAccepted;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    /** A list with a table name of its own pages through "<name>Page", so two lists on one page keep their own page. */
    public function getComputedPageName(): string
    {
        return $this->tableName === 'table' ? 'page' : $this->tableName . 'Page';
    }

    protected function resetComputedPage(): void
    {
        $this->resetPage($this->getComputedPageName());
    }

    public function getEmptyMessage(): string
    {
        return __($this->emptyMessage);
    }

    public function getColspanCount(): int
    {
        return 100;
    }

    // ---- sorting ---------------------------------------------------------------------------------------------

    /** Ascending, then descending, then not sorted: one column at a time. */
    public function sortBy(string $columnSelectName): ?string
    {
        if ($this->sorts !== [] && ! isset($this->sorts[$columnSelectName])) {
            $this->sorts = [];
            $this->resetComputedPage();
        }

        if (! isset($this->sorts[$columnSelectName])) {
            $this->resetComputedPage();

            return $this->sorts[$columnSelectName] = 'asc';
        }

        if ($this->sorts[$columnSelectName] === 'asc') {
            $this->resetComputedPage();

            return $this->sorts[$columnSelectName] = 'desc';
        }

        unset($this->sorts[$columnSelectName]);

        return null;
    }

    public function getSort(string $columnSelectName): ?string
    {
        return $this->sorts[$columnSelectName] ?? null;
    }

    /** The default order (or the one the user chose), then the row's own key last, so a page never repeats or loses a row. */
    public function applySorting(): Builder
    {
        if ($this->defaultSortColumn !== null && $this->sorts === []) {
            $this->setBuilder($this->getBuilder()->orderBy($this->defaultSortColumn, $this->defaultSortDirection));
        } else {
            foreach ($this->sorts as $name => $direction) {
                $direction = $direction === 'desc' ? 'desc' : 'asc';
                $column = $this->columns->first(fn (Column $column) => $column->getColumnSelectName() === $name);

                if ($column === null) {
                    // a column without a field of its own is sorted by its callback, under its slug
                    $byCallback = $this->columns->first(fn (Column $column) => $column->getSlug() === $name && $column->hasSortCallback());
                    if ($byCallback !== null) {
                        $this->setBuilder(call_user_func($byCallback->getSortCallback(), $this->getBuilder(), $direction));
                    }

                    continue;
                }

                if (! $column->isSortable()) {
                    continue;
                }

                if ($column->hasSortCallback()) {
                    $this->setBuilder(call_user_func($column->getSortCallback(), $this->getBuilder(), $direction));
                } elseif ($column->isBaseColumn()) {
                    $this->setBuilder($this->getBuilder()->orderBy($column->getColumnSelectName(), $direction));
                } else {
                    // a related column is selected under its own name ("city.name"): sort by that name
                    $alias = $this->getBuilder()->getGrammar()->wrap($column->getColumn() . ' as ' . $column->getColumnSelectName());
                    $this->setBuilder($this->getBuilder()->orderByRaw(preg_split('/\s+as\s+/i', $alias)[1] . ' ' . $direction));
                }
            }
        }

        $key = $this->tieBreakerColumn($this->getBuilder());
        if ($key !== null) {
            $this->setBuilder($this->getBuilder()->orderBy($key, 'desc'));
        }

        return $this->getBuilder();
    }

    /** The model's own key, qualified with its table (a joined list has more than one `id`); null when it has none. */
    protected function tieBreakerColumn(Builder $builder): ?string
    {
        $model = $builder->getModel();

        return $model->getKeyName() ? $model->getQualifiedKeyName() : null;
    }

    // ---- searching -------------------------------------------------------------------------------------------

    /**
     * The typed text is split into words (SearchTerm). A row matches when EVERY word is found in at least one searchable
     * column, so "Ana Reyes" finds a patient stored as first name "Ana" + last name "Reyes". A column with a search
     * callback gets ONE word at a time and adds its conditions to a private OR group.
     */
    public function applySearch(): Builder
    {
        $words = SearchTerm::words(trim($this->search));
        $columns = $this->columns->filter(fn (Column $column) => $column->isSearchable() || $column->hasSearchCallback())->values();

        if ($this->searchEnabled && $columns->isNotEmpty() && $words !== []) {
            $this->setBuilder($this->getBuilder()->where(function ($query) use ($columns, $words) {
                foreach ($words as $word) {
                    $query->where(function ($group) use ($columns, $word) {
                        foreach ($columns as $column) {
                            if ($column->hasSearchCallback()) {
                                $callback = $column->getSearchCallback();
                                $group->orWhere(fn ($inner) => $callback($inner, $word));
                            } else {
                                $group->orWhere($column->getColumn(), 'like', SearchTerm::like($word));
                            }
                        }
                    });
                }
            }));
        }

        return $this->getBuilder();
    }

    // ---- the rows --------------------------------------------------------------------------------------------

    /** The current page of rows. The list's own query is left as it was, so this can be asked for more than once. */
    public function getRows(): LengthAwarePaginator
    {
        $original = $this->builder;
        $this->builder = clone $original;

        try {
            $this->joinRelations();
            $this->applySearch();
            $this->selectFields();
            $this->applySorting();

            return $this->getBuilder()->paginate($this->perPage, ['*'], $this->getComputedPageName());
        } finally {
            $this->builder = $original;
        }
    }

    /** Every related column ("city.name") is left-joined under the name of its relation, so it can be sorted and searched. */
    protected function joinRelations(): void
    {
        foreach ($this->columns as $column) {
            if (! $column->hasRelations()) {
                continue;
            }

            $alias = null;
            $parentAlias = null;
            $query = clone $this->getBuilder();

            foreach ($column->getRelations() as $i => $relationName) {
                $relation = $query->getRelation($relationName);
                $alias = $alias === null ? $relationName : $alias . '_' . $relationName;
                $related = $relation->getRelated()->getTable() . ' AS ' . $alias;

                if ($relation instanceof HasOne || $relation instanceof MorphOne) {
                    $this->join($related, $alias . '.' . $relation->getForeignKeyName(), $i === 0 ? $relation->getQualifiedParentKeyName() : $parentAlias . '.' . $relation->getLocalKeyName());
                } elseif ($relation instanceof BelongsTo) {
                    $this->join($related, $i === 0 ? $relation->getQualifiedForeignKeyName() : $parentAlias . '.' . $relation->getForeignKeyName(), $alias . '.' . $relation->getOwnerKeyName());
                }

                $parentAlias = $alias;
                $query = $relation->getQuery();
            }
        }
    }

    private function join(string $table, string $first, string $second): void
    {
        foreach ($this->getBuilder()->getQuery()->joins ?? [] as $join) {
            if ($join->table === $table) {
                return;
            }
        }

        $this->setBuilder($this->getBuilder()->leftJoin($table, $first, '=', $second));
    }

    /** Each column is selected under its own name, plus what the list asked for with setAdditionalSelects(). */
    protected function selectFields(): void
    {
        foreach ($this->additionalSelects as $select) {
            $this->setBuilder($this->getBuilder()->addSelect($select));
        }

        foreach ($this->columns->reject(fn (Column $column) => $column->isLabel()) as $column) {
            $this->setBuilder($this->getBuilder()->addSelect($column->getColumn() . ' as ' . $column->getColumnSelectName()));
        }
    }

    /** The table of each column is the list's own table, or the alias its relation was joined under. */
    private function setUpColumns(): void
    {
        $own = $this->getBuilder()->getModel()->getTable();

        $this->columns = collect($this->columns())
            ->filter(fn ($column) => $column instanceof Column)
            ->map(function (Column $column) use ($own) {
                if ($column->hasField()) {
                    $column->setTable($column->isBaseColumn() ? $own : str_replace('.', '_', $column->getRelationString()));
                }

                return $column;
            })
            ->values();
    }

    // ---- what the view asks ----------------------------------------------------------------------------------

    /** @return array<string, mixed> */
    public function getTableAttributes(): array
    {
        return $this->tableAttributes !== [] ? $this->tableAttributes : ['id' => 'table-' . $this->tableName, 'default' => true];
    }

    public function thAttributes(Column $column): ComponentAttributeBag
    {
        return new ComponentAttributeBag($this->thAttributesCallback ? call_user_func($this->thAttributesCallback, $column) : ['default' => true]);
    }

    public function tdAttributes(Column $column, Model $row, int $colIndex, int $rowIndex): ComponentAttributeBag
    {
        return new ComponentAttributeBag($this->tdAttributesCallback ? call_user_func($this->tdAttributesCallback, $column, $row, $colIndex, $rowIndex) : ['default' => true]);
    }
}
