<?php

namespace App\Livewire\Concerns;

use App\Support\SearchTerm;
use Illuminate\Database\Eloquent\Builder;

/**
 * Replaces the table library's search, which ran ONE "LIKE %whole phrase%" per column.
 *
 * With that, "Ana Reyes" could never match a patient stored as first name "Ana" + middle name "Maria" + last name
 * "Reyes", "Reyes, Ana" matched nothing, and the wildcards people type (* and ?) were just letters.
 *
 * Here the typed text is split into words (see SearchTerm). A row matches when EVERY word is found in at least one
 * searchable column. A column with its own search callback receives ONE word at a time and adds its conditions
 * to a private OR group, so it cannot disturb the other columns.
 */
trait SearchesByWords
{
    public function applySearch(): Builder
    {
        if ($this->searchIsEnabled() && $this->hasSearch()) {
            $columns = $this->getSearchableColumns();
            $words = SearchTerm::words($this->getSearch());

            if ($columns->count() && $words !== []) {
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
        }

        return $this->getBuilder();
    }
}
