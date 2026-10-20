<?php

use App\Http\Controllers\PatientController;
use App\Support\PanelRoutes;
use Illuminate\Support\Facades\Route;

Route::prefix('doctors')->name('doctors.')->middleware('auth', 'checkUserStatus', 'role:doctor', 'forcePasswordChange')->group(function () {
    Route::get('/patients-detail/{patient}', [PatientController::class, 'show'])->name('patient.detail')->middleware('permission:manage_patients');

    PanelRoutes::dashboard('doctor');
    PanelRoutes::queue('doctor');
    PanelRoutes::doctors('doctor');
    PanelRoutes::prescriptions('doctor');
    PanelRoutes::patients('doctor');
    PanelRoutes::specializations();
    PanelRoutes::documentLookups('doctor');
    PanelRoutes::documents();
    PanelRoutes::labRequests();
    PanelRoutes::medicineLookup();
    PanelRoutes::inventory('doctor');
    PanelRoutes::dispensing('doctor');
    PanelRoutes::activityLogs('doctor');
});
