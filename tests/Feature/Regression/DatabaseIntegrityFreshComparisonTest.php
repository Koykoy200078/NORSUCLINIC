<?php

namespace Tests\Feature\Regression;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Plan Phase 1.5: `php artisan db:integrity --compare-fresh` builds a fresh install in a throwaway schema and compares.
 *
 * The suite's own schema was just built by the migrations, so it must be identical to a fresh install. This class does
 * not wrap its tests in a transaction: inside one, MySQL's repeatable-read snapshot would hide the tables of the
 * throwaway schema (created by another connection) from the information_schema reads. The command itself always runs
 * without an outer transaction.
 */
class DatabaseIntegrityFreshComparisonTest extends TestCase
{
    use RefreshDatabase;

    protected $connectionsToTransact = [];

    public function test_a_correct_schema_is_identical_to_a_fresh_install(): void
    {
        $this->artisan('db:integrity', ['--compare-fresh' => true])
            ->expectsOutputToContain('Structure is identical to a fresh install')
            ->assertSuccessful();
    }
}
