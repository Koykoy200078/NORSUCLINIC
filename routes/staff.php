<?php

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
use App\Http\Controllers\LabRequestController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::prefix('staff')->name('staff.')->middleware('auth', 'xss', 'checkUserStatus', 'role:staff|nurse')->group(function () {

    // Staff Dashboard - accessible to all staff members
    Route::get('/dashboard', [DashboardController::class, 'staffDashboard'])->name('dashboard')->middleware('staff.module:dashboard');

    // Patient Management (Staff can manage patients but with limited access)
    Route::middleware(['permission:manage_patients', 'staff.module:patients'])->group(function () {
        Route::post('patients/{patient}/restore', [PatientController::class, 'restore'])->name('patients.restore');
        Route::resource('patients', PatientController::class);
        Route::get('patients/{patient}/history', [PatientController::class, 'showMyHistory'])->name('patients.showMyHistory');
        // Email verification for patients
        Route::post('/email/verification-notification/{userId}', [UserController::class, 'resendEmailVerification'])->name('resend.email.verification');
        // Toggle email_verified_at for any user (staff managing doctors/patients)
        Route::post('/email-verified', [UserController::class, 'emailVerified'])->name('emailVerified');
    });

    // Patient Queue Management (Nurse/Staff can manage queue)
    Route::middleware(['permission:manage_patients', 'staff.module:queue'])->group(function () {
        Route::get('patient-queue/refresh', [PatientQueueController::class, 'indexPartial'])->name('patient-queue.refresh');
        Route::resource('patient-queue', PatientQueueController::class)->except(['show']);
        Route::post('patient-queue/{patientQueue}/call-next', [PatientQueueController::class, 'callNext'])->name('patient-queue.call-next');
        Route::post('patient-queue/{patientQueue}/complete', [PatientQueueController::class, 'complete'])->name('patient-queue.complete');
    });

    // Doctor Management (Staff can manage doctors)
    Route::middleware(['permission:manage_doctors', 'staff.module:doctors'])->group(function () {
        Route::get('doctors', [UserController::class, 'index'])->name('doctors.index');
        Route::get('doctors/create', [UserController::class, 'create'])->name('doctors.create');
        Route::post('doctors', [UserController::class, 'store'])->name('doctors.store');
        Route::get('doctors/{doctor}', [UserController::class, 'show'])->name('doctors.show');
        Route::get('doctors/{doctor}/edit', [UserController::class, 'edit'])->name('doctors.edit');
        Route::match(['PUT', 'PATCH'], 'doctors/{doctor}', [UserController::class, 'update'])->name('doctors.update');
        Route::delete('doctors/{doctor}', [UserController::class, 'destroy'])->name('doctors.destroy');
        Route::post('/add-qualification', [UserController::class, 'addQualification'])->name('add.qualification');
        Route::put('doctor-status', [UserController::class, 'changeDoctorStatus'])->name('doctor.status');
        Route::post('doctors/{user}/reset-password', [UserController::class, 'resetPassword'])->name('doctors.reset.password');
    });

    // Specializations (Staff can manage specializations)
    Route::middleware(['permission:manage_specialties', 'staff.module:specializations'])->group(function () {
        Route::resource('specializations', SpecializationController::class)->except(['create', 'show']);
    });

    // Search users route (moved outside middleware for testing)
    Route::get('document-issuances/search-users', [DocumentIssuanceController::class, 'searchUsers'])
        ->name('document-issuances.search-users')
        ->middleware('staff.module:consultations,certificates');
    Route::get('document-issuances/get-last-consultation', [DocumentIssuanceController::class, 'getLastConsultation'])
        ->name('document-issuances.get-last-consultation')
        ->middleware('staff.module:consultations');
    Route::get('document-issuances/get-last-medical-certificate', [DocumentIssuanceController::class, 'getLastMedicalCertificate'])
        ->name('document-issuances.get-last-medical-certificate')
        ->middleware('staff.module:certificates');
    Route::get('lab-requests/search-users', [LabRequestController::class, 'searchUsers'])
        ->name('lab-requests.search-users')
        ->middleware('staff.module:lab_requests');

    // Request Documents - Consultations / Certificates
    Route::middleware(['permission:manage_request_documents', 'staff.module:document_issuances'])->group(function () {
        Route::get('document-issuances/{document_issuance}/export-pdf', [DocumentIssuanceController::class, 'exportPdf'])->name('document-issuances.export-pdf');
        Route::resource('document-issuances', DocumentIssuanceController::class);
    });

    // Lab Requests
    Route::middleware(['permission:manage_request_documents', 'staff.module:lab_requests'])->group(function () {
        Route::post('lab-requests/{lab_request}/status', [LabRequestController::class, 'updateStatus'])->name('lab-requests.update-status');
        Route::get('lab-requests/{id}/pdf', [LabRequestController::class, 'exportPdf'])->name('lab-requests.pdf');
        Route::resource('lab-requests', LabRequestController::class);
    });

    // Prescription Management (Staff can assist with prescriptions)
    Route::middleware(['permission:manage_request_documents', 'staff.module:prescriptions'])->group(function () {
        Route::resource('prescriptions', PrescriptionController::class)->except('create', 'edit');
        Route::get('patients/{patientId}/prescription-create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
        Route::get('prescriptions/{prescription}/edit', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
        Route::post('prescription-medicine', [PrescriptionController::class, 'prescreptionMedicineStore'])->name('prescription.medicine.store');
        Route::post('prescriptions/{prescription}/active-deactive', [PrescriptionController::class, 'activeDeactiveStatus'])->name('prescription.status');
        Route::post('prescriptions/{prescription}/dispense', [PrescriptionController::class, 'dispense'])->name('prescriptions.dispense');
        Route::get('prescription-medicine-show/{id}', [PrescriptionController::class, 'prescriptionMedicineShowFunction'])->name('prescription.medicine.show');
        Route::get('prescription-pdf/{id}', [PrescriptionController::class, 'convertToPDF'])->name('prescriptions.pdf');
    });

    // Inventory Management
    Route::middleware(['permission:manage_medicines', 'staff.module:inventory'])->group(function () {
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

        // Stock In (Medicine Purchasing)
        Route::resource('stock-in', StockInController::class);
        Route::get('export-stock-in', [StockInController::class, 'purchaseMedicineExport'])->name('stock-in.excel');
        Route::get('get-medicine/{medicine}', [StockInController::class, 'getMedicine'])->name('get-medicine');
    });

    // Dispensing Management
    Route::middleware(['permission:manage_medicines', 'staff.module:dispensing'])->group(function () {
        Route::get('medicine-dispensing-management', [MedicineDispensingManagementController::class, 'index'])->name('medicine-dispensing.index');
        Route::get('used-medicine', function () {
            return redirect()->route('staff.medicine-dispensing.index', ['tab' => 'stock-out']);
        })->name('used-medicine.index');

        // Dispense Records
        Route::resource('dispense-records', DispenseRecordController::class)->parameters(['dispense-records' => 'medicine_history']);
        Route::post('dispense-records/store-patient', [DispenseRecordController::class, 'storePatient'])->name('dispense-records.store-patient');
        Route::get('dispense-records-pdf/{id}', [DispenseRecordController::class, 'convertToPDF'])->name('dispense-records.pdf');
        Route::get('dispense-records/by-category/{category}', [DispenseRecordController::class, 'getMedicineCategory'])->name('dispense-records.by-category');

        // Medicine History (legacy URLs - redirect to dispense-records for backward compat)
        Route::redirect('medicine-history', '/staff/dispense-records');
        Route::redirect('medicine-history/{id}', '/staff/dispense-records/{id}');
    });

    // Activity Logs (Staff can view activity logs)
    Route::prefix('activity-logs')->name('activity-logs.')->middleware('staff.module:activity_logs')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/export/csv', [ActivityLogController::class, 'export'])->name('export');
        Route::get('/{activityLog}', [ActivityLogController::class, 'show'])->name('show');
    });
});
