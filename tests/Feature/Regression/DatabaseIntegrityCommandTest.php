<?php

namespace Tests\Feature\Regression;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\Concerns\UsesFixtureDatabase;
use Tests\TestCase;

/**
 * Plan Phase 1.5: `php artisan db:integrity` prints every check and exits 1 only when something is wrong (an error);
 * `php artisan db:restore-foreign-keys` adds the missing keys that nothing blocks.
 */
class DatabaseIntegrityCommandTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;
    use UsesFixtureDatabase;

    protected function tearDown(): void
    {
        try {
            $this->dropFixtureDatabase();
        } finally {
            parent::tearDown();
        }
    }

    public function test_a_correct_database_exits_successfully(): void
    {
        $this->artisan('db:integrity')
            ->expectsOutputToContain('Every table is InnoDB')
            ->expectsOutputToContain('All 47 foreign keys are in place')
            ->expectsOutputToContain('0 error(s), 0 warning(s)')
            ->assertSuccessful();
    }

    public function test_an_error_is_printed_with_its_detail_and_the_exit_code_is_one(): void
    {
        $medicine = $this->makeMedicine('Paracetamol');
        $this->stockIn($medicine, 20, now()->addYear()->toDateString());
        DB::table('medicines')->where('id', $medicine->id)->update(['quantity' => 25]);

        $this->artisan('db:integrity')
            ->expectsOutputToContain('Medicine totals match their batches')
            ->expectsOutputToContain("Paracetamol (#{$medicine->id}): recorded 25, batches add up to 20")
            ->expectsOutputToContain('1 error(s)')
            ->assertFailed();
    }

    public function test_a_warning_alone_does_not_fail_the_command(): void
    {
        $this->seedAccessControl();
        $staff = $this->makeStaff();
        $staff->givePermissionTo('manage_patients');

        $this->artisan('db:integrity')
            ->expectsOutputToContain("user #{$staff->id} (staff): 1 permission(s) given directly instead of by the role")
            ->expectsOutputToContain('0 error(s), 1 warning(s)')
            ->assertSuccessful();
    }

    public function test_restore_foreign_keys_adds_what_nothing_blocks_and_says_what_it_skipped(): void
    {
        $this->useFixtureDatabase('restore_fks');
        DB::statement('CREATE TABLE `users` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB');
        DB::statement('CREATE TABLE `doctors` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT UNSIGNED NOT NULL) ENGINE=InnoDB');
        DB::statement('CREATE TABLE `patients` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT UNSIGNED NOT NULL) ENGINE=InnoDB');
        DB::table('users')->insert(['id' => 1]);
        DB::table('patients')->insert(['id' => 1, 'user_id' => 99]);

        $this->artisan('db:restore-foreign-keys')
            ->expectsOutputToContain('Added 1 foreign key(s)')
            ->expectsOutputToContain('patients_user_id_foreign: 1 row(s) of patients.user_id point at users rows that do not exist')
            ->assertSuccessful();
    }
}
