<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BarangayController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\Front\CMSController;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Front\SliderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\UserController;
use App\Support\PanelRoutes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminLogViewerController;
use App\Http\Controllers\BackupController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', function () {
    return (! Auth::check()) ? view('auth.login') : Redirect::to(getDashboardURL());
})->name('login')->middleware('setLanguage');

// Returns the current session's CSRF token so the frontend can recover from a stale
// token (HTTP 419 "CSRF token mismatch") without losing the user's work.
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf.token');

Route::middleware('setLanguage')->group(function () {
    Route::get('/', [FrontController::class, 'medical'])->name('medical');
    Route::get('/medical-about-us', [FrontController::class, 'medicalAboutUs'])->name('medicalAboutUs');
    Route::get('/medical-services', [FrontController::class, 'medicalServices'])->name('medicalServices');
    Route::get('/medical-doctors', [FrontController::class, 'medicalDoctors'])->name('medicalDoctors');
    Route::get('/medical-contact', [FrontController::class, 'medicalContact'])->name('medicalContact');
    Route::get('/terms-conditions', [FrontController::class, 'termsCondition'])->name('terms.conditions');
    Route::get('/privacy-policy', [FrontController::class, 'privacyPolicy'])->name('privacy.policy');
});
//Change language
Route::post('/change-language', [FrontController::class, 'changeLanguage'])->name('front.change.language');

// The waiting-area display is a bearer link. It renders no authenticated layout or clinical fields.
Route::get('queue-board/{token}/state', [\App\Http\Controllers\QueueBoardController::class, 'state'])
    ->where('token', '[A-Za-z0-9]{64}')->name('queue-board.state');
Route::get('queue-board/{token}', [\App\Http\Controllers\QueueBoardController::class, 'show'])
    ->where('token', '[A-Za-z0-9]{64}')->name('queue-board.show');

// The sidebar menu alone (its badges are redrawn when a module changes, see resources/assets/js/realtime).
Route::get('/live/menu', fn () => response(view('layouts.menu')->render().view('layouts.notification-bell')->render()))
    ->middleware(['auth', 'checkUserStatus'])
    ->name('live.menu');

//Dark Mode
Route::get('update-dark-mode', [UserController::class, 'updateDarkMode'])
    ->middleware(['auth', 'checkUserStatus'])
    ->name('update-dark-mode');

// Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');

Route::post(
    '/notification/{notification}/read',
    [NotificationController::class, 'readNotification']
)->middleware(['auth', 'checkUserStatus'])->name('notifications.read');
Route::post(
    '/read-all-notification',
    [NotificationController::class, 'readAllNotification']
)->middleware(['auth', 'checkUserStatus'])->name('notifications.read.all');

Route::middleware('auth', 'checkUserStatus')->group(function () {
    Route::get('impersonate-leave', [UserController::class, 'impersonateLeave'])->name('impersonate.leave');

    // Update profile
    Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('profile.setting');
    Route::put('/profile/update', [UserController::class, 'updateProfile'])->name('update.profile.setting');
    Route::put('/change-user-password', [UserController::class, 'changePassword'])->name('user.changePassword');
    Route::put('/email-notification', [UserController::class, 'emailNotification'])->name('emailNotification');
});

//get States and cities route
Route::get('get-states', [UserController::class, 'getStates'])
    ->middleware(['auth', 'checkUserStatus'])
    ->name('get-state');
Route::get('get-cities', [UserController::class, 'getCity'])
    ->middleware(['auth', 'checkUserStatus'])
    ->name('get-city');
Route::get('get-barangays', [UserController::class, 'getBarangays'])
    ->middleware(['auth', 'checkUserStatus'])
    ->name('get-barangay');

// ============================================================================
// ADMIN ROUTES
// ============================================================================
Route::prefix('admin')->middleware('auth', 'checkUserStatus', 'role:clinic_admin', 'forceAdminPasswordChange')->group(function () {

    PanelRoutes::dashboard('admin');

    // Logs — gate behind a permission so it isn't reachable purely by role. AUTH-5.
    Route::get('logs', [AdminLogViewerController::class, 'index'])->middleware('permission:manage_admin_dashboard');

    // Impersonate. Starting is a POST (a GET link was CSRF-able: an admin clicking a crafted link
    // silently became another user). Leaving lives OUTSIDE this admin-only group, because while
    // impersonating the signed-in user is the target (a doctor/staff account) and the role check
    // here answered 403, so "Return to admin" never worked. M-08.
    Route::post('impersonate/{id}', [UserController::class, 'impersonate'])->name('impersonate');

    PanelRoutes::emailVerification('admin');
    PanelRoutes::doctors('admin');

    // Countries routes
    Route::middleware('permission:manage_countries')->group(function () {
        Route::resource('countries', CountryController::class)->except(['create', 'show']);
        // The edit pop-up posts to countries/{id} without a PUT override (resources/assets/js/countries), so this route is used.
        Route::post('countries/{country}', [CountryController::class, 'update'])->name('countries.update.post');
    });

    // States routes
    Route::middleware('permission:manage_states')->group(function () {
        Route::resource('states', ProvinceController::class)->except(['create', 'show']);
    });

    // Cities Routes
    Route::middleware('permission:manage_cities')->group(function () {
        Route::resource('cities', CityController::class)->except(['create', 'show']);
    });

    // Barangays Routes
    Route::middleware('permission:manage_cities')->group(function () {
        Route::resource('barangays', BarangayController::class)->except(['create', 'show']);
    });

    // Role route
    Route::middleware('permission:manage_roles')->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
    });

    // Settings routes
    Route::middleware('permission:manage_settings')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('setting.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('setting.update');
        // Settings > Report lists: the illnesses / services behind the Accomplishment Report
        // Settings > Deleted records: see who deleted a consultation / certificate / lab request and bring it back
        Route::get('/settings/deleted-records', [\App\Http\Controllers\DeletedRecordController::class, 'index'])->name('deleted-records.index');
        Route::post('/settings/deleted-records/{type}/{id}/restore', [\App\Http\Controllers\DeletedRecordController::class, 'restore'])
            ->whereIn('type', ['document', 'lab-request'])->whereNumber('id')->name('deleted-records.restore');
        Route::get('/settings/report-lists', [\App\Http\Controllers\ReportListController::class, 'index'])->name('report-lists.index');
        Route::post('/settings/report-lists/illnesses', [\App\Http\Controllers\ReportListController::class, 'storeIllness'])->name('report-lists.illnesses.store');
        Route::put('/settings/report-lists/illnesses/{illness}', [\App\Http\Controllers\ReportListController::class, 'updateIllness'])->name('report-lists.illnesses.update');
        Route::post('/settings/report-lists/services', [\App\Http\Controllers\ReportListController::class, 'storeService'])->name('report-lists.services.store');
        Route::put('/settings/report-lists/services/{service}', [\App\Http\Controllers\ReportListController::class, 'updateService'])->name('report-lists.services.update');
        Route::get('states-list', [SettingController::class, 'getStates'])->name('states-list');
        Route::get('cities-list', [SettingController::class, 'getCities'])->name('cities-list');

        // Backups
        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('backups/create', [BackupController::class, 'create'])->name('backups.create');
        Route::get('backups/download/{fileName}', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('backups/destroy/{fileName}', [BackupController::class, 'destroy'])->name('backups.destroy');
        Route::post('backups/import', [BackupController::class, 'import'])->name('backups.import');
    });

    PanelRoutes::patients('admin');
    Route::middleware('permission:manage_patients')->group(function () {
        Route::get('patient-merge', [\App\Http\Controllers\PatientMergeController::class, 'index'])->name('patient-merge.index');
        Route::post('patient-merge', [\App\Http\Controllers\PatientMergeController::class, 'store'])->name('patient-merge.store');
    });
    PanelRoutes::documentLookups('admin');
    PanelRoutes::documents();
    PanelRoutes::labRequests();
    PanelRoutes::activityLogs('admin');
    PanelRoutes::specializations();

    // Staff route
    Route::middleware('permission:manage_staff')->group(function () {
        Route::resource('staffs', StaffController::class);
        Route::post('staffs/{user}/reset-password', [StaffController::class, 'resetPassword'])->name('staffs.reset.password');
    });

    // CMS/Front Management
    Route::middleware('permission:manage_front_cms')->group(function () {
        Route::get('cms', [CMSController::class, 'index'])->name('cms.index');
        Route::post('cms', [CMSController::class, 'update'])->name('cms.update');
        Route::resource('banner', SliderController::class)->except('create', 'store', 'destroy', 'show');
    });

    PanelRoutes::prescriptions('admin');
    PanelRoutes::queue('admin');
    PanelRoutes::inventory('admin');
    Route::middleware('permission:manage_medicines')->group(function () {
        Route::get('inventory-merge', [\App\Http\Controllers\InventoryMergeController::class, 'index'])->name('inventory-merge.index');
        Route::post('inventory-merge', [\App\Http\Controllers\InventoryMergeController::class, 'store'])->name('inventory-merge.store');
    });
    PanelRoutes::dispensing('admin');
});

// Note: Doctor and Staff routes are defined in their respective route files
// to avoid duplication and maintain role-based separation.

require __DIR__ . '/auth.php';

// Patient self-service portal is retired — patients are record-only and no longer log in
// (see AuthenticatedSessionController). The routes are kept in routes/patient.php, disabled.
// To bring the patient portal back, uncomment the line below (and re-enable patient login).
// require __DIR__ . '/patient.php';
require __DIR__ . '/staff.php';
require __DIR__ . '/doctor.php';
