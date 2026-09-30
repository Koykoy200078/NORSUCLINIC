<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Hourly database backup; the command skips saving when nothing changed since the previous
        // backup and thins old files out (every backup for 2 days, then one per day for 30 days).
        // Nothing runs this by itself: `php artisan schedule:work` (started by the startup scripts) or a
        // Task Scheduler / cron entry running `php artisan schedule:run` every minute must be active.
        $schedule->command('db:backup')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/backup.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
