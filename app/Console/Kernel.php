<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Nothing here runs by itself: the startup script (startup-dev-environment.ps1 / .bat) starts
        // `php artisan schedule:work` in the SAME terminal as the server, so the tasks run while the server is up. No Windows
        // Task Scheduler entry and no extra window is used. Times are Philippine time (config/app.php: Asia/Manila).
        $schedule->command('notifications:refresh')->everyMinute()->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/notifications.log'));

        // Batches expire at midnight: refresh the "available" figure of medicines right after, so expired
        // units stop being offered in forms and reports. (The sidebar also does this once a day when a page
        // is opened, for clinics where the scheduler is not running.)
        $schedule->command('inventory:sync-expiry')
            ->dailyAt('00:05')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/inventory.log'));

        // The daily backup of the database, every day at 6:00 PM Philippine time (the time is named here on purpose, so it
        // stays 6 PM in Manila whatever the application's own zone is). --force: a dump every day even when nothing changed,
        // so there is always one of the day and the startup script can tell how old the last one is. It goes to
        // storage/app/backups/scheduled (the Backups screen lists it); db:backup keeps every dump of the last 2 days and one per
        // day for 30 days. It only runs while the server is running: when the PC is off at 6 PM the startup script takes the
        // missed one at the next start. Copy the folder to another disk now and then: the dump is on the same PC as the data.
        $schedule->command('db:backup', ['--force'])
            ->dailyAt('18:00')
            ->timezone('Asia/Manila')
            ->withoutOverlapping(30)
            ->appendOutputTo(storage_path('logs/backup.log'))
            ->onFailure(fn () => Log::error('The scheduled database backup failed: see storage/logs/backup.log. Take one by hand: php artisan db:backup'));
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
