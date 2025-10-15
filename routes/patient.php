<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorSessionController;
use App\Http\Controllers\PatientAppointmentController;
use App\Http\Controllers\PatientVisitController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('patients')->name('patients.')->middleware('auth', 'xss', 'checkUserStatus', 'role:patient')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'patientDashboard'])->name('dashboard');
    Route::get(
        '/dashboard-patients',
        [DashboardController::class, 'getPatientList']
    )->name('patientData.dashboard');

    Route::resource('appointments', AppointmentController::class)->except(['index', 'edit', 'update']);
    Route::get(
        'appointment-pdf/{id}',
        [AppointmentController::class, 'appointmentPdf']
    )->name('appointmentPdf');
    Route::get('appointments', [PatientAppointmentController::class, 'index'])->name('patient-appointments-index');

    Route::get(
        'doctor-session-time',
        [DoctorSessionController::class, 'getDoctorSession']
    )->name('doctor-session-time');
    Route::get('get-service', [ServiceController::class, 'getService'])->name('get-service');
    Route::get('get-charge', [ServiceController::class, 'getCharge'])->name('get-charge');

    //        Route::get('appointment-cancel', [AppointmentController::class, 'cancelStatus'])->name('cancel-status');
    Route::get(
        'patient-appointments-calendar',
        [AppointmentController::class, 'patientAppointmentCalendar']
    )->name('appointments.calendar');
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('appointment-cancel', [AppointmentController::class, 'cancelStatus'])->name('cancel-status');
    Route::get('doctors/{doctor}', [UserController::class, 'show'])->name('doctor.detail');
    Route::get(
        'appointments/{appointment}',
        [AppointmentController::class, 'show']
    )->name('appointment.detail');
    Route::post(
        'appointment-payment',
        [AppointmentController::class, 'appointmentPayment']
    )->name('appointment-payment');

    Route::get('patient-visits', [PatientVisitController::class, 'index'])->name('patient.visits.index');
    Route::get(
        'patient-visits/{patientVisit}',
        [PatientVisitController::class, 'show']
    )->name('patient.visits.show');

    // Route for Prescription
    Route::resource('prescriptions', PrescriptionController::class)->except('create', 'edit', 'index');
    Route::get('appointments/{appointmentId}/prescription-create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::get('appointments/{appointmentId}/prescription-edit/{prescription}', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
    Route::post('prescription-medicine', [PrescriptionController::class, 'prescreptionMedicineStore'])->name('prescription.medicine.store');
    Route::post('prescriptions/{prescription}/active-deactive', [PrescriptionController::class, 'activeDeactiveStatus'])->name('prescription.status');
    Route::get('prescription-medicine-show/{id}', [PrescriptionController::class, 'prescriptionMedicineShowFunction'])->name('prescription.medicine.show');
    Route::get('prescription-pdf/{id}', [PrescriptionController::class, 'convertToPDF'])->name('prescriptions.pdf');
});
