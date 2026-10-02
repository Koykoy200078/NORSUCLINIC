<?php

namespace App\Support;

use Closure;

/**
 * Turns what a person types into a search box into LIKE conditions that find what they meant.
 *
 *  - "Ana Reyes" and "Reyes, Ana" are two words; a row matches when EVERY word is found in at least one of the
 *    searched columns. Matching the whole phrase against one column (what the search boxes did) can never find a
 *    full name stored in separate first / middle / last name columns.
 *  - * and % mean "any letters", ? and _ mean "one letter" (so "an*", "r?yes" and "a%s" work).
 *  - Case, extra spaces and accents are ignored (the database collation is accent- and case-insensitive).
 *  - A backslash is escaped, and the term is capped, so nothing typed can break or bloat the query.
 */
final class SearchTerm
{
    public const MAX_WORDS = 8;

    public const MAX_LENGTH = 100;

    /** The columns of a users row that identify a person (used inside whereHas('user')). */
    public const PERSON_COLUMNS = ['first_name', 'middle_name', 'last_name', 'email', 'university_id_number', 'employee_id'];

    /**
     * The words of a typed term, already escaped and with the wildcards converted, without the outer % signs.
     *
     * @return list<string>
     */
    public static function words(?string $term): array
    {
        $term = trim((string) $term);

        if ($term === '') {
            return [];
        }

        $term = mb_substr($term, 0, self::MAX_LENGTH);

        // "Reyes, Ana" and "Reyes,Ana": the comma only separates words.
        $term = str_replace(',', ' ', $term);

        $parts = preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $words = [];
        foreach (array_slice($parts, 0, self::MAX_WORDS) as $part) {
            // The backslash must be escaped first, before % and _ are introduced.
            $words[] = str_replace(['\\', '*', '?'], ['\\\\', '%', '_'], $part);
        }

        return $words;
    }

    /**
     * The LIKE pattern for one word: it may appear anywhere in the value.
     */
    public static function like(string $word): string
    {
        return '%' . $word . '%';
    }

    /**
     * Restrict $query to rows where every word of $term is found in at least one of $columns.
     *
     * A column is a plain column name, or a Closure `fn ($query, string $word)` that adds its own conditions
     * (for example a whereHas on a related table); it is placed inside an OR group, so it cannot leak into the
     * other columns' conditions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  array<int, string|Closure>  $columns
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function whereAllWords($query, ?string $term, array $columns)
    {
        foreach (self::words($term) as $word) {
            $query->where(fn ($group) => self::wordInColumns($group, $word, $columns));
        }

        return $query;
    }

    /**
     * Add "the word is found in this column" for each column, joined with OR, to $query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  array<int, string|Closure>  $columns
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function wordInColumns($query, string $word, array $columns)
    {
        foreach ($columns as $column) {
            if ($column instanceof Closure) {
                $query->orWhere(fn ($inner) => $column($inner, $word));

                continue;
            }

            $query->orWhere($column, 'like', self::like($word));
        }

        return $query;
    }
}
