<?php

/*
|--------------------------------------------------------------------------
| Patient self-service routes — DISABLED
|--------------------------------------------------------------------------
| Patients are record-only: they do not log in (patient login is rejected in
| AuthenticatedSessionController) and this file is NOT loaded — the
| `require __DIR__ . '/patient.php';` line in routes/web.php is commented out.
|
| Patient RECORDS (create/edit/view/history by admin, staff and doctor) live
| under the admin/staff/doctor route groups and are unaffected.
|
| The self-service routes below are preserved for reference / easy re-enable.
| To restore the patient portal: re-enable the require in routes/web.php and
| remove the patient-login block in AuthenticatedSessionController.
*/

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LabRequestController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('patients')->name('patients.')->middleware('auth', 'xss', 'checkUserStatus', 'role:patient')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'patientDashboard'])->name('dashboard');
    Route::post('/change-default-password', [DashboardController::class, 'changeDefaultPassword'])->name('change-default-password');
    // (Removed patients/dashboard-patients -> getPatientList: it was an unscoped copy of the
    //  admin patient-list endpoint that leaked EVERY patient's PII to any logged-in patient. E-IDOR-1.)

    // Route for Prescription
    Route::resource('prescriptions', PrescriptionController::class)->except('create', 'edit', 'index');
    Route::get('patients/{patientId}/prescription-create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::get('prescriptions/{prescription}/edit', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
    Route::post('prescription-medicine', [PrescriptionController::class, 'prescreptionMedicineStore'])->name('prescription.medicine.store');
    Route::post('prescriptions/{prescription}/active-deactive', [PrescriptionController::class, 'activeDeactiveStatus'])->name('prescription.status');
    Route::get('prescription-medicine-show/{id}', [PrescriptionController::class, 'prescriptionMedicineShowFunction'])->name('prescription.medicine.show');
    Route::get('prescription-pdf/{id}', [PrescriptionController::class, 'convertToPDF'])->name('prescriptions.pdf');

    // Lab Requests — patients can create, view their own requests and print PDF
    Route::get('lab-requests', [LabRequestController::class, 'index'])->name('lab-requests.index');
    Route::get('lab-requests/create', [LabRequestController::class, 'create'])->name('lab-requests.create');
    Route::post('lab-requests', [LabRequestController::class, 'store'])->name('lab-requests.store');
    Route::get('lab-requests/{lab_request}', [LabRequestController::class, 'show'])->name('lab-requests.show');
    Route::get('lab-requests/{id}/pdf', [LabRequestController::class, 'exportPdf'])->name('lab-requests.pdf');
});

