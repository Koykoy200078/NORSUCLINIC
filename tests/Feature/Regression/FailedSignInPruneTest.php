<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The owner's decision of 2026-11-14 (STATUS section 6, "clean-up of old failed-sign-in rows"): a failed sign-in or a
 * "too many attempts" lockout is kept in the activity log for 90 days, then removed by a daily scheduled clean-up. The rows
 * hold the e-mail that was typed; nothing ever removed them. Every other activity-log row is kept.
 */
class FailedSignInPruneTest extends TestCase
{
    use RefreshDatabase;

    private function logRow(string $action, int $daysAgo): ActivityLog
    {
        $row = ActivityLog::create(['action' => $action, 'description' => $action . ' ' . $daysAgo . ' days ago', 'date' => now()->subDays($daysAgo)->toDateString()]);
        $row->forceFill(['created_at' => now()->subDays($daysAgo), 'updated_at' => now()->subDays($daysAgo)])->saveQuietly();

        return $row;
    }

    public function test_failed_sign_in_and_lockout_rows_older_than_90_days_are_removed(): void
    {
        $oldFailed = $this->logRow('login_failed', 91);
        $oldLockout = $this->logRow('login_locked_out', 200);
        $recentFailed = $this->logRow('login_failed', 89);
        $oldSignIn = $this->logRow('login', 400);
        $oldQueue = $this->logRow('queue_added', 400);

        $this->artisan('activity-log:prune-failed-sign-ins')
            ->expectsOutputToContain('2 failed sign-in')
            ->assertSuccessful();

        $this->assertModelMissing($oldFailed);
        $this->assertModelMissing($oldLockout);
        $this->assertModelExists($recentFailed);
        $this->assertModelExists($oldSignIn);
        $this->assertModelExists($oldQueue);
    }

    public function test_a_dry_run_removes_nothing(): void
    {
        $oldFailed = $this->logRow('login_failed', 120);

        $this->artisan('activity-log:prune-failed-sign-ins', ['--dry-run' => true])
            ->expectsOutputToContain('1 failed sign-in')
            ->assertSuccessful();

        $this->assertModelExists($oldFailed);
    }

    public function test_the_clean_up_runs_every_day(): void
    {
        $events = collect(app(Schedule::class)->events())
            ->filter(fn (Event $event) => str_contains((string) $event->command, 'activity-log:prune-failed-sign-ins'));

        $this->assertCount(1, $events);
        $this->assertSame('0 12 * * *', $events->first()->expression);
    }
}
