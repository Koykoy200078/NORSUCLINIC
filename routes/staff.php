<?php

use App\Support\PanelRoutes;
use Illuminate\Support\Facades\Route;

Route::prefix('staff')->name('staff.')->middleware('auth', 'checkUserStatus', 'role:staff', 'forcePasswordChange')->group(function () {
    PanelRoutes::dashboard('staff');
    PanelRoutes::patients('staff');
    PanelRoutes::queue('staff');
    PanelRoutes::doctors('staff');
    PanelRoutes::specializations();
    PanelRoutes::documentLookups('staff');
    PanelRoutes::medicineLookup();
    PanelRoutes::documents();
    PanelRoutes::labRequests();
    PanelRoutes::prescriptions('staff');
    PanelRoutes::inventory('staff');
    PanelRoutes::dispensing('staff');
    PanelRoutes::activityLogs('staff');
});
