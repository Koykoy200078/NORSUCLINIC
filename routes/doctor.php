<?php

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
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::prefix('doctors')->name('doctors.')->middleware('auth', 'xss', 'checkUserStatus', 'role:doctor')->group(function () {

    Route::get('/patients-detail/{patient}', [PatientController::class, 'show'])->name('patient.detail');

    //doctor dashboard route
    Route::get('/dashboard', [DashboardController::class, 'doctorDashboard'])->name('dashboard');
    Route::get(
        '/doctor-dashboard',
        [DashboardController::class, 'getDoctorAppointment']
    )->name('appointment.dashboard');

    // Patient Queue (Doctors can view and update queue)
    Route::get('patient-queue', [PatientQueueController::class, 'doctorQueue'])->name('patient-queue.index');
    Route::post('patient-queue/{patientQueue}/call-next', [PatientQueueController::class, 'callNext'])->name('patient-queue.call-next');
    Route::post('patient-queue/{patientQueue}/complete', [PatientQueueController::class, 'complete'])->name('patient-queue.complete');
    Route::get('patient-queue/{patientQueue}/consultation', [PatientQueueController::class, 'viewConsultation'])->name('patient-queue.view-consultation');

    Route::get('doctors/{doctor}', [UserController::class, 'show'])->name('doctors.detail');

    // Route for Prescription
    Route::resource('prescriptions', PrescriptionController::class)->except(['create', 'edit']);
    Route::get('patients/{patientId}/prescription-create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::get('prescriptions/{prescription}/edit', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
    Route::post('prescription-medicine', [PrescriptionController::class, 'prescreptionMedicineStore'])->name('prescription.medicine.store');
    Route::post('prescriptions/{prescription}/active-deactive', [PrescriptionController::class, 'activeDeactiveStatus'])->name('prescription.status');
    Route::get('prescription-medicine-show/{id}', [PrescriptionController::class, 'prescriptionMedicineShowFunction'])->name('prescription.medicine.show');
    Route::get('prescription-pdf/{id}', [PrescriptionController::class, 'convertToPDF'])->name('prescriptions.pdf');

    // Patient Management (Doctors can manage patients)
    Route::middleware('permission:manage_patients')->group(function () {
        Route::resource('patients', PatientController::class);
        Route::get('patients/{patient}/history', [PatientController::class, 'showMyHistory'])->name('patients.showMyHistory');
        Route::post('patients/{user}/reset-password', [PatientController::class, 'resetPassword'])->name('patients.reset.password');
        // Email verification for patients
        Route::post('/email/verification-notification/{userId}', [UserController::class, 'resendEmailVerification'])->name('resend.email.verification');
    });

    // Specializations (Doctors can manage specializations)
    Route::middleware('permission:manage_specialties')->group(function () {
        Route::resource('specializations', SpecializationController::class);
    });

    // Search users route (moved outside middleware for testing)
    Route::get('request-documents/search-users', [RequestDocumentsController::class, 'searchUsers'])->name('request-documents.search-users');
    Route::get('request-documents/get-last-consultation', [RequestDocumentsController::class, 'getLastConsultation'])->name('request-documents.get-last-consultation');

    // Request Documents (Doctors can manage)
    Route::middleware('permission:manage_request_documents')->group(function () {
        Route::get('request-documents/{id}/export-pdf', [RequestDocumentsController::class, 'exportPdf'])->name('request-documents.export-pdf');
        Route::resource('request-documents', RequestDocumentsController::class);
    });

    // Medicine Management (Doctors can manage medicines, categories, generics)
    Route::middleware('permission:manage_medicines')->group(function () {
        // Medicine Categories
        Route::resource('categories', CategoryController::class)->parameters(['categories' => 'category']);
        Route::post('categories/{category_id}/active-deactive', [CategoryController::class, 'activeDeActiveCategory'])->name('active.deactive');

        // Medicine Generics
        Route::resource('generics', GenericController::class);

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

    // Activity Logs (Doctors can view activity logs)
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/export/csv', [ActivityLogController::class, 'export'])->name('export');
        Route::get('/{activityLog}', [ActivityLogController::class, 'show'])->name('show');
    });
});
