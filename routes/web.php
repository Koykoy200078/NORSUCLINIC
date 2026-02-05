<?php

// Debug route
require __DIR__ . '/debug-profile.php';

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\AuthorizePaymentController;
use App\Http\Controllers\BarangayController;
use App\Http\Controllers\GenericController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ClinicScheduleController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorSessionController;
use App\Http\Controllers\Front\CMSController;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Front\SliderController;

use App\Http\Controllers\HolidayController;
use App\Http\Controllers\MedicineBillController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\PayTMController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\MedicineAvailabilityController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;
use App\Http\Controllers\RequestDocumentsController;

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
})->name('login');

Route::middleware('setLanguage')->group(function () {
    Route::get('/', [FrontController::class, 'medical'])->name('medical');
    Route::get('/medical-about-us', [FrontController::class, 'medicalAboutUs'])->name('medicalAboutUs');
    Route::get('/medical-services', [FrontController::class, 'medicalServices'])->name('medicalServices');
    Route::get('/medical-appointment', [FrontController::class, 'medicalAppointment'])->name('medicalAppointment');
    Route::get('/medical-doctors', [FrontController::class, 'medicalDoctors'])->name('medicalDoctors');
    Route::get('/medical-contact', [FrontController::class, 'medicalContact'])->name('medicalContact');
    Route::get('/terms-conditions', [FrontController::class, 'termsCondition'])->name('terms.conditions');
    Route::get('/privacy-policy', [FrontController::class, 'privacyPolicy'])->name('privacy.policy');
});
//Change language
Route::post('/change-language', [FrontController::class, 'changeLanguage'])->name('front.change.language');

//Dark Mode
Route::get('update-dark-mode', [UserController::class, 'updateDarkMode'])->name('update-dark-mode');

//Stripe route
Route::get(
    '/medical-payment-success',
    [AppointmentController::class, 'paymentSuccess']
)->name('medical-appointment-payment-success');
Route::get(
    '/medical-payment-failed',
    [AppointmentController::class, 'handleFailedPayment']
)->name('medical-appointment-failed-payment');

// Manually payment route
Route::get('/manually-payment', [AppointmentController::class, 'manuallyPayment'])->name('manually-payment');
Route::put('transaction-status', [TransactionController::class, 'changeTransactionStatus'])->name('transaction.status');

// paypal routes
Route::get('/paypal-payment', function () {
    return view('payments.paypal.index');
})->name('paypal.index');

Route::get('paypal-onboard', [PaypalController::class, 'onBoard'])->name('paypal.init');
Route::get('paypal-payment-success', [PaypalController::class, 'success'])->name('paypal.success');
Route::get('paypal-payment-failed', [PaypalController::class, 'failed'])->name('paypal.failed');

// Authorize Route
Route::get('authorize-onboard', [AuthorizePaymentController::class, 'onboard'])->name('authorize.init');
Route::post('authorize-do-payment', [AuthorizePaymentController::class, 'pay'])->name('authorize.onboard');
Route::get('authorize-payment-failed', [AuthorizePaymentController::class, 'failed'])->name('authorize.failed');

//Paytm Route
Route::get('/paytm-init', [PayTMController::class, 'initiate'])->name('paytm.init');
Route::post('/paytm-payment', [PayTMController::class, 'payment'])->name('make.payment');
Route::post('/paytm-callback', [PayTMController::class, 'paymentCallback'])->name('paytm.callback');
Route::get('paytm-payment-cancel', [PayTMController::class, 'failed'])->name('paytm.failed');

// Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');

Route::get('doctor-session-time', [DoctorSessionController::class, 'getDoctorSession'])->name('doctor-session-time');
Route::get('get-service', [ServiceController::class, 'getService'])->name('get-service');
Route::get('get-charge', [ServiceController::class, 'getCharge'])->name('get-charge');
Route::post(
    'front-appointment-book',
    [AppointmentController::class, 'frontAppointmentBook']
)->name('front.appointment.book');
Route::post(
    'medical-appointment',
    [AppointmentController::class, 'frontHomeAppointmentBook']
)->name('front.home.appointment.book');
Route::get('get-patient-name', [AppointmentController::class, 'getPatientName'])->name('get-patient-name');
//change Language
Route::post('update-language', [UserController::class, 'updateLanguage'])->name('change-language');

Route::get('doctor-appointment/{doctor}', [AppointmentController::class, 'doctorBookAppointment'])->name('doctorBookAppointment');
Route::get('service-appointment/{service}', [AppointmentController::class, 'serviceBookAppointment'])->name('serviceBookAppointment');

Route::post(
    '/notification/{notification}/read',
    [NotificationController::class, 'readNotification']
)->name('notifications.read');
Route::post(
    '/read-all-notification',
    [NotificationController::class, 'readAllNotification']
)->name('notifications.read.all');

Route::middleware('auth', 'xss', 'checkUserStatus')->group(function () {
    // Update profile
    Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('profile.setting');
    Route::put('/profile/update', [UserController::class, 'updateProfile'])->name('update.profile.setting');
    Route::put('/change-user-password', [UserController::class, 'changePassword'])->name('user.changePassword');
    Route::put('/email-notification', [UserController::class, 'emailNotification'])->name('emailNotification');
});

Route::get('cancel-appointment/{patient_id}/{appointment_unique_id}', [AppointmentController::class, 'cancelAppointment'])->name('cancelAppointment');

//get States and cities route
Route::get('get-states', [UserController::class, 'getStates'])->name('get-state');
Route::get('get-cities', [UserController::class, 'getCity'])->name('get-city');
Route::get('get-barangays', [UserController::class, 'getBarangays'])->name('get-barangay');

// ============================================================================
// ADMIN ROUTES
// ============================================================================
Route::prefix('admin')->middleware('auth', 'checkUserStatus', 'role:clinic_admin')->group(function () {

    // Admin Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:manage_admin_dashboard')->name('admin.dashboard');
    Route::get('/dashboard-patients', [DashboardController::class, 'getPatientList'])->name('patientData.dashboard');

    // Logs
    Route::get('logs', [LogViewerController::class, 'index']);

    // Impersonate
    Route::get('impersonate/{id}', [UserController::class, 'impersonate'])->name('impersonate');
    Route::get('impersonate-leave', [UserController::class, 'impersonateLeave'])->name('impersonate.leave');

    // Email Verified
    Route::post('email-verified', [UserController::class, 'emailVerified'])->name('emailVerified');
    Route::post('/email/verification-notification/{userId}', [UserController::class, 'resendEmailVerification'])->name('resend.email.verification');

    // Doctor route
    Route::middleware('permission:manage_doctors')->group(function () {
        Route::resource('doctors', UserController::class);
        Route::get('doctor/session', [UserController::class, 'sessionData'])->name('doctors.session');
        Route::get('doctors-appointment', [UserController::class, 'doctorAppointment'])->name('doctors.appointment');
        Route::post('/add-qualification', [UserController::class, 'addQualification'])->name('add.qualification');
        Route::put('doctor-status', [UserController::class, 'changeDoctorStatus'])->name('doctor.status');
    });

    // Countries routes
    Route::middleware('permission:manage_countries')->group(function () {
        Route::resource('countries', CountryController::class);
        Route::post('countries/{country}', [CountryController::class, 'update']);
    });

    // States routes
    Route::middleware('permission:manage_states')->group(function () {
        Route::resource('states', StateController::class);
        Route::post('states/{state}', [StateController::class, 'update']);
    });

    // Cities Routes
    Route::middleware('permission:manage_cities')->group(function () {
        Route::resource('cities', CityController::class);
    });

    // Barangays Routes
    Route::middleware('permission:manage_cities')->group(function () {
        Route::resource('barangays', BarangayController::class);
    });

    // Role route
    Route::middleware('permission:manage_roles')->group(function () {
        Route::resource('roles', RoleController::class);
    });

    // Settings routes
    Route::middleware('permission:manage_settings')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('setting.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('setting.update');
        Route::get('states-list', [SettingController::class, 'getStates'])->name('states-list');
        Route::get('cities-list', [SettingController::class, 'getCities'])->name('cities-list');
        Route::resource('clinic-schedules', ClinicScheduleController::class);
        Route::resource('holidays', HolidayController::class);
        Route::post('checkRecord', [ClinicScheduleController::class, 'checkRecord'])->name('checkRecord');
    });

    // Patient Routes
    Route::middleware('permission:manage_patients')->group(function () {
        Route::resource('patients', PatientController::class);
        Route::get('patient-appointments', [PatientController::class, 'patientAppointment'])->name('patients.appointment');
        Route::get('patients/{patient}/history', [PatientController::class, 'showMyHistory'])->name('patients.showMyHistory');
        Route::post('patients/{user}/reset-password', [PatientController::class, 'resetPassword'])->name('patients.reset.password');
    });

    // Request Documents
    Route::middleware('permission:manage_request_documents')->group(function () {
        Route::resource('request-documents', RequestDocumentsController::class);
        Route::get('/search-users', [RequestDocumentsController::class, 'searchUsers'])->name('search-users');
        Route::get('/get-last-consultation', [RequestDocumentsController::class, 'getLastConsultation'])->name('get-last-consultation');
        Route::get('request-documents/{id}/export-pdf', [RequestDocumentsController::class, 'exportPdf'])->name('request-documents.export-pdf');
    });

    // Activity Logs Routes
    Route::prefix('activity-logs')->group(function () {
        Route::get('/', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/export', [\App\Http\Controllers\ActivityLogController::class, 'export'])->name('activity-logs.export');
        Route::get('/{id}', [\App\Http\Controllers\ActivityLogController::class, 'show'])->name('activity-logs.show');
    });

    // Doctor Schedule Routes
    Route::middleware('permission:manage_doctor_sessions')->group(function () {
        Route::resource('doctor-sessions', DoctorSessionController::class);
        Route::get('/get-slot-by-gap', [DoctorSessionController::class, 'getSlotByGap'])->name('get.slot.by.gap');
    });

    // Specialization routes
    Route::middleware('permission:manage_specialties')->group(function () {
        Route::resource('specializations', SpecializationController::class);
    });

    // Services and Service Category route
    Route::middleware('permission:manage_services')->group(function () {
        Route::resource('services', ServiceController::class);
        Route::put('service-status', [ServiceController::class, 'changeServiceStatus'])->name('service.status');
        Route::resource('service-categories', ServiceCategoryController::class);
    });

    // Staff route
    Route::middleware('permission:manage_staff')->group(function () {
        Route::resource('staffs', StaffController::class);
    });

    // Appointment route
    Route::middleware('permission:manage_appointments')->group(function () {
        Route::resource('appointments', AppointmentController::class)->except(['edit', 'update']);
        Route::post('appointments/{appointment}', [AppointmentController::class, 'changeStatus'])->name('admin.change-status');
        Route::post('appointments-payment/{id}', [AppointmentController::class, 'changePaymentStatus'])->name('change-payment-status');
        Route::get('appointment-pdf/{id}', [AppointmentController::class, 'appointmentPdf'])->name('admin.appointmentPdf');
        Route::get('admin-appointments-calendar', [AppointmentController::class, 'appointmentCalendar'])->name('appointments.calendar');
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    });

    // Encounter route (Patient Visits)
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

    // CMS/Front Management
    Route::middleware('permission:manage_front_cms')->group(function () {
        Route::get('cms', [CMSController::class, 'index'])->name('cms.index');
        Route::post('cms', [CMSController::class, 'update'])->name('cms.update');
        Route::resource('banner', SliderController::class)->except('create', 'store', 'destroy', 'show');
    });

    // Prescription Management
    Route::resource('prescriptions', PrescriptionController::class)->except('create', 'edit', 'index');
    Route::get('appointments/{appointmentId}/prescription-create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::get('appointments/{appointmentId}/prescription-edit/{prescription}', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
    Route::post('prescription-medicine', [PrescriptionController::class, 'prescreptionMedicineStore'])->name('prescription.medicine.store');
    Route::post('prescriptions/{prescription}/active-deactive', [PrescriptionController::class, 'activeDeactiveStatus'])->name('prescription.status');
    Route::get('prescription-medicine-show/{id}', [PrescriptionController::class, 'prescriptionMedicineShowFunction'])->name('prescription.medicine.show');
    Route::get('prescription-pdf/{id}', [PrescriptionController::class, 'convertToPDF'])->name('prescriptions.pdf');

    // Patient Queue Management
    Route::middleware('permission:manage_patients')->group(function () {
        Route::get('patient-queue', [\App\Http\Controllers\PatientQueueController::class, 'index'])->name('patient-queue.index');
        Route::get('patient-queue/create', [\App\Http\Controllers\PatientQueueController::class, 'create'])->name('patient-queue.create');
        Route::post('patient-queue', [\App\Http\Controllers\PatientQueueController::class, 'store'])->name('patient-queue.store');
        Route::get('patient-queue/{patientQueue}/edit', [\App\Http\Controllers\PatientQueueController::class, 'edit'])->name('patient-queue.edit');
        Route::put('patient-queue/{patientQueue}', [\App\Http\Controllers\PatientQueueController::class, 'update'])->name('patient-queue.update');
        Route::delete('patient-queue/{patientQueue}', [\App\Http\Controllers\PatientQueueController::class, 'destroy'])->name('patient-queue.destroy');
        Route::post('patient-queue/{patientQueue}/call-next', [\App\Http\Controllers\PatientQueueController::class, 'callNext'])->name('patient-queue.call-next');
        Route::post('patient-queue/{patientQueue}/complete', [\App\Http\Controllers\PatientQueueController::class, 'complete'])->name('patient-queue.complete');
    });
});

// ============================================================================
// ADMIN MEDICINE ROUTES (No specific permission required, uses checkUserStatus)
// ============================================================================
Route::prefix('admin')->middleware('auth', 'checkUserStatus')->group(function () {
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

    // Medicine Availability
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

// ============================================================================
// STAFF ROUTES
// ============================================================================
Route::prefix('staff')->name('staff.')->middleware('auth', 'xss', 'checkUserStatus', 'role:staff')->group(function () {

    // Staff Dashboard
    Route::get('/dashboard', [DashboardController::class, 'staffDashboard'])->name('dashboard');

    // Patient Management
    Route::middleware('permission:manage_patients')->group(function () {
        Route::resource('patients', PatientController::class);
        Route::get('patient-appointments', [PatientController::class, 'patientAppointment'])->name('patients.appointment');
        Route::get('patients/{patient}/history', [PatientController::class, 'showMyHistory'])->name('patients.showMyHistory');
        Route::post('patients/{user}/reset-password', [PatientController::class, 'resetPassword'])->name('patients.reset.password');
        Route::post('/email/verification-notification/{userId}', [UserController::class, 'resendEmailVerification'])->name('resend.email.verification');
    });

    // Appointment Management
    Route::middleware('permission:manage_appointments')->group(function () {
        Route::resource('appointments', AppointmentController::class)->except(['edit', 'update']);
        Route::post('appointments/{appointment}', [AppointmentController::class, 'changeStatus'])->name('change-status');
        Route::post('appointments-payment/{id}', [AppointmentController::class, 'changePaymentStatus'])->name('change-payment-status');
        Route::get('appointment-pdf/{id}', [AppointmentController::class, 'appointmentPdf'])->name('appointmentPdf');
        Route::get('appointments-calendar', [AppointmentController::class, 'appointmentCalendar'])->name('appointments.calendar');
    });

    // Transaction Management
    Route::middleware('permission:manage_transactions')->group(function () {
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    });

    // Doctor Management
    Route::middleware('permission:manage_doctors')->group(function () {
        Route::resource('doctors', UserController::class);
        Route::get('doctor/session', [UserController::class, 'sessionData'])->name('doctors.session');
        Route::get('doctors-appointment', [UserController::class, 'doctorAppointment'])->name('doctors.appointment');
        Route::post('/add-qualification', [UserController::class, 'addQualification'])->name('add.qualification');
        Route::put('doctor-status', [UserController::class, 'changeDoctorStatus'])->name('doctor.status');
    });

    // Patient Visits
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

    // Services Management
    Route::middleware('permission:manage_services')->group(function () {
        Route::resource('services', ServiceController::class);
        Route::put('service-status', [ServiceController::class, 'changeServiceStatus'])->name('service.status');
        Route::resource('service-categories', ServiceCategoryController::class);
    });

    // Specializations
    Route::middleware('permission:manage_specialties')->group(function () {
        Route::resource('specializations', SpecializationController::class);
    });

    // Doctor Sessions
    Route::middleware('permission:manage_doctor_sessions')->group(function () {
        Route::resource('doctor-sessions', DoctorSessionController::class);
        Route::get('/get-slot-by-gap', [DoctorSessionController::class, 'getSlotByGap'])->name('get.slot.by.gap');
    });

    // Request Documents
    Route::get('request-documents/search-users', [RequestDocumentsController::class, 'searchUsers'])->name('request-documents.search-users');
    Route::get('request-documents/get-last-consultation', [RequestDocumentsController::class, 'getLastConsultation'])->name('request-documents.get-last-consultation');
    Route::middleware('permission:manage_request_documents')->group(function () {
        Route::get('request-documents/{id}/export-pdf', [RequestDocumentsController::class, 'exportPdf'])->name('request-documents.export-pdf');
        Route::resource('request-documents', RequestDocumentsController::class);
    });

    // Prescription Management
    Route::resource('prescriptions', PrescriptionController::class)->except('create', 'edit', 'index');
    Route::get('appointments/{appointmentId}/prescription-create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::get('appointments/{appointmentId}/prescription-edit/{prescription}', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
    Route::post('prescription-medicine', [PrescriptionController::class, 'prescreptionMedicineStore'])->name('prescription.medicine.store');
    Route::post('prescriptions/{prescription}/active-deactive', [PrescriptionController::class, 'activeDeactiveStatus'])->name('prescription.status');
    Route::get('prescription-medicine-show/{id}', [PrescriptionController::class, 'prescriptionMedicineShowFunction'])->name('prescription.medicine.show');
    Route::get('prescription-pdf/{id}', [PrescriptionController::class, 'convertToPDF'])->name('prescriptions.pdf');

    // Medicine Management
    Route::middleware('permission:manage_medicines')->group(function () {
        Route::resource('categories', CategoryController::class)->parameters(['categories' => 'category']);
        Route::post('categories/{category_id}/active-deactive', [CategoryController::class, 'activeDeActiveCategory'])->name('active.deactive');
        Route::resource('generics', GenericController::class);
        Route::resource('medicines', MedicineController::class)->parameters(['medicines' => 'medicine']);
        Route::get('medicines-show-modal/{medicine}', [MedicineController::class, 'showModal'])->name('medicines.show.modal');
        Route::get('medicines-uses-check/{medicine}', [MedicineController::class, 'checkUseOfMedicine'])->name('check.use.medicine');
        Route::get('medicines-by-category', [MedicineController::class, 'getMedicinesByCategory'])->name('medicines.by.category');
        Route::resource('medicine-availability', MedicineAvailabilityController::class)->parameters(['categories' => 'category']);
        Route::get('export-medicine-availability', [MedicineAvailabilityController::class, 'purchaseMedicineExport'])->name('medicine-availability.excel');
        Route::get('get-medicine/{medicine}', [MedicineAvailabilityController::class, 'getMedicine'])->name('get-medicine');
        Route::get('used-medicine', [MedicineAvailabilityController::class, 'usedMedicine'])->name('used-medicine.index');
    });

    // CMS Management
    Route::middleware('permission:manage_front_cms')->group(function () {
        Route::get('cms', [CMSController::class, 'index'])->name('cms.index');
        Route::post('cms', [CMSController::class, 'update'])->name('cms.update');
        Route::resource('banner', SliderController::class)->except('create', 'store', 'destroy', 'show');
    });

    // Settings Management
    Route::middleware('permission:manage_settings')->group(function () {
        Route::get('settings', [SettingController::class, 'index'])->name('setting.index');
        Route::get('states-list', [SettingController::class, 'getStates'])->name('states-list');
        Route::get('cities-list', [SettingController::class, 'getCities'])->name('cities-list');
        Route::resource('clinic-schedules', ClinicScheduleController::class);
        Route::resource('holidays', HolidayController::class)->middleware('permission:manage_doctors_holiday');
    });

    // Roles Management
    Route::middleware('permission:manage_roles')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    });

    // Countries Management
    Route::middleware('permission:manage_countries')->group(function () {
        Route::get('countries', [CountryController::class, 'index'])->name('countries.index');
        Route::get('countries/{country}', [CountryController::class, 'show'])->name('countries.show');
    });
});

// Note: Doctor routes are defined in routes/doctor.php to avoid duplication
// The file is required below with other role-specific route files

Route::get('delete-old-patients', [PatientController::class, 'deleteOldPatient']);

require __DIR__ . '/auth.php';
require __DIR__ . '/patient.php';
require __DIR__ . '/staff.php';
require __DIR__ . '/doctor.php';
require __DIR__ . '/upgrade.php';
