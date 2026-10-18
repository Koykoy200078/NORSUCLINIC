<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The channel authorisation endpoint: only a signed-in, active user (the same stack as every clinic page).
        Broadcast::routes(['middleware' => ['web', 'auth', 'checkUserStatus']]);

        require base_path('routes/channels.php');
    }
}
