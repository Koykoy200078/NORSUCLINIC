<?php

namespace Tests\Unit;

use App\Support\SearchTerm;
use PHPUnit\Framework\TestCase;

/**
 * How a typed search is turned into LIKE patterns (2026-10-01 report): a full name must be split into words,
 * * and ? must work as wildcards, and nothing typed may break the query.
 */
class SearchTermTest extends TestCase
{
    public function test_a_full_name_becomes_one_pattern_per_word(): void
    {
        $this->assertSame(['Ana', 'Reyes'], SearchTerm::words('Ana Reyes'));
        $this->assertSame(['Ana', 'Maria', 'Reyes'], SearchTerm::words('Ana Maria Reyes'));
    }

    public function test_last_name_first_with_a_comma_gives_the_same_words(): void
    {
        $this->assertEqualsCanonicalizing(['Ana', 'Reyes'], SearchTerm::words('Reyes, Ana'));
        $this->assertEqualsCanonicalizing(['Dela', 'Cruz', 'Carlo'], SearchTerm::words('Dela Cruz,Carlo'));
    }

    public function test_extra_spaces_and_tabs_are_ignored(): void
    {
        $this->assertSame(['ana', 'reyes'], SearchTerm::words("  ana \t  reyes \n"));
    }

    public function test_star_and_percent_mean_any_letters_and_question_mark_and_underscore_mean_one_letter(): void
    {
        $this->assertSame(['an%'], SearchTerm::words('an*'));
        $this->assertSame(['a%s'], SearchTerm::words('a%s'));
        $this->assertSame(['r_yes'], SearchTerm::words('r?yes'));
        $this->assertSame(['r_yes'], SearchTerm::words('r_yes'));
        $this->assertSame(['a%a', 'rey%'], SearchTerm::words('a*a rey*'));
    }

    public function test_a_backslash_is_escaped_so_it_cannot_break_the_pattern(): void
    {
        $this->assertSame(['a\\\\b'], SearchTerm::words('a\\b'));
    }

    public function test_empty_input_gives_no_words(): void
    {
        $this->assertSame([], SearchTerm::words(null));
        $this->assertSame([], SearchTerm::words(''));
        $this->assertSame([], SearchTerm::words("   \t "));
        $this->assertSame([], SearchTerm::words(',,'));
    }

    public function test_the_term_is_limited_so_a_pasted_paragraph_cannot_build_a_giant_query(): void
    {
        $words = SearchTerm::words(str_repeat('abc ', 50));
        $this->assertLessThanOrEqual(8, count($words));

        $long = SearchTerm::words(str_repeat('x', 500));
        $this->assertLessThanOrEqual(100, strlen($long[0]));
    }

    public function test_like_wraps_a_word_so_it_matches_anywhere_in_the_value(): void
    {
        $this->assertSame('%ana%', SearchTerm::like('ana'));
        $this->assertSame('%r_yes%', SearchTerm::like('r_yes'));
    }
}
