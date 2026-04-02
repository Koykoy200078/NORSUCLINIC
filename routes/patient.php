<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('patients')->name('patients.')->middleware('auth', 'xss', 'checkUserStatus', 'role:patient')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'patientDashboard'])->name('dashboard');
    Route::post('/change-default-password', [DashboardController::class, 'changeDefaultPassword'])->name('change-default-password');
    Route::get(
        '/dashboard-patients',
        [DashboardController::class, 'getPatientList']
    )->name('patientData.dashboard');

    // Route for Prescription
    Route::resource('prescriptions', PrescriptionController::class)->except('create', 'edit', 'index');
    Route::get('patients/{patientId}/prescription-create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::get('prescriptions/{prescription}/edit', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
    Route::post('prescription-medicine', [PrescriptionController::class, 'prescreptionMedicineStore'])->name('prescription.medicine.store');
    Route::post('prescriptions/{prescription}/active-deactive', [PrescriptionController::class, 'activeDeactiveStatus'])->name('prescription.status');
    Route::get('prescription-medicine-show/{id}', [PrescriptionController::class, 'prescriptionMedicineShowFunction'])->name('prescription.medicine.show');
    Route::get('prescription-pdf/{id}', [PrescriptionController::class, 'convertToPDF'])->name('prescriptions.pdf');
});
