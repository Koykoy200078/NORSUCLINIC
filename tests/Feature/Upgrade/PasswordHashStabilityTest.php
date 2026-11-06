<?php

namespace Tests\Feature\Upgrade;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Laravel 11 rehashes a password at sign-in when the stored hash was made with a different cost than the configured one.
 * The clinic's hashes use cost 10 and config/hashing.php defaults to 10, so nothing may change at sign-in; this test fails
 * if the default ever drifts (docs/laravel-13-upgrade-plan.md, finding C15).
 */
class PasswordHashStabilityTest extends TestCase
{
    use RefreshDatabase, BuildsClinicData;

    public function test_the_default_bcrypt_cost_matches_the_hashes_already_stored(): void
    {
        // phpunit.xml lowers BCRYPT_ROUNDS to 4 for speed, so the production default is read from the file itself.
        $this->assertStringContainsString("env('BCRYPT_ROUNDS', 10)", file_get_contents(config_path('hashing.php')));
    }

    public function test_the_env_example_does_not_set_a_different_bcrypt_cost_than_the_default(): void
    {
        // A fresh install copies .env.example to .env. A BCRYPT_ROUNDS line there that differs from the default in
        // config/hashing.php would make every password re-hash at its next sign-in (finding C15 re-check, 2026-11-06).
        preg_match("/env\('BCRYPT_ROUNDS', (\d+)\)/", file_get_contents(config_path('hashing.php')), $default);
        $this->assertNotEmpty($default, 'the default cost was not found in config/hashing.php');

        $example = file_get_contents(base_path('.env.example'));
        $hasLine = preg_match('/^BCRYPT_ROUNDS=(\d+)\s*$/m', $example, $set) === 1;

        $this->assertTrue(! $hasLine || $set[1] === $default[1], '.env.example sets BCRYPT_ROUNDS to ' . ($set[1] ?? '-') . ', the default is ' . $default[1]);
    }

    public function test_signing_in_does_not_change_the_stored_password_hash(): void
    {
        $this->seedAccessControl();
        config(['hashing.bcrypt.rounds' => 10]);
        $admin = $this->makeAdmin(['email' => 'hash.stable@test.local', 'password' => Hash::make('Stable#Pass1')]);
        $before = User::whereKey($admin->id)->value('password');

        $this->post(route('login'), ['email' => 'hash.stable@test.local', 'password' => 'Stable#Pass1'])->assertRedirect();

        $this->assertAuthenticatedAs($admin);
        $this->assertSame($before, User::whereKey($admin->id)->value('password'));
        $this->assertFalse(Hash::needsRehash($before));
    }
}
