<?php

namespace App\Support\Tables;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * One column of a list screen (App\Livewire\Tables\TableComponent).
 *
 * Column::make('Name', 'name')                      a column of the list's own table
 * Column::make('City', 'city.name')                 a column of a related table: the list joins it for sorting and searching
 * Column::make('Actions')->label(fn ($row) => ...)  a column that only shows something computed from the row
 *
 * ->view('x.y') draws the cell with a Blade view that gets $value, $row and $column; ->format(fn ($value, $row, $column))
 * draws it with a callback (the text is escaped unless ->html() is called); ->sortable() / ->searchable() take an
 * optional callback of their own; ->hideIf($condition) keeps the column out of the screen but still in the query.
 */
class Column
{
    protected string $title;

    protected ?string $from = null;

    protected ?string $field = null;

    /** @var array<int, string> the relations to join, outermost first: 'patient.user.first_name' is ['patient', 'user'] */
    protected array $relations = [];

    /** the table (or the join alias) the field lives in, set by the list once its query is known */
    protected ?string $table = null;

    protected bool $sortable = false;

    /** @var (callable(\Illuminate\Database\Eloquent\Builder, string): mixed)|null */
    protected $sortCallback = null;

    protected bool $searchable = false;

    /** @var (callable(\Illuminate\Database\Eloquent\Builder, string): mixed)|null */
    protected $searchCallback = null;

    protected bool $hidden = false;

    /** @var (callable(mixed, Model, Column): mixed)|null */
    protected $formatCallback = null;

    /** @var (callable(Model, Column): mixed)|null */
    protected $labelCallback = null;

    protected bool $html = false;

    public function __construct(string $title, ?string $from = null)
    {
        $this->title = trim($title);

        if ($from) {
            $this->from = trim($from);
            if (Str::contains($this->from, '.')) {
                $this->field = Str::afterLast($this->from, '.');
                $this->relations = explode('.', Str::beforeLast($this->from, '.'));
            } else {
                $this->field = $this->from;
            }
        } else {
            $this->field = Str::snake($title);
        }
    }

    public static function make(string $title, ?string $from = null): static
    {
        return new static($title, $from);
    }

    // ---- how the column is declared -------------------------------------------------------------------------

    /** A column with no field of its own: what it shows is computed from the row. */
    public function label(callable $callback): static
    {
        $this->from = null;
        $this->field = null;
        $this->labelCallback = $callback;

        return $this;
    }

    public function sortable(?callable $callback = null): static
    {
        $this->sortable = true;
        $this->sortCallback = $callback;

        return $this;
    }

    public function searchable(?callable $callback = null): static
    {
        $this->searchable = true;
        $this->searchCallback = $callback;

        return $this;
    }

    public function hideIf($condition): static
    {
        $this->hidden = (bool) $condition;

        return $this;
    }

    public function format(callable $callback): static
    {
        $this->formatCallback = $callback;

        return $this;
    }

    public function view(string $view): static
    {
        return $this->format(fn ($value, $row, Column $column) => view($view)->withValue($value)->withRow($row)->withColumn($column));
    }

    /** The cell's text is HTML that is already safe to print. */
    public function html(): static
    {
        $this->html = true;

        return $this;
    }

    public function setTable(?string $table): static
    {
        $this->table = $table;

        return $this;
    }

    // ---- what the list asks of it ----------------------------------------------------------------------------

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return Str::slug($this->title);
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function isField(string $field): bool
    {
        return $this->field === $field;
    }

    public function hasField(): bool
    {
        return $this->field !== null;
    }

    public function isLabel(): bool
    {
        return $this->from === null && $this->field === null;
    }

    /** @return Collection<int, string> */
    public function getRelations(): Collection
    {
        return collect($this->relations);
    }

    public function hasRelations(): bool
    {
        return $this->relations !== [];
    }

    public function getRelationString(): ?string
    {
        return $this->hasRelations() ? implode('.', $this->relations) : null;
    }

    public function isBaseColumn(): bool
    {
        return ! $this->hasRelations() && $this->hasField();
    }

    public function getTable(): ?string
    {
        return $this->table;
    }

    /** The column as the query names it: "cities.name", or "city.name" when the table is joined under an alias. */
    public function getColumn(): string
    {
        return $this->table . '.' . $this->field;
    }

    /** The name the list selects it under and sorts by: "name", or "city.name" for a related column. */
    public function getColumnSelectName(): ?string
    {
        return $this->isBaseColumn() ? $this->field : $this->getRelationString() . '.' . $this->field;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function isSortable(): bool
    {
        return $this->hasField() && $this->sortable;
    }

    public function hasSortCallback(): bool
    {
        return $this->sortCallback !== null;
    }

    public function getSortCallback(): ?callable
    {
        return $this->sortCallback;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function hasSearchCallback(): bool
    {
        return $this->searchCallback !== null;
    }

    public function getSearchCallback(): ?callable
    {
        return $this->searchCallback;
    }

    public function isHtml(): bool
    {
        return $this->html;
    }

    // ---- the cell ---------------------------------------------------------------------------------------------

    public function getValue(Model $row): mixed
    {
        return $this->isBaseColumn() ? $row->{$this->field} : $row->{$this->getRelationString() . '.' . $this->field};
    }

    public function renderContents(Model $row): mixed
    {
        if ($this->isLabel()) {
            $value = call_user_func($this->labelCallback, $row, $this);

            return $this->html ? new HtmlString($value) : $value;
        }

        $value = $this->getValue($row);

        if ($this->formatCallback !== null) {
            $value = call_user_func($this->formatCallback, $value, $row, $this);

            return $this->html ? new HtmlString($value) : $value;
        }

        return $value;
    }
}
