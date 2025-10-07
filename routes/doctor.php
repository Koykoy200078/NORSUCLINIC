<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicineBillController;
use App\Http\Controllers\PurchaseMedicineController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorSessionController;
use App\Http\Controllers\HolidayContoller;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\RequestDocumentsController;
use Illuminate\Support\Facades\Route;

Route::prefix('doctors')->name('doctors.')->middleware('auth', 'xss', 'checkUserStatus', 'role:doctor')->group(function () {

    Route::get('/patients-detail/{patient}', [PatientController::class, 'show'])->name('patient.detail');

    //doctor dashboard route
    Route::get('/dashboard', [DashboardController::class, 'doctorDashboard'])->name('dashboard');
    Route::get(
        '/doctor-dashboard',
        [DashboardController::class, 'getDoctorAppointment']
    )->name('appointment.dashboard');

    // Appointment Management (Doctors can fully manage appointments)
    Route::middleware('permission:manage_appointments')->group(function () {
        // Full CRUD access - removed view-only restrictions
        Route::resource('appointments', AppointmentController::class);
        Route::get('appointments', [AppointmentController::class, 'doctorAppointment'])->name('appointments');
        Route::get('appointments-calendar', [AppointmentController::class, 'doctorAppointmentCalendar'])->name('appointments.calendar');
        Route::get('appointments/{appointment}', [AppointmentController::class, 'appointmentDetail'])->name('appointment.detail');
        Route::get('appointment-pdf/{id}', [AppointmentController::class, 'appointmentPdf'])->name('appointmentPdf');
        Route::post('appointments/{appointment}', [AppointmentController::class, 'changeStatus'])->name('change-status');
        Route::post('appointments-payment/{id}', [AppointmentController::class, 'changePaymentStatus'])->name('change-payment-status');
        Route::get('appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointment.detail');
    });

    // Doctor Session Management (Doctors can manage their sessions)
    Route::middleware('permission:manage_doctor_sessions')->group(function () {
        Route::get('doctor-session-time', [DoctorSessionController::class, 'getDoctorSession'])->name('doctor-session-time');
        Route::resource('doctor-sessions', DoctorSessionController::class);
        Route::get('get-slot-by-gap', [DoctorSessionController::class, 'getSlotByGap'])->name('get.slot.by.gap');
        Route::get('doctor-schedule-edit', [DoctorSessionController::class, 'doctorScheduleEdit'])->name('doctor.schedule.edit');
    });

    // Patient Visits (Doctors can manage visits)
    Route::middleware('permission:manage_patient_visits')->group(function () {
        Route::resource('visits', VisitController::class);
        Route::post('add-problem', [VisitController::class, 'addProblem'])->name('visits.add.problem');
        Route::post('delete-problem/{problem}', [VisitController::class, 'deleteProblem'])->name('visits.delete.problem');
        Route::post('add-observation', [VisitController::class, 'addObservation'])->name('visits.add.observation');
        Route::post('delete-observation/{observation}', [VisitController::class, 'deleteObservation'])->name('visits.delete.observation');
        Route::post('add-note', [VisitController::class, 'addNote'])->name('visits.add.note');
        Route::post('delete-note/{note}', [VisitController::class, 'deleteNote'])->name('visits.delete.note');
        Route::post('add-prescription', [VisitController::class, 'addPrescription'])->name('visits.add.prescription');
        Route::post('delete-prescription/{prescription}', [VisitController::class, 'deletePrescription'])->name('visits.delete.prescription');
        Route::get('edit-prescription/{prescription}', [VisitController::class, 'editPrescription'])->name('visits.edit.prescription');
    });

    Route::get('patient-appointments', [PatientController::class, 'patientAppointment'])->name('patients.appointment');
    Route::get('doctors/{doctor}', [UserController::class, 'show'])->name('doctors.detail');
    Route::get('doctors-appointment', [UserController::class, 'doctorAppointment'])->name('doctors.appointment');

    // Transactions (Doctors can view transactions)
    Route::middleware('permission:manage_transactions')->group(function () {
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    });

    // Holiday Management (Doctors can manage their holidays)
    Route::middleware('permission:manage_doctors_holiday')->group(function () {
        Route::get('holidays', [HolidayContoller::class, 'holiday'])->name('holiday');
        Route::get('holidays/create', [HolidayContoller::class, 'doctorCreate'])->name('holiday-create');
        Route::post('holidays/create', [HolidayContoller::class, 'doctorStore'])->name('holiday-store');
        Route::delete('holidays/delete/{holiday}', [HolidayContoller::class, 'doctorDestroy'])->name('holiday-destroy');
    });

    // Route for Prescription
    // Full CRUD access - excluded create/edit from resource as they need appointment context
    Route::resource('prescriptions', PrescriptionController::class)->except(['create', 'edit']);
    Route::get('appointments/{appointmentId}/prescription-create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::get('appointments/{appointmentId}/prescription-edit/{prescription}', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
    Route::post('prescription-medicine', [PrescriptionController::class, 'prescreptionMedicineStore'])->name('prescription.medicine.store');
    Route::post('prescriptions/{prescription}/active-deactive', [PrescriptionController::class, 'activeDeactiveStatus'])->name('prescription.status');
    Route::get('prescription-medicine-show/{id}', [PrescriptionController::class, 'prescriptionMedicineShowFunction'])->name('prescription.medicine.show');
    Route::get('prescription-pdf/{id}', [PrescriptionController::class, 'convertToPDF'])->name('prescriptions.pdf');

    // Patient Management (Doctors can manage patients)
    Route::middleware('permission:manage_patients')->group(function () {
        Route::resource('patients', PatientController::class);
        Route::get('patients/{patient}/history', [PatientController::class, 'showMyHistory'])->name('patients.showMyHistory');
        // Email verification for patients
        Route::post('/email/verification-notification/{userId}', [UserController::class, 'resendEmailVerification'])->name('resend.email.verification');
    });

    // Services Management (Doctors can manage services)
    Route::middleware('permission:manage_services')->group(function () {
        Route::resource('services', ServiceController::class);
        Route::put('service-status', [ServiceController::class, 'changeServiceStatus'])->name('service.status');
        Route::resource('service-categories', ServiceCategoryController::class);
    });

    // Specializations (Doctors can manage specializations)
    Route::middleware('permission:manage_specialties')->group(function () {
        Route::resource('specializations', SpecializationController::class);
    });

    // Search users route (moved outside middleware for testing)
    Route::get('request-documents/search-users', [RequestDocumentsController::class, 'searchUsers'])->name('request-documents.search-users');

    // Request Documents (Doctors can manage)
    Route::middleware('permission:manage_request_documents')->group(function () {
        Route::get('request-documents/{id}/export-pdf', [RequestDocumentsController::class, 'exportPdf'])->name('request-documents.export-pdf');
        Route::resource('request-documents', RequestDocumentsController::class);
    });

    // Medicine Management (Doctors can manage medicines, categories, brands)
    Route::middleware('permission:manage_medicines')->group(function () {
        // Medicine Categories
        Route::resource('categories', CategoryController::class)->parameters(['categories' => 'category']);
        Route::post('categories/{category_id}/active-deactive', [CategoryController::class, 'activeDeActiveCategory'])->name('active.deactive');

        // Medicine Brands
        Route::resource('brands', BrandController::class);

        // Medicines
        Route::resource('medicines', MedicineController::class)->parameters(['medicines' => 'medicine']);
        Route::get('medicines-show-modal/{medicine}', [MedicineController::class, 'showModal'])->name('medicines.show.modal');
        Route::get('medicines-uses-check/{medicine}', [MedicineController::class, 'checkUseOfMedicine'])->name('check.use.medicine');

        // Medicine Purchase
        Route::resource('medicine-purchase', PurchaseMedicineController::class)->parameters(['categories' => 'category']);
        Route::get('export-medicine-purchase', [PurchaseMedicineController::class, 'purchaseMedicineExport'])->name('purchase-medicine.excel');
        Route::get('get-medicine/{medicine}', [PurchaseMedicineController::class, 'getMedicine'])->name('get-medicine');
        Route::get('used-medicine', [PurchaseMedicineController::class, 'usedMedicine'])->name('used-medicine.index');

        // Medicine History
        Route::resource('medicine-history', MedicineBillController::class);
        Route::post('medicine-history/store-patient', [MedicineBillController::class, 'storePatient'])->name('store.patient');
        Route::get('medicine-history-pdf/{id}', [MedicineBillController::class, 'convertToPDF'])->name('medicine.bill.pdf');
        Route::get('get-medicine-category/{category}', [MedicineBillController::class, 'getMedicineCategory'])->name('get-medicine-category');
    });
});
