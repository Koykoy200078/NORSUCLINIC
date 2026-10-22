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
        // Nothing here runs by itself. The startup scripts do NOT start the scheduler: on the clinic PC a Windows Task
        // Scheduler entry must run `php artisan schedule:run` every minute (docs/commands.md, STATUS section 7).
        $schedule->command('notifications:refresh')->everyMinute()->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/notifications.log'));
        // Hourly database backup; the command skips saving when nothing changed since the previous
        // backup and thins old files out (every backup for 2 days, then one per day for 30 days).
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
