<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\GenericController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicineBillController;
use App\Http\Controllers\MedicineAvailabilityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientQueueController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\RequestDocumentsController;
use App\Http\Controllers\Front\CMSController;
use App\Http\Controllers\Front\SliderController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::prefix('staff')->name('staff.')->middleware('auth', 'xss', 'checkUserStatus', 'role:staff')->group(function () {

    // Staff Dashboard - accessible to all staff members
    Route::get('/dashboard', [DashboardController::class, 'staffDashboard'])->name('dashboard');

    // Patient Management (Staff can manage patients but with limited access)
    Route::middleware('permission:manage_patients')->group(function () {
        Route::resource('patients', PatientController::class);
        Route::get('patients/{patient}/history', [PatientController::class, 'showMyHistory'])->name('patients.showMyHistory');
        // Email verification for patients
        Route::post('/email/verification-notification/{userId}', [UserController::class, 'resendEmailVerification'])->name('resend.email.verification');
        // Toggle email_verified_at for any user (staff managing doctors/patients)
        Route::post('/email-verified', [UserController::class, 'emailVerified'])->name('emailVerified');
    });

    // Patient Queue Management (Nurse/Staff can manage queue)
    Route::middleware('permission:manage_patients')->group(function () {
        Route::get('patient-queue/refresh', [PatientQueueController::class, 'indexPartial'])->name('patient-queue.refresh');
        Route::resource('patient-queue', PatientQueueController::class);
        Route::post('patient-queue/{patientQueue}/call-next', [PatientQueueController::class, 'callNext'])->name('patient-queue.call-next');
        Route::post('patient-queue/{patientQueue}/complete', [PatientQueueController::class, 'complete'])->name('patient-queue.complete');
    });

    // Doctor Management (Staff can manage doctors)
    Route::middleware('permission:manage_doctors')->group(function () {
        Route::get('doctors', [UserController::class, 'index'])->name('doctors.index');
        Route::get('doctors/create', [UserController::class, 'create'])->name('doctors.create');
        Route::post('doctors', [UserController::class, 'store'])->name('doctors.store');
        Route::get('doctors/{doctor}', [UserController::class, 'show'])->name('doctors.show');
        Route::get('doctors/{doctor}/edit', [UserController::class, 'edit'])->name('doctors.edit');
        Route::match(['PUT', 'PATCH'], 'doctors/{doctor}', [UserController::class, 'update'])->name('doctors.update');
        Route::delete('doctors/{doctor}', [UserController::class, 'destroy'])->name('doctors.destroy');
        Route::post('/add-qualification', [UserController::class, 'addQualification'])->name('add.qualification');
        Route::put('doctor-status', [UserController::class, 'changeDoctorStatus'])->name('doctor.status');
    });

    // Specializations (Staff can manage specializations)
    Route::middleware('permission:manage_specialties')->group(function () {
        Route::resource('specializations', SpecializationController::class);
    });

    // Search users route (moved outside middleware for testing)
    Route::get('request-documents/search-users', [RequestDocumentsController::class, 'searchUsers'])->name('request-documents.search-users');
    Route::get('request-documents/get-last-consultation', [RequestDocumentsController::class, 'getLastConsultation'])->name('request-documents.get-last-consultation');

    // Request Documents (Staff specific)
    Route::middleware('permission:manage_request_documents')->group(function () {
        Route::get('request-documents/{id}/export-pdf', [RequestDocumentsController::class, 'exportPdf'])->name('request-documents.export-pdf');
        Route::resource('request-documents', RequestDocumentsController::class);
    });

    // Prescription Management (Staff can assist with prescriptions)
    Route::resource('prescriptions', PrescriptionController::class)->except('create', 'edit', 'index');
    Route::get('patients/{patientId}/prescription-create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::get('prescriptions/{prescription}/edit', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
    Route::post('prescription-medicine', [PrescriptionController::class, 'prescreptionMedicineStore'])->name('prescription.medicine.store');
    Route::post('prescriptions/{prescription}/active-deactive', [PrescriptionController::class, 'activeDeactiveStatus'])->name('prescription.status');
    Route::get('prescription-medicine-show/{id}', [PrescriptionController::class, 'prescriptionMedicineShowFunction'])->name('prescription.medicine.show');
    Route::get('prescription-pdf/{id}', [PrescriptionController::class, 'convertToPDF'])->name('prescriptions.pdf');

    // Medicine Management (Staff can manage medicines, categories, generics)
    Route::middleware('permission:manage_medicines')->group(function () {
        // Medicine Categories
        Route::resource('categories', CategoryController::class)->parameters(['categories' => 'category']);
        Route::post('categories/{category_id}/active-deactive', [CategoryController::class, 'activeDeActiveCategory'])->name('active.deactive');

        // Medicine Generics
        Route::resource('generics', GenericController::class);

        // Medicine Brands
        Route::resource('brands', BrandController::class);

        // Medicines
        Route::resource('medicines', MedicineController::class)->parameters(['medicines' => 'medicine']);
        Route::get('medicines-show-modal/{medicine}', [MedicineController::class, 'showModal'])->name('medicines.show.modal');
        Route::get('medicines-uses-check/{medicine}', [MedicineController::class, 'checkUseOfMedicine'])->name('check.use.medicine');
        Route::get('medicines-by-category', [MedicineController::class, 'getMedicinesByCategory'])->name('medicines.by.category');

        // Medicine Purchase
        Route::resource('medicine-availability', MedicineAvailabilityController::class)->parameters(['categories' => 'category']);
        Route::get('export-medicine-availability', [MedicineAvailabilityController::class, 'purchaseMedicineExport'])->name('medicine-availability.excel');
        Route::get('get-medicine/{medicine}', [MedicineAvailabilityController::class, 'getMedicine'])->name('get-medicine');
        Route::get('used-medicine', [MedicineAvailabilityController::class, 'usedMedicine'])->name('used-medicine.index');

        // Medicine History
        Route::resource('medicine-history', MedicineBillController::class);
        Route::post('medicine-history/store-patient', [MedicineBillController::class, 'storePatient'])->name('store.patient');
        Route::get('medicine-history-pdf/{id}', [MedicineBillController::class, 'convertToPDF'])->name('medicine.bill.pdf');
        Route::get('get-medicine-category/{category}', [MedicineBillController::class, 'getMedicineCategory'])->name('get-medicine-category');
    });

    // CMS Management (Staff can manage CMS with limited access)
    Route::middleware('permission:manage_front_cms')->group(function () {
        Route::get('cms', [CMSController::class, 'index'])->name('cms.index');
        Route::post('cms', [CMSController::class, 'update'])->name('cms.update');

        // Banner/Slider management
        Route::resource('banner', SliderController::class)->except('create', 'store', 'destroy', 'show');
    });

    // Settings Management (Staff can view settings but limited editing)
    Route::middleware('permission:manage_settings')->group(function () {
        Route::get('settings', [SettingController::class, 'index'])->name('setting.index');
        Route::get('states-list', [SettingController::class, 'getStates'])->name('states-list');
        Route::get('cities-list', [SettingController::class, 'getCities'])->name('cities-list');
    });

    // Additional management routes (view-only for staff)
    Route::middleware('permission:manage_roles')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    });

    Route::middleware('permission:manage_countries')->group(function () {
        Route::get('countries', [CountryController::class, 'index'])->name('countries.index');
        Route::get('countries/{country}', [CountryController::class, 'show'])->name('countries.show');
    });

    // Activity Logs (Staff can view activity logs)
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/export/csv', [ActivityLogController::class, 'export'])->name('export');
        Route::get('/{activityLog}', [ActivityLogController::class, 'show'])->name('show');
    });
});
