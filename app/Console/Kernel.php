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

        // Batches expire at midnight: refresh the "available" figure of medicines right after, so expired
        // units stop being offered in forms and reports. (The sidebar also does this once a day when a page
        // is opened, for clinics where the scheduler is not running.)
        $schedule->command('inventory:sync-expiry')
            ->dailyAt('00:05')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/inventory.log'));
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
