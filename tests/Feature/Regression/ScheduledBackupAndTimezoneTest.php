<?php

namespace Tests\Feature\Regression;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * The clinic runs on Philippine time (Asia/Manila, UTC+8, no daylight saving) in Laravel AND in the database, and the database is
 * backed up by itself every day at 6:00 PM Philippine time by the scheduler that the startup script runs in its one terminal.
 *
 * The database connection is pinned with the OFFSET "+08:00", not the name "Asia/Manila": MySQL on Windows (WAMP) has no named
 * time zone tables loaded, so the name makes every connection fail with "Unknown or incorrect time zone".
 */
class ScheduledBackupAndTimezoneTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function backupEvent(): Event
    {
        $events = collect(app(Schedule::class)->events())->filter(fn (Event $event) => str_contains((string) $event->command, 'db:backup'));
        $this->assertCount(1, $events, 'exactly one scheduled database backup');

        return $events->first();
    }

    private function timezoneName(Event $event): string
    {
        return $event->timezone instanceof \DateTimeZone ? $event->timezone->getName() : (string) $event->timezone;
    }

    // ------------------------------------------------------------------ Laravel

    public function test_laravel_runs_on_philippine_time(): void
    {
        $this->assertSame('Asia/Manila', config('app.timezone'));
        $this->assertSame('Asia/Manila', date_default_timezone_get());
        $this->assertSame('Asia/Manila', now()->getTimezone()->getName());
        $this->assertSame('+08:00', now()->format('P'));
    }

    // ------------------------------------------------------------------ the database

    public function test_the_database_connection_is_pinned_to_philippine_time_by_offset(): void
    {
        $zone = config('database.connections.mysql.timezone');

        $this->assertSame('+08:00', $zone);
        $this->assertMatchesRegularExpression('/^[+-]\d{2}:\d{2}$/', (string) $zone, 'an offset: the name needs time zone tables that WAMP MySQL does not have');
        $this->assertSame('+08:00', DB::selectOne('SELECT @@session.time_zone AS zone')->zone, 'the session really runs on it, whatever the Windows clock zone is');
    }

    public function test_the_database_clock_and_laravels_clock_show_the_same_philippine_time(): void
    {
        $database = Carbon::parse(DB::selectOne('SELECT NOW() AS now')->now, 'Asia/Manila');

        $this->assertLessThanOrEqual(5, abs($database->timestamp - now()->timestamp), 'NOW() is the Philippine wall clock, as now() is');
        $this->assertLessThanOrEqual(5, abs((int) DB::selectOne('SELECT UNIX_TIMESTAMP() AS moment')->moment - now()->timestamp));
    }

    public function test_a_time_written_by_laravel_is_the_same_moment_for_mysql(): void
    {
        // 8:30 in the morning in Manila is 00:30 UTC. The settings table has TIMESTAMP columns, which MySQL converts by its session zone.
        DB::table('settings')->insert(['key' => 'tz_probe', 'value' => 'x', 'created_at' => '2026-03-01 08:30:00', 'updated_at' => '2026-03-01 08:30:00']);

        $moment = (int) DB::selectOne("SELECT UNIX_TIMESTAMP(created_at) AS moment FROM settings WHERE `key` = 'tz_probe'")->moment;

        $this->assertSame(Carbon::parse('2026-03-01 00:30:00', 'UTC')->timestamp, $moment);
        $this->assertSame('2026-03-01 08:30:00', DB::table('settings')->where('key', 'tz_probe')->value('created_at'), 'and it reads back as the same Philippine clock time');
    }

    // ------------------------------------------------------------------ the daily backup

    public function test_the_database_is_backed_up_every_day_at_six_in_the_evening_philippine_time(): void
    {
        $event = $this->backupEvent();

        $this->assertSame('0 18 * * *', $event->expression);
        $this->assertSame('Asia/Manila', $this->timezoneName($event), 'the time is Philippine time on its own, not only because the application is');
        $this->assertStringContainsString('--force', (string) $event->command, 'a dump every day, also when nothing changed, so there is always one of the day');
        $this->assertTrue($event->withoutOverlapping, 'a slow dump is never started twice');
    }

    public function test_the_backup_is_due_at_six_pm_in_manila_and_at_no_other_time(): void
    {
        $event = $this->backupEvent();

        Carbon::setTestNow(Carbon::parse('2026-10-30 18:00:00', 'Asia/Manila'));
        $this->assertTrue($event->isDue($this->app), '6:00 PM in Manila');

        Carbon::setTestNow(Carbon::parse('2026-10-30 10:00:00', 'UTC'));
        $this->assertTrue($event->isDue($this->app), 'the same moment told in UTC (10:00)');

        foreach (['2026-10-30 17:59:00', '2026-10-30 18:01:00', '2026-10-30 06:00:00', '2026-10-30 00:05:00'] as $time) {
            Carbon::setTestNow(Carbon::parse($time, 'Asia/Manila'));
            $this->assertFalse($event->isDue($this->app), "not due at {$time} Manila time");
        }

        Carbon::setTestNow(Carbon::parse('2026-10-30 18:00:00', 'UTC'));
        $this->assertFalse($event->isDue($this->app), '18:00 UTC is 2:00 AM the next day in Manila: not the backup hour');
    }

    public function test_the_backups_screen_says_when_the_automatic_backup_is_taken(): void
    {
        $this->seedAccessControl();
        $this->actingAs($this->makeAdmin());

        $this->get(route('backups.index'))->assertOk()
            ->assertSee('every day at 6:00 PM Philippine time')
            ->assertSee('while the server window is open');
    }

    public function test_the_other_scheduled_work_keeps_its_times_and_is_listed_with_the_backup(): void
    {
        $times = collect(app(Schedule::class)->events())->mapWithKeys(fn (Event $event) => [
            trim(preg_replace('/^.*artisan[\'"]?\s+/', '', (string) $event->command)) => $event->expression,
        ])->all();

        $this->assertSame('* * * * *', $times['notifications:refresh']);
        $this->assertSame('5 0 * * *', $times['inventory:sync-expiry']);
        $this->artisan('schedule:list')->expectsOutputToContain('db:backup')->assertSuccessful();
    }
}
