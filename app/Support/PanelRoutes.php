<?php

namespace App\Support;

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DispenseRecordController;
use App\Http\Controllers\DocumentIssuanceController;
use App\Http\Controllers\GenericController;
use App\Http\Controllers\LabRequestController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicineDispensingManagementController;
use App\Http\Controllers\MedsOnlyController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientQueueController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/** Shared module definitions; the caller supplies its panel's prefix, name and authentication group. */
class PanelRoutes
{
    public static function dashboard(string $panel): void
    {
        $action = ['admin' => 'index', 'staff' => 'staffDashboard', 'doctor' => 'doctorDashboard'][$panel];
        $route = Route::get('/dashboard', [DashboardController::class, $action])
            ->name($panel === 'admin' ? 'admin.dashboard' : 'dashboard');
        if ($panel === 'admin') {
            $route->middleware('permission:manage_admin_dashboard');
        }
        if ($panel !== 'doctor') {
            Route::get('/dashboard-patients', [DashboardController::class, $panel === 'admin' ? 'getPatientList' : 'getStaffPatientList'])
                ->name('patientData.dashboard');
        }
    }

    public static function emailVerification(string $panel): void
    {
        if ($panel === 'admin') {
            Route::post('email-verified', [UserController::class, 'emailVerified'])->name('emailVerified');
        }
        Route::post('/email/verification-notification/{userId}', [UserController::class, 'resendEmailVerification'])->name('resend.email.verification');
        if ($panel !== 'admin') {
            Route::post('/email-verified', [UserController::class, 'emailVerified'])->name('emailVerified');
        }
    }

    public static function doctors(string $panel): void
    {
        Route::middleware('permission:manage_doctors')->group(function () use ($panel) {
            if ($panel === 'doctor') {
                Route::get('doctors/{doctor}', [UserController::class, 'show'])->name('doctors.detail');

                return;
            }
            Route::resource('doctors', UserController::class);
            Route::post('/add-qualification', [UserController::class, 'addQualification'])->name('add.qualification');
            Route::put('doctor-status', [UserController::class, 'changeDoctorStatus'])->name('doctor.status');
            Route::post('doctors/{user}/reset-password', [UserController::class, 'resetPassword'])->name('doctors.reset.password');
        });
    }

    public static function patients(string $panel): void
    {
        Route::middleware('permission:manage_patients')->group(function () use ($panel) {
            // Doctors edit existing records; registration, archival and restoration remain staff/admin only.
            if ($panel !== 'doctor') {
                Route::post('patients/{patient}/restore', [PatientController::class, 'restore'])->name('patients.restore');
            }
            $resource = Route::resource('patients', PatientController::class);
            if ($panel === 'doctor') {
                $resource->only(['index', 'show', 'edit', 'update']);
            }
            // Register the resource before the following routes, matching Laravel's pending-resource ordering.
            unset($resource);
            Route::get('patients/{patient}/history', [PatientController::class, 'showMyHistory'])->name('patients.showMyHistory');
            if ($panel === 'admin') {
                Route::post('patients/{user}/reset-password', [PatientController::class, 'resetPassword'])->name('patients.reset.password');
            } else {
                self::emailVerification($panel);
            }
        });
    }

    public static function queue(string $panel): void
    {
        Route::middleware('permission:manage_patients')->group(function () use ($panel) {
            if ($panel === 'admin') {
                Route::get('queue-display', [\App\Http\Controllers\QueueBoardController::class, 'settings'])->name('queue-board.settings');
                Route::post('queue-display', [\App\Http\Controllers\QueueBoardController::class, 'rotate'])->name('queue-board.rotate');
            }
            if ($panel !== 'staff') {
                Route::get('patient-queue', [PatientQueueController::class, $panel === 'doctor' ? 'doctorQueue' : 'index'])->name('patient-queue.index');
            }
            Route::get('patient-queue/refresh', [PatientQueueController::class, $panel === 'doctor' ? 'doctorQueuePartial' : 'indexPartial'])->name('patient-queue.refresh');
            if ($panel === 'staff') {
                Route::resource('patient-queue', PatientQueueController::class)->except(['show']);
            } elseif ($panel === 'admin') {
                // The historical admin update accepts PUT only and uses camelCase binding parameters.
                Route::get('patient-queue/create', [PatientQueueController::class, 'create'])->name('patient-queue.create');
                Route::post('patient-queue', [PatientQueueController::class, 'store'])->name('patient-queue.store');
                Route::get('patient-queue/{patientQueue}/edit', [PatientQueueController::class, 'edit'])->name('patient-queue.edit');
                Route::put('patient-queue/{patientQueue}', [PatientQueueController::class, 'update'])->name('patient-queue.update');
                Route::delete('patient-queue/{patientQueue}', [PatientQueueController::class, 'destroy'])->name('patient-queue.destroy');
                self::completeQueue();
            }
            Route::post('patient-queue/{patientQueue}/call-next', [PatientQueueController::class, 'callNext'])->name('patient-queue.call-next');
            if ($panel !== 'admin') {
                self::completeQueue();
            }
            if ($panel === 'doctor') {
                Route::get('patient-queue/{patientQueue}/consultation', [PatientQueueController::class, 'viewConsultation'])
                    ->name('patient-queue.view-consultation')->middleware('permission:manage_request_documents');
            }
        });
    }

    private static function completeQueue(): void
    {
        Route::post('patient-queue/{patientQueue}/complete', [PatientQueueController::class, 'complete'])->name('patient-queue.complete');
    }

    public static function specializations(): void
    {
        Route::middleware('permission:manage_specialties')->group(function () {
            Route::resource('specializations', SpecializationController::class)->except(['create', 'show']);
        });
    }

    public static function documentLookups(string $panel): void
    {
        // The admin lookups historically have no module middleware; keep that contract in this refactor.
        Route::middleware($panel === 'admin' ? [] : ['permission:manage_request_documents'])->group(function () {
            Route::get('document-issuances/search-users', [DocumentIssuanceController::class, 'searchUsers'])->name('document-issuances.search-users');
            Route::get('document-issuances/get-last-consultation', [DocumentIssuanceController::class, 'getLastConsultation'])->name('document-issuances.get-last-consultation');
            Route::get('document-issuances/get-last-medical-certificate', [DocumentIssuanceController::class, 'getLastMedicalCertificate'])->name('document-issuances.get-last-medical-certificate');
            Route::get('lab-requests/search-users', [LabRequestController::class, 'searchUsers'])->name('lab-requests.search-users');
        });
    }

    public static function documents(): void
    {
        Route::middleware('permission:manage_request_documents')->group(function () {
            Route::get('document-issuances/{document_issuance}/export-pdf', [DocumentIssuanceController::class, 'exportPdf'])->name('document-issuances.export-pdf');
            Route::get('document-issuances/{document_issuance}/images/{index}', [DocumentIssuanceController::class, 'showImage'])
                ->whereNumber('index')->name('document-issuances.image');
            Route::resource('document-issuances', DocumentIssuanceController::class);
        });
    }

    public static function labRequests(): void
    {
        Route::middleware('permission:manage_request_documents')->group(function () {
            Route::post('lab-requests/{lab_request}/status', [LabRequestController::class, 'updateStatus'])->name('lab-requests.update-status');
            Route::get('lab-requests/{id}/pdf', [LabRequestController::class, 'exportPdf'])->name('lab-requests.pdf');
            Route::resource('lab-requests', LabRequestController::class);
        });
    }

    public static function prescriptions(string $panel): void
    {
        Route::middleware('permission:manage_request_documents')->group(function () use ($panel) {
            Route::resource('prescriptions', PrescriptionController::class)->except(['create', 'edit']);
            Route::get('patients/{patientId}/prescription-create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
            Route::get('prescriptions/{prescription}/edit', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
            Route::post('prescription-medicine', [PrescriptionController::class, 'prescreptionMedicineStore'])->name('prescription.medicine.store');
            Route::post('prescriptions/{prescription}/active-deactive', [PrescriptionController::class, 'activeDeactiveStatus'])->name('prescription.status');
            if ($panel !== 'doctor') {
                self::dispensePrescription($panel);
            }
            Route::get('prescription-medicine-show/{id}', [PrescriptionController::class, 'prescriptionMedicineShowFunction'])->name('prescription.medicine.show');
            Route::get('prescription-pdf/{id}', [PrescriptionController::class, 'convertToPDF'])->name('prescriptions.pdf');
            if ($panel === 'doctor') {
                self::dispensePrescription($panel);
            }
        });
    }

    private static function dispensePrescription(string $panel): void
    {
        $route = Route::post('prescriptions/{prescription}/dispense', [PrescriptionController::class, 'dispense'])->name('prescriptions.dispense');
        if ($panel !== 'admin') {
            $route->middleware('permission:manage_medicines');
        }
    }

    public static function medicineLookup(): void
    {
        Route::get('medicines-by-category', [MedicineController::class, 'getMedicinesByCategory'])->name('medicines.by.category')
            ->middleware('permission:manage_request_documents|manage_medicines');
    }

    public static function inventory(string $panel): void
    {
        Route::middleware('permission:manage_medicines')->group(function () use ($panel) {
            $readOnly = $panel === 'doctor' ? ['only' => ['index', 'show']] : [];
            Route::resource('categories', CategoryController::class, $readOnly)->parameters(['categories' => 'category'])->except(['create']);
            if ($panel !== 'doctor') {
                Route::post('categories/{category_id}/active-deactive', [CategoryController::class, 'activeDeActiveCategory'])->name('active.deactive');
            }
            Route::resource('generics', GenericController::class, $readOnly);
            Route::resource('medicines', MedicineController::class, $panel === 'doctor' ? ['only' => ['index']] : [])
                ->parameters(['medicines' => 'medicine'])->except(['show']);
            Route::get('medicine-inventory-tracking', [MedicineController::class, 'index'])->name('medicine-inventory.index');
            if ($panel !== 'doctor') {
                Route::get('stock-control', [\App\Http\Controllers\StockControlController::class, 'index'])->name('stock-control.index');
                Route::post('stock-control/preview', [\App\Http\Controllers\StockControlController::class, 'preview'])->name('stock-control.preview');
                Route::post('stock-control', [\App\Http\Controllers\StockControlController::class, 'store'])->name('stock-control.store');
            }
            Route::get('medicines-show-modal/{medicine}', [MedicineController::class, 'showModal'])->name('medicines.show.modal');
            Route::get('medicines-uses-check/{medicine}', [MedicineController::class, 'checkUseOfMedicine'])->name('check.use.medicine');
            if ($panel === 'admin') {
                Route::get('medicines-by-category', [MedicineController::class, 'getMedicinesByCategory'])->name('medicines.by.category');
            }
            if ($panel !== 'staff') {
                self::dispensingIndex();
            }
            Route::resource('stock-in', StockInController::class, $readOnly);
            Route::get('export-stock-in', [StockInController::class, 'purchaseMedicineExport'])->name('stock-in.excel');
            Route::get('get-medicine/{medicine}', [StockInController::class, 'getMedicine'])->name('get-medicine');
        });
    }

    private static function dispensingIndex(): void
    {
        Route::get('medicine-dispensing-management', [MedicineDispensingManagementController::class, 'index'])->name('medicine-dispensing.index');
    }

    /**
     * Meds Only (2026-10-29): patients who only come for medicine. Same permission as Dispensing, which it feeds; a doctor
     * reads the list and a record, and cannot record, change or delete one.
     */
    public static function medsOnly(string $panel): void
    {
        Route::middleware('permission:manage_medicines')->group(function () use ($panel) {
            if ($panel === 'doctor') {
                Route::resource('meds-only', MedsOnlyController::class)->parameters(['meds-only' => 'record'])->only(['index', 'show']);

                return;
            }
            // Before the resource: "patients" would otherwise be read as a record id.
            Route::get('meds-only/patients', [MedsOnlyController::class, 'searchPatients'])->name('meds-only.patients');
            Route::resource('meds-only', MedsOnlyController::class)->parameters(['meds-only' => 'record']);
        });
    }

    public static function dispensing(string $panel): void
    {
        Route::middleware('permission:manage_medicines')->group(function () use ($panel) {
            if ($panel === 'staff') {
                self::dispensingIndex();
            }
            $namePrefix = ['admin' => '', 'staff' => 'staff.', 'doctor' => 'doctors.'][$panel];
            Route::get('used-medicine', function () use ($namePrefix) {
                return redirect()->route($namePrefix.'medicine-dispensing.index', ['tab' => 'stock-out']);
            })->name('used-medicine.index');
            Route::resource('dispense-records', DispenseRecordController::class)->parameters(['dispense-records' => 'medicine_history']);
            Route::post('dispense-records/store-patient', [DispenseRecordController::class, 'storePatient'])->name('dispense-records.store-patient');
            Route::get('dispense-records-pdf/{id}', [DispenseRecordController::class, 'convertToPDF'])->name('dispense-records.pdf');
            Route::get('dispense-records/by-category/{category}', [DispenseRecordController::class, 'getMedicineCategory'])->name('dispense-records.by-category');
            Route::get('dispense-records/consultation/{document_issuance}', [DispenseRecordController::class, 'showConsultation'])
                ->whereNumber('document_issuance')->name('dispense-records.consultation');

            $pathPrefix = $panel === 'doctor' ? 'doctors' : $panel;
            $index = Route::redirect('medicine-history', '/'.$pathPrefix.'/dispense-records');
            $show = Route::redirect('medicine-history/{id}', '/'.$pathPrefix.'/dispense-records/{id}');
            if ($panel !== 'admin') {
                $index->name('medicine-history.index');
                $show->name('medicine-history.show');
            }
        });
    }

    public static function activityLogs(string $panel): void
    {
        Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
        Route::prefix('activity-logs')->name('activity-logs.')->group(function () use ($panel) {
            Route::get('/', [ActivityLogController::class, 'index'])->name('index');
            Route::get($panel === 'admin' ? '/export' : '/export/csv', [ActivityLogController::class, 'export'])->name('export');
            Route::get('/accomplishment/{format}', [ActivityLogController::class, 'accomplishment'])
                ->whereIn('format', ['pdf', 'xlsx', 'csv'])->name('accomplishment');
            Route::get('/inventory-yearly/{format}', [ActivityLogController::class, 'inventoryYearly'])
                ->whereIn('format', ['pdf', 'xlsx', 'csv'])->name('inventory-yearly');
            Route::get($panel === 'admin' ? '/{id}' : '/{activityLog}', [ActivityLogController::class, 'show'])->name('show');
        });
    }
}
