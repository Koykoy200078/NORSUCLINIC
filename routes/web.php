<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BarangayController;
use App\Http\Controllers\GenericController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Front\CMSController;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Front\SliderController;
use App\Http\Controllers\DispenseRecordController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicineDispensingManagementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminLogViewerController;
use App\Http\Controllers\DocumentIssuanceController;
use App\Http\Controllers\LabRequestController;
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

    // Admin Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:manage_admin_dashboard')->name('admin.dashboard');
    Route::get('/dashboard-patients', [DashboardController::class, 'getPatientList'])->name('patientData.dashboard');

    // Logs — gate behind a permission so it isn't reachable purely by role. AUTH-5.
    Route::get('logs', [AdminLogViewerController::class, 'index'])->middleware('permission:manage_admin_dashboard');

    // Impersonate. Starting is a POST (a GET link was CSRF-able: an admin clicking a crafted link
    // silently became another user). Leaving lives OUTSIDE this admin-only group, because while
    // impersonating the signed-in user is the target (a doctor/staff account) and the role check
    // here answered 403, so "Return to admin" never worked. M-08.
    Route::post('impersonate/{id}', [UserController::class, 'impersonate'])->name('impersonate');

    // Email Verified
    Route::post('email-verified', [UserController::class, 'emailVerified'])->name('emailVerified');
    Route::post('/email/verification-notification/{userId}', [UserController::class, 'resendEmailVerification'])->name('resend.email.verification');

    // Doctor route
    Route::middleware('permission:manage_doctors')->group(function () {
        Route::resource('doctors', UserController::class);
        Route::post('/add-qualification', [UserController::class, 'addQualification'])->name('add.qualification');
        Route::put('doctor-status', [UserController::class, 'changeDoctorStatus'])->name('doctor.status');
        // Use the DOCTOR-scoped UserController::resetPassword (PatientController::resetPassword
        // only accepts PATIENT accounts, so it always 403'd for doctors). E-CRIT-3.
        Route::post('doctors/{user}/reset-password', [UserController::class, 'resetPassword'])->name('doctors.reset.password');
    });

    // Countries routes
    Route::middleware('permission:manage_countries')->group(function () {
        Route::resource('countries', CountryController::class)->except(['create', 'show']);
        Route::post('countries/{country}', [CountryController::class, 'update']);
    });

    // States routes
    Route::middleware('permission:manage_states')->group(function () {
        Route::resource('states', ProvinceController::class)->except(['create', 'show']);
        Route::post('states/{state}', [ProvinceController::class, 'update']);
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
        Route::get('states-list', [SettingController::class, 'getStates'])->name('states-list');
        Route::get('cities-list', [SettingController::class, 'getCities'])->name('cities-list');

        // Backups
        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('backups/create', [BackupController::class, 'create'])->name('backups.create');
        Route::get('backups/download/{fileName}', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('backups/destroy/{fileName}', [BackupController::class, 'destroy'])->name('backups.destroy');
        Route::post('backups/import', [BackupController::class, 'import'])->name('backups.import');
    });

    // Patient Routes
    Route::middleware('permission:manage_patients')->group(function () {
        Route::post('patients/{patient}/restore', [PatientController::class, 'restore'])->name('patients.restore');
        Route::resource('patients', PatientController::class);
        Route::get('patients/{patient}/history', [PatientController::class, 'showMyHistory'])->name('patients.showMyHistory');
        Route::post('patients/{user}/reset-password', [PatientController::class, 'resetPassword'])->name('patients.reset.password');
    });

    // Search users route (moved outside middleware for testing)
    Route::get('document-issuances/search-users', [DocumentIssuanceController::class, 'searchUsers'])->name('document-issuances.search-users');
    Route::get('document-issuances/get-last-consultation', [DocumentIssuanceController::class, 'getLastConsultation'])->name('document-issuances.get-last-consultation');
    Route::get('document-issuances/get-last-medical-certificate', [DocumentIssuanceController::class, 'getLastMedicalCertificate'])->name('document-issuances.get-last-medical-certificate');
    Route::get('lab-requests/search-users', [LabRequestController::class, 'searchUsers'])->name('lab-requests.search-users');

    // Request Documents
    Route::middleware('permission:manage_request_documents')->group(function () {
        Route::get('document-issuances/{document_issuance}/export-pdf', [DocumentIssuanceController::class, 'exportPdf'])->name('document-issuances.export-pdf');
        Route::get('document-issuances/{document_issuance}/images/{index}', [DocumentIssuanceController::class, 'showImage'])
            ->whereNumber('index')->name('document-issuances.image');
        Route::resource('document-issuances', DocumentIssuanceController::class);

        // Lab Requests
        Route::post('lab-requests/{lab_request}/status', [LabRequestController::class, 'updateStatus'])->name('lab-requests.update-status');
        Route::get('lab-requests/{id}/pdf', [LabRequestController::class, 'exportPdf'])->name('lab-requests.pdf');
        Route::resource('lab-requests', LabRequestController::class);
    });

    // Activity Logs Routes
    Route::prefix('activity-logs')->group(function () {
        Route::get('/', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/export', [\App\Http\Controllers\ActivityLogController::class, 'export'])->name('activity-logs.export');
        Route::get('/{id}', [\App\Http\Controllers\ActivityLogController::class, 'show'])->name('activity-logs.show');
    });

    // Specialization routes
    Route::middleware('permission:manage_specialties')->group(function () {
        Route::resource('specializations', SpecializationController::class)->except(['create', 'show']);
    });

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

    // Prescription Management
    Route::middleware('permission:manage_request_documents')->group(function () {
        Route::resource('prescriptions', PrescriptionController::class)->except('create', 'edit');
        Route::get('patients/{patientId}/prescription-create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
        Route::get('prescriptions/{prescription}/edit', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
        Route::post('prescription-medicine', [PrescriptionController::class, 'prescreptionMedicineStore'])->name('prescription.medicine.store');
        Route::post('prescriptions/{prescription}/active-deactive', [PrescriptionController::class, 'activeDeactiveStatus'])->name('prescription.status');
        Route::post('prescriptions/{prescription}/dispense', [PrescriptionController::class, 'dispense'])->name('prescriptions.dispense');
        Route::get('prescription-medicine-show/{id}', [PrescriptionController::class, 'prescriptionMedicineShowFunction'])->name('prescription.medicine.show');
        Route::get('prescription-pdf/{id}', [PrescriptionController::class, 'convertToPDF'])->name('prescriptions.pdf');
    });

    // Patient Queue Management
    Route::middleware('permission:manage_patients')->group(function () {
        Route::get('patient-queue', [\App\Http\Controllers\PatientQueueController::class, 'index'])->name('patient-queue.index');
        Route::get('patient-queue/refresh', [\App\Http\Controllers\PatientQueueController::class, 'indexPartial'])->name('patient-queue.refresh');
        Route::get('patient-queue/create', [\App\Http\Controllers\PatientQueueController::class, 'create'])->name('patient-queue.create');
        Route::post('patient-queue', [\App\Http\Controllers\PatientQueueController::class, 'store'])->name('patient-queue.store');
        Route::get('patient-queue/{patientQueue}/edit', [\App\Http\Controllers\PatientQueueController::class, 'edit'])->name('patient-queue.edit');
        Route::put('patient-queue/{patientQueue}', [\App\Http\Controllers\PatientQueueController::class, 'update'])->name('patient-queue.update');
        Route::delete('patient-queue/{patientQueue}', [\App\Http\Controllers\PatientQueueController::class, 'destroy'])->name('patient-queue.destroy');
        Route::post('patient-queue/{patientQueue}/complete', [\App\Http\Controllers\PatientQueueController::class, 'complete'])->name('patient-queue.complete');
        Route::post('patient-queue/{patientQueue}/call-next', [\App\Http\Controllers\PatientQueueController::class, 'callNext'])->name('patient-queue.call-next');
    });

    // ============================================================================
    // ADMIN MEDICINE ROUTES
    // ============================================================================
    // Require manage_medicines so revoking that permission from an admin role actually
    // restricts inventory / stock-deduction access (mirrors doctor/staff route files). AUTH-5.
    Route::middleware('permission:manage_medicines')->group(function () {

    // Medicine Categories
    Route::resource('categories', CategoryController::class)->parameters(['categories' => 'category'])->except(['create']);
    Route::post('categories/{category_id}/active-deactive', [CategoryController::class, 'activeDeActiveCategory'])->name('active.deactive');

    // Medicine Generics
    Route::resource('generics', GenericController::class);

    // Medicines
    Route::resource('medicines', MedicineController::class)->parameters(['medicines' => 'medicine'])->except(['show']);
    Route::get('medicine-inventory-tracking', [MedicineController::class, 'index'])->name('medicine-inventory.index');
    Route::get('medicines-show-modal/{medicine}', [MedicineController::class, 'showModal'])->name('medicines.show.modal');
    Route::get('medicines-uses-check/{medicine}', [MedicineController::class, 'checkUseOfMedicine'])->name('check.use.medicine');
    Route::get('medicines-by-category', [MedicineController::class, 'getMedicinesByCategory'])->name('medicines.by.category');
    Route::get('medicine-dispensing-management', [MedicineDispensingManagementController::class, 'index'])->name('medicine-dispensing.index');

    // Stock In (Medicine Purchasing)
    Route::resource('stock-in', StockInController::class);
    Route::get('export-stock-in', [StockInController::class, 'purchaseMedicineExport'])->name('stock-in.excel');
    Route::get('get-medicine/{medicine}', [StockInController::class, 'getMedicine'])->name('get-medicine');
    Route::get('used-medicine', function () {
        return redirect()->route('medicine-dispensing.index', ['tab' => 'stock-out']);
    })->name('used-medicine.index');

    // Dispense Records
    Route::resource('dispense-records', DispenseRecordController::class)->parameters(['dispense-records' => 'medicine_history']);
    Route::post('dispense-records/store-patient', [DispenseRecordController::class, 'storePatient'])->name('dispense-records.store-patient');
    Route::get('dispense-records-pdf/{id}', [DispenseRecordController::class, 'convertToPDF'])->name('dispense-records.pdf');
    Route::get('dispense-records/by-category/{category}', [DispenseRecordController::class, 'getMedicineCategory'])->name('dispense-records.by-category');

    // Medicine History (legacy URLs - redirect to dispense-records for backward compat)
    Route::redirect('medicine-history', '/admin/dispense-records');
    Route::redirect('medicine-history/{id}', '/admin/dispense-records/{id}');

    }); // end permission:manage_medicines group
});

// Note: Doctor and Staff routes are defined in their respective route files
// to avoid duplication and maintain role-based separation.

// POST (not GET) so CSRF protection applies — a GET bulk-delete is triggerable via a
// simple <img> tag on any page the admin visits. CRUD-DL.
Route::post('delete-old-patients', [PatientController::class, 'deleteOldPatient'])
    ->name('patients.delete-old')
    ->middleware(['auth', 'checkUserStatus', 'role:clinic_admin', 'permission:manage_patients']);

require __DIR__ . '/auth.php';

// Patient self-service portal is retired — patients are record-only and no longer log in
// (see AuthenticatedSessionController). The routes are kept in routes/patient.php, disabled.
// To bring the patient portal back, uncomment the line below (and re-enable patient login).
// require __DIR__ . '/patient.php';
require __DIR__ . '/staff.php';
require __DIR__ . '/doctor.php';
require __DIR__ . '/upgrade.php';
