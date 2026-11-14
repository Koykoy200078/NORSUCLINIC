<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

/**
 * Removes the activity-log rows of failed sign-ins and "too many attempts" lockouts once they are older than 90 days (the
 * owner's decision, 2026-11-14). They hold the e-mail that was typed and are only needed to spot recent guessing; every
 * other activity-log row is kept. The scheduler runs it every day at noon (app/Console/Kernel.php); a day the server is
 * off is simply caught up at the next run.
 *
 *   php artisan activity-log:prune-failed-sign-ins            remove
 *   php artisan activity-log:prune-failed-sign-ins --dry-run  only count
 */
class PruneFailedSignIns extends Command
{
    /** The rows written by AppServiceProvider for a failed sign-in and for a lockout. */
    public const ACTIONS = ['login_failed', 'login_locked_out'];

    protected $signature = 'activity-log:prune-failed-sign-ins
        {--days=90 : Keep the rows of the last N days}
        {--dry-run : Count the rows that would be removed without removing them}';

    protected $description = 'Remove failed sign-in and lockout rows of the activity log older than 90 days';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $old = ActivityLog::query()->whereIn('action', self::ACTIONS)->where('created_at', '<', now()->subDays($days));

        if ($this->option('dry-run')) {
            $this->warn($old->count() . " failed sign-in / lockout row(s) older than {$days} days would be removed (dry run, nothing changed).");

            return self::SUCCESS;
        }

        $removed = $old->delete();
        $this->info($removed . " failed sign-in / lockout row(s) older than {$days} days removed.");

        return self::SUCCESS;
    }
}
