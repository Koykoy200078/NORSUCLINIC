<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Real-time updates (plan Phase 7). A screen listens to the channel of a module only if its user may use that module:
// the same rule as the menu, the routes and the buttons (canUseModule), so a nurse without Manage Medicines can never
// hear about the inventory. The notices carry no patient data (App\Events\ModuleChanged).
Broadcast::channel('module.{module}', function ($user, string $module) {
    return in_array($module, \App\Support\Realtime::MODULES, true) && canUseModule($module, $user);
});

// One private channel per signed-in user, for notices meant for one person only.
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
