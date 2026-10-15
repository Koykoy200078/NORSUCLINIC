<?php

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Ends every list's ORDER BY with the row's own unique key.
 *
 * Plan Phase 5, Pass A item 4. When the sort value repeats (a date, a name, a status) MySQL orders the tied rows
 * differently for each page query, so one page repeats rows that another page never shows (reproduced: 60 medicines
 * with the same date showed the same 10 rows on all six pages). A list that nobody sorted gets a stable order too:
 * the newest record first.
 *
 * A table that orders the rows itself with its own unique keys (Dispense History) overrides tieBreakerColumn() to
 * return null.
 */
trait SortsByUniqueKey
{
    public function applySorting(): Builder
    {
        $builder = parent::applySorting();

        $key = $this->tieBreakerColumn($builder);
        if ($key !== null) {
            $this->setBuilder($builder->orderBy($key, 'desc'));
        }

        return $this->getBuilder();
    }

    /** The model's own key, qualified with its table (a joined list has more than one `id`); null when it has none. */
    protected function tieBreakerColumn(Builder $builder): ?string
    {
        $model = $builder->getModel();

        return $model->getKeyName() ? $model->getQualifiedKeyName() : null;
    }
}
