<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicineBillController;
use App\Http\Controllers\PurchaseMedicineController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientQueueController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\DoctorSessionController;
use App\Http\Controllers\RequestDocumentsController;
use App\Http\Controllers\Front\EnquiryController;
use App\Http\Controllers\Front\CMSController;
use App\Http\Controllers\Front\SliderController;
use App\Http\Controllers\Front\SubscribeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ClinicScheduleController;
use App\Http\Controllers\HolidayContoller;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::prefix('staff')->name('staff.')->middleware('auth', 'xss', 'checkUserStatus', 'role:staff')->group(function () {

    // Staff Dashboard - accessible to all staff members
    Route::get('/dashboard', [DashboardController::class, 'staffDashboard'])->name('dashboard');

    // Patient Management (Staff can manage patients but with limited access)
    Route::middleware('permission:manage_patients')->group(function () {
        Route::resource('patients', PatientController::class);
        Route::get('patient-appointments', [PatientController::class, 'patientAppointment'])->name('patients.appointment');
        Route::get('patients/{patient}/history', [PatientController::class, 'showMyHistory'])->name('patients.showMyHistory');
        // Email verification for patients
        Route::post('/email/verification-notification/{userId}', [UserController::class, 'resendEmailVerification'])->name('resend.email.verification');
    });

    // Patient Queue Management (Nurse/Staff can manage queue)
    Route::middleware('permission:manage_patients')->group(function () {
        Route::resource('patient-queue', PatientQueueController::class);
        Route::post('patient-queue/{patientQueue}/call-next', [PatientQueueController::class, 'callNext'])->name('patient-queue.call-next');
        Route::post('patient-queue/{patientQueue}/complete', [PatientQueueController::class, 'complete'])->name('patient-queue.complete');
    });

    // Appointment Management (Staff can manage appointments)
    Route::middleware('permission:manage_appointments')->group(function () {
        Route::resource('appointments', AppointmentController::class)->except(['edit', 'update']);
        Route::post('appointments/{appointment}', [AppointmentController::class, 'changeStatus'])->name('staff.change-status');
        Route::post('appointments-payment/{id}', [AppointmentController::class, 'changePaymentStatus'])->name('change-payment-status');
        Route::get('appointment-pdf/{id}', [AppointmentController::class, 'appointmentPdf'])->name('appointmentPdf');
        Route::get('appointments-calendar-view', [AppointmentController::class, 'appointmentCalendar'])->name('appointments.calendar-view');
        Route::get('appointments-calendar', [AppointmentController::class, 'appointmentCalendar'])->name('appointments.calendar');
    });

    // Transaction Management (View only for staff)
    Route::middleware('permission:manage_transactions')->group(function () {
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
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
        Route::get('doctor/session', [UserController::class, 'sessionData'])->name('doctors.session');
        Route::get('doctors-appointment', [UserController::class, 'doctorAppointment'])->name('doctors.appointment');
        Route::post('/add-qualification', [UserController::class, 'addQualification'])->name('add.qualification');
        Route::put('doctor-status', [UserController::class, 'changeDoctorStatus'])->name('doctor.status');
    });

    // Patient Visits (Staff can manage visits)
    Route::middleware('permission:manage_patient_visits')->group(function () {
        Route::resource('visits', VisitController::class);
        Route::post('add-problem', [VisitController::class, 'addProblem'])->name('add.problem');
        Route::post('delete-problem/{problem}', [VisitController::class, 'deleteProblem'])->name('delete.problem');
        Route::post('add-observation', [VisitController::class, 'addObservation'])->name('add.observation');
        Route::post('delete-observation/{observation}', [VisitController::class, 'deleteObservation'])->name('delete.observation');
        Route::post('add-note', [VisitController::class, 'addNote'])->name('add.note');
        Route::post('delete-note/{note}', [VisitController::class, 'deleteNote'])->name('delete.note');
        Route::post('add-prescription', [VisitController::class, 'addPrescription'])->name('add.prescription');
        Route::post('delete-prescription/{prescription}', [VisitController::class, 'deletePrescription'])->name('delete.prescription');
        Route::get('edit-prescription/{prescription}', [VisitController::class, 'editPrescription'])->name('edit.prescription');
    });

    // Services Management (Staff can manage services)
    Route::middleware('permission:manage_services')->group(function () {
        Route::resource('services', ServiceController::class);
        Route::put('service-status', [ServiceController::class, 'changeServiceStatus'])->name('service.status');
        Route::resource('service-categories', ServiceCategoryController::class);
    });

    // Specializations (Staff can manage specializations)
    Route::middleware('permission:manage_specialties')->group(function () {
        Route::resource('specializations', SpecializationController::class);
    });

    // Doctor Sessions (Staff can manage doctor schedules)
    Route::middleware('permission:manage_doctor_sessions')->group(function () {
        Route::resource('doctor-sessions', DoctorSessionController::class);
        Route::get('/get-slot-by-gap', [DoctorSessionController::class, 'getSlotByGap'])->name('get.slot.by.gap');
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
    Route::get('appointments/{appointmentId}/prescription-create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::get('appointments/{appointmentId}/prescription-edit/{prescription}', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
    Route::post('prescription-medicine', [PrescriptionController::class, 'prescreptionMedicineStore'])->name('prescription.medicine.store');
    Route::post('prescriptions/{prescription}/active-deactive', [PrescriptionController::class, 'activeDeactiveStatus'])->name('prescription.status');
    Route::get('prescription-medicine-show/{id}', [PrescriptionController::class, 'prescriptionMedicineShowFunction'])->name('prescription.medicine.show');
    Route::get('prescription-pdf/{id}', [PrescriptionController::class, 'convertToPDF'])->name('prescriptions.pdf');

    // Medicine Management (Staff can manage medicines, categories, brands)
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
        Route::get('medicines-by-category', [MedicineController::class, 'getMedicinesByCategory'])->name('medicines.by.category');

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

    // Enquiry Management (Staff can manage enquiries)
    Route::get('enquiries', [EnquiryController::class, 'index'])->name('enquiries.index');
    Route::get('enquiries/{enquiry}', [EnquiryController::class, 'show'])->name('enquiries.show');
    Route::delete('enquiries/{enquiry}', [EnquiryController::class, 'destroy'])->name('enquiries.destroy');

    // CMS Management (Staff can manage CMS with limited access)
    Route::middleware('permission:manage_front_cms')->group(function () {
        Route::get('cms', [CMSController::class, 'index'])->name('cms.index');
        Route::post('cms', [CMSController::class, 'update'])->name('cms.update');

        // Banner/Slider management
        Route::resource('banner', SliderController::class)->except('create', 'store', 'destroy', 'show');

        // Subscribers management
        Route::get('subscribers', [SubscribeController::class, 'index'])->name('subscribers.index');
        Route::delete('subscribers/{subscribe}', [SubscribeController::class, 'destroy'])->name('subscribers.destroy');
    });

    // Settings Management (Staff can view settings but limited editing)
    Route::middleware('permission:manage_settings')->group(function () {
        Route::get('settings', [SettingController::class, 'index'])->name('setting.index');
        Route::get('states-list', [SettingController::class, 'getStates'])->name('states-list');
        Route::get('cities-list', [SettingController::class, 'getCities'])->name('cities-list');

        // Clinic Schedules
        Route::resource('clinic-schedules', ClinicScheduleController::class);

        // Holidays Management (Staff and Doctor can manage)
        Route::resource('holidays', HolidayContoller::class)->middleware('permission:manage_doctors_holiday');
    });

    // Additional management routes (view-only for staff)
    Route::middleware('permission:manage_roles')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    });

    Route::middleware('permission:manage_currencies')->group(function () {
        Route::get('currencies', [CurrencyController::class, 'index'])->name('currencies.index');
        Route::get('currencies/{currency}', [CurrencyController::class, 'show'])->name('currencies.show');
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
