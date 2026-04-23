<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\GenericController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicineDispensingManagementController;
use App\Http\Controllers\DispenseRecordController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientQueueController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\DocumentIssuanceController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\LabRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('doctors')->name('doctors.')->middleware('auth', 'xss', 'checkUserStatus', 'role:doctor')->group(function () {

    Route::get('/patients-detail/{patient}', [PatientController::class, 'show'])->name('patient.detail');

    //doctor dashboard route
    Route::get('/dashboard', [DashboardController::class, 'doctorDashboard'])->name('dashboard');

    // Patient Queue (Doctors can view and update queue)
    Route::get('patient-queue', [PatientQueueController::class, 'doctorQueue'])->name('patient-queue.index');
    Route::get('patient-queue/refresh', [PatientQueueController::class, 'doctorQueuePartial'])->name('patient-queue.refresh');
    Route::post('patient-queue/{patientQueue}/call-next', [PatientQueueController::class, 'callNext'])->name('patient-queue.call-next');
    Route::post('patient-queue/{patientQueue}/complete', [PatientQueueController::class, 'complete'])->name('patient-queue.complete');
    Route::get('patient-queue/{patientQueue}/consultation', [PatientQueueController::class, 'viewConsultation'])->name('patient-queue.view-consultation');

    Route::get('doctors/{doctor}', [UserController::class, 'show'])->name('doctors.detail');

    // Route for Prescription
    Route::middleware('permission:manage_request_documents')->group(function () {
        Route::resource('prescriptions', PrescriptionController::class)->except(['create', 'edit']);
        Route::get('patients/{patientId}/prescription-create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
        Route::get('prescriptions/{prescription}/edit', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
        Route::post('prescription-medicine', [PrescriptionController::class, 'prescreptionMedicineStore'])->name('prescription.medicine.store');
        Route::post('prescriptions/{prescription}/active-deactive', [PrescriptionController::class, 'activeDeactiveStatus'])->name('prescription.status');
        Route::get('prescription-medicine-show/{id}', [PrescriptionController::class, 'prescriptionMedicineShowFunction'])->name('prescription.medicine.show');
        Route::get('prescription-pdf/{id}', [PrescriptionController::class, 'convertToPDF'])->name('prescriptions.pdf');
        Route::post('prescriptions/{prescription}/dispense', [PrescriptionController::class, 'dispense'])->name('prescriptions.dispense');
    });

    // Patient Management (Doctors can manage patients)
    Route::middleware('permission:manage_patients')->group(function () {
        Route::post('patients/{patient}/restore', [PatientController::class, 'restore'])->name('patients.restore');
        Route::resource('patients', PatientController::class);
        Route::get('patients/{patient}/history', [PatientController::class, 'showMyHistory'])->name('patients.showMyHistory');
        Route::post('patients/{user}/reset-password', [PatientController::class, 'resetPassword'])->name('patients.reset.password');
        // Email verification for patients
        Route::post('/email/verification-notification/{userId}', [UserController::class, 'resendEmailVerification'])->name('resend.email.verification');
        // Toggle email_verified_at (doctor managing patients)
        Route::post('/email-verified', [UserController::class, 'emailVerified'])->name('emailVerified');
    });

    // Specializations (Doctors can manage specializations)
    Route::middleware('permission:manage_specialties')->group(function () {
        Route::resource('specializations', SpecializationController::class);
    });

    // Search users route (moved outside middleware for testing)
    Route::get('document-issuances/search-users', [DocumentIssuanceController::class, 'searchUsers'])->name('document-issuances.search-users');
    Route::get('document-issuances/get-last-consultation', [DocumentIssuanceController::class, 'getLastConsultation'])->name('document-issuances.get-last-consultation');
    Route::get('document-issuances/get-last-medical-certificate', [DocumentIssuanceController::class, 'getLastMedicalCertificate'])->name('document-issuances.get-last-medical-certificate');
    Route::get('lab-requests/search-users', [LabRequestController::class, 'searchUsers'])->name('lab-requests.search-users');

    // Request Documents (Doctors can manage)
    Route::middleware('permission:manage_request_documents')->group(function () {
        Route::get('document-issuances/{id}/export-pdf', [DocumentIssuanceController::class, 'exportPdf'])->name('document-issuances.export-pdf');
        Route::resource('document-issuances', DocumentIssuanceController::class);

        // Lab Requests
        Route::post('lab-requests/{lab_request}/status', [LabRequestController::class, 'updateStatus'])->name('lab-requests.update-status');
        Route::get('lab-requests/{id}/pdf', [LabRequestController::class, 'exportPdf'])->name('lab-requests.pdf');
        Route::resource('lab-requests', LabRequestController::class);
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
            return redirect()->route('doctors.medicine-dispensing.index', ['tab' => 'stock-out']);
        })->name('used-medicine.index');

        // Dispense Records
        Route::resource('dispense-records', DispenseRecordController::class)->parameters(['dispense-records' => 'medicine_history']);
        Route::post('dispense-records/store-patient', [DispenseRecordController::class, 'storePatient'])->name('dispense-records.store-patient');
        Route::get('dispense-records-pdf/{id}', [DispenseRecordController::class, 'convertToPDF'])->name('dispense-records.pdf');
        Route::get('dispense-records/by-category/{category}', [DispenseRecordController::class, 'getMedicineCategory'])->name('dispense-records.by-category');

        // Medicine History (legacy route names)
        Route::resource('medicine-history', DispenseRecordController::class);
        Route::post('medicine-history/store-patient', [DispenseRecordController::class, 'storePatient'])->name('store.patient');
        Route::get('medicine-history-pdf/{id}', [DispenseRecordController::class, 'convertToPDF'])->name('medicine.bill.pdf');
        Route::get('get-medicine-category/{category}', [DispenseRecordController::class, 'getMedicineCategory'])->name('get-medicine-category');
    });

    // Activity Logs (Doctors can view activity logs)
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/export/csv', [ActivityLogController::class, 'export'])->name('export');
        Route::get('/{activityLog}', [ActivityLogController::class, 'show'])->name('show');
    });
});
