<?php

namespace App\Console\Commands;

use App\Services\ClinicNotificationService;
use Illuminate\Console\Command;

class RefreshClinicNotifications extends Command
{
    protected $signature = 'notifications:refresh';

    protected $description = 'Refresh per-user clinic alerts, including time-based stock expiry';

    public function handle(ClinicNotificationService $service): int
    {
        $service->refresh();
        $this->info('Clinic notifications refreshed.');

        return self::SUCCESS;
    }
}
