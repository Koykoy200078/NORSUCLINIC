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
        // Nothing here runs by itself: the startup script (startup-dev-environment.ps1 / .bat) starts
        // `php artisan schedule:work` in its own terminal, so the tasks run while the server is up. No Windows Task
        // Scheduler entry is used. There is no automatic database backup: take one by hand (Settings > Backups or
        // `php artisan db:backup`).
        $schedule->command('notifications:refresh')->everyMinute()->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/notifications.log'));

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
