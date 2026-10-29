<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateDispenseRecordRequest;
use App\Http\Requests\CreatePatientRequest;
use App\Http\Requests\UpdateDispenseRecordRequest;
use App\Models\Category;
use App\Models\DispenseRecord;
use App\Models\DocumentIssuance;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Repositories\DispenseRecordRepository;
use App\Repositories\MedicineRepository;
use App\Repositories\PatientRepository;
use App\Repositories\PrescriptionRepository;
use App\Services\MedicineInventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laracasts\Flash\Flash;

/**
 * Renamed from MedicineBillController → DispenseRecordController.
 * Handles create/edit/delete/PDF for dispense records (medicine-history).
 * Route binding still uses `medicine_history` model key for backward compat.
 */
class DispenseRecordController extends AppBaseController
{
    private $prescriptionRepository;
    private $medicineRepository;
    private $patientRepository;
    private $dispenseRecordRepository;
    private MedicineInventoryService $medicineInventoryService;



    public function __construct(
        PrescriptionRepository $prescriptionRepo,
        MedicineRepository $medicineRepository,
        PatientRepository $patientRepo,
        DispenseRecordRepository $dispenseRecordRepository,
        MedicineInventoryService $medicineInventoryService,
    ) {
        $this->prescriptionRepository = $prescriptionRepo;
        $this->medicineRepository     = $medicineRepository;
        $this->patientRepository      = $patientRepo;
        $this->dispenseRecordRepository = $dispenseRecordRepository;
        $this->medicineInventoryService = $medicineInventoryService;
    }

    public function index(): View
    {
        return view('medicine-dispensing.index');
    }

    public function create(): View
    {
        $patients              = $this->prescriptionRepository->getPatients();
        $doctors               = $this->prescriptionRepository->getDoctors();
        $medicines             = $this->prescriptionRepository->getMedicines();
        $data                  = $this->medicineRepository->getSyncList();
        $medicineList          = $this->medicineRepository->getMedicineList();
        $mealList              = $this->medicineRepository->getMealList();
        $medicineCategories    = $this->dispenseRecordRepository->getMedicinesCategoriesData();
        $medicineCategoriesList = $this->dispenseRecordRepository->getMedicineCategoriesList();

        return view(
            'medicine-history.create',
            compact('patients', 'doctors', 'medicines', 'medicineList', 'mealList', 'medicineCategoriesList', 'medicineCategories')
        )->with($data);
    }

    public function store(CreateDispenseRecordRequest $request): JsonResponse|RedirectResponse
    {
        $input = $request->all();

        if (empty($input['medicine'])) {
            if ($request->ajax()) {
                return $this->sendError(__('messages.medicine_bills.medicine_not_selected'));
            }
            Flash::error(__('messages.medicine_bills.medicine_not_selected'));
            return redirectByRole('dispense-records.create');
        }

        $lineIdentifiers = collect($input['medicine'])->map(function ($medicineId, $key) use ($input) {
            $dosage = trim((string) ($input['dosage'][$key] ?? ''));
            $expiry = trim((string) ($input['expiry_date'][$key] ?? ''));

            return $medicineId . '|' . $dosage . '|' . $expiry;
        });

        if ($lineIdentifiers->duplicates()->isNotEmpty()) {
            if ($request->ajax()) {
                return $this->sendError(__('messages.medicine_bills.duplicate_medicine'));
            }
            Flash::error(__('messages.medicine_bills.duplicate_medicine'));

            return redirectByRole('dispense-records.create');
        }

        DB::beginTransaction();
        try {
            $dispenseRecordData = [
                'history_number' => 'HIS' . generateUniqueHistoryNumber(),
                'patient_id'     => $input['patient_id'],
                'note'           => $input['note'] ?? null,
                'model_type'     => DispenseRecord::class,
                'bill_date'      => $input['bill_date'],
            ];

            $dispenseRecord = DispenseRecord::create($dispenseRecordData);
            $dispenseRecord->update(['model_id' => $dispenseRecord->id]);

            if (empty($input['category_id'])) {
                throw new \RuntimeException(__('messages.medicine_bills.something_went_wrong'));
            }

            foreach ($input['category_id'] as $key => $value) {
                $medicine = Medicine::find($input['medicine'][$key]);
                if (! $medicine) {
                    throw new \RuntimeException(__('messages.medicine_bills.medicine_not_selected'));
                }

                $quantity = (int) ($input['quantity'][$key] ?? 0);
                $dosage = trim((string) ($input['dosage'][$key] ?? ''));

                $allocations = $this->medicineInventoryService->deductStockFefo(
                    $medicine->id,
                    $quantity,
                    getLogInUserId(),
                    $dispenseRecord,
                    'Dispense record #' . $dispenseRecord->history_number . ' deduction',
                    \App\Models\MedicineTransaction::TYPE_DISPENSE,
                    $dosage
                );

                \App\Services\DispenseRecordLines::fromAllocations(
                    $dispenseRecord->id,
                    $medicine->id,
                    $dosage,
                    $input['expiry_date'][$key] ?? null,
                    $quantity,
                    $allocations
                );
            }

            DB::commit();

            if ($request->ajax()) {
                return $this->sendSuccess(__('messages.medicine_bills.saved_created'));
            }

            Flash::success(__('messages.medicine_bills.saved_created'));

            return redirectByRole('medicine-dispensing.index', ['tab' => 'dispense-history']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Dispense record store failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax()) {
                return $this->sendError($this->userFacingErrorMessage($e));
            }

            Flash::error($this->userFacingErrorMessage($e));

            return redirectByRole('dispense-records.create');
        }
    }

    public function show(DispenseRecord $medicine_history): View|RedirectResponse
    {
        if ($medicine_history->meds_only) {
            return redirectByRole('meds-only.show', [$medicine_history->id]);
        }

        $dispenseRecord = $medicine_history;
        $dispenseRecord->load([
            'dispenseItems.medicine',
            'patient.user:id,first_name,last_name,email,contact,gender,dob',
            'doctor.user:id,first_name,last_name,email,gender',
        ]);

        // Keep view variable name for legacy template compatibility.
        $medicineBill = $dispenseRecord;

        return view('medicine-history.show', compact('medicineBill'));
    }

    /**
     * Read-only page for the medicines recorded inside a consultation (a "Consultation" row of the Dispense
     * History). Only what was handed out is shown here; the clinical notes stay in the consultation itself, which
     * is linked only for users who may open consultations.
     */
    public function showConsultation(DocumentIssuance $document_issuance): View
    {
        abort_unless($document_issuance->document_type === 'consultation_form', 404);

        $document_issuance->load([
            'consultationMedicines.medicine:id,name,dosage',
            'creator:id,first_name,middle_name,last_name,email',
        ]);

        $patient = Patient::with('user:id,first_name,middle_name,last_name,email,contact,gender,dob')
            ->where('user_id', $document_issuance->user_id)
            ->first();

        $user = auth()->user();
        $canOpenConsultation = canUseModule('consultations', $user);

        return view('medicine-history.consultation', [
            'consultation' => $document_issuance,
            'patientUser' => $patient?->user,
            'canOpenConsultation' => $canOpenConsultation,
        ]);
    }

    /**
     * A dispense record created alongside a prescription is owned by that prescription: its
     * stock only moves when the prescription is dispensed, and it must be changed from the
     * prescription itself. Editing or deleting it here would "restore" stock that was never
     * deducted (pending) or unbalance the ledger (dispensed). M-01.
     */
    private function isPrescriptionOwned(DispenseRecord $dispenseRecord): bool
    {
        return $dispenseRecord->model_type === \App\Models\Prescription::class;
    }

    private function prescriptionOwnedMessage(): string
    {
        return 'This dispense record belongs to a prescription and cannot be changed here. '
            . 'Manage it from the prescription instead.';
    }

    /**
     * A Meds Only record (no registered patient, a college / course / year of its own, its own validation and audit trail)
     * is recorded, changed and deleted in the Meds Only module. This form would give it a patient and skip its audit line.
     */
    private function medsOnlyMessage(): string
    {
        return 'This is a Meds Only record and cannot be changed here. Open it in Meds Only instead.';
    }

    public function edit(DispenseRecord $medicine_history): View|RedirectResponse
    {
        if ($this->isPrescriptionOwned($medicine_history)) {
            Flash::error($this->prescriptionOwnedMessage());

            return redirectByRole('medicine-dispensing.index', ['tab' => 'dispense-history']);
        }

        if ($medicine_history->meds_only) {
            Flash::error($this->medsOnlyMessage());

            return redirectByRole('meds-only.show', [$medicine_history->id]);
        }

        $dispenseRecord = $medicine_history;
        $dispenseRecord->load([
            'dispenseItems.medicine.category',
            'dispenseItems.medicine.purchasedMedicine',
            'patient.user:id,first_name,last_name,email,contact,gender,dob',
            'doctor.user:id,first_name,last_name,email,gender',
        ]);

        $patients               = $this->prescriptionRepository->getPatients();
        $doctors                = $this->prescriptionRepository->getDoctors();
        $medicines              = $this->prescriptionRepository->getMedicines();
        $data                   = $this->medicineRepository->getSyncList();
        $medicineList           = $this->medicineRepository->getMedicineList();
        $mealList               = $this->medicineRepository->getMealList();
        $medicineCategories     = $this->dispenseRecordRepository->getMedicinesCategoriesData();
        $medicineCategoriesList = $this->dispenseRecordRepository->getMedicineCategoriesList();

        // Keep view variable name for legacy template compatibility.
        $medicineBill = $dispenseRecord;

        return view(
            'medicine-history.edit',
            compact('patients', 'doctors', 'medicines', 'medicineList', 'mealList', 'medicineBill', 'medicineCategoriesList', 'medicineCategories')
        )->with($data);
    }

    public function update(DispenseRecord $medicine_history, UpdateDispenseRecordRequest $request)
    {
        if ($this->isPrescriptionOwned($medicine_history)) {
            return $this->sendError($this->prescriptionOwnedMessage());
        }

        if ($medicine_history->meds_only) {
            return $this->sendError($this->medsOnlyMessage());
        }

        $dispenseRecord = $medicine_history;
        $input        = $request->all();
        if (empty($input['medicine'])) {
            return $this->sendError(__('messages.medicine_bills.medicine_not_selected'));
        }
        $this->dispenseRecordRepository->update($dispenseRecord, $input);

        return $this->sendSuccess(__('messages.medicine_bills.saved_updated'));
    }

    public function destroy(DispenseRecord $medicine_history)
    {
        if ($this->isPrescriptionOwned($medicine_history)) {
            return $this->sendError($this->prescriptionOwnedMessage());
        }

        if ($medicine_history->meds_only) {
            return $this->sendError($this->medsOnlyMessage());
        }

        DB::transaction(function () use ($medicine_history) {
            // Restore stock for each dispensed item before deleting so removing a dispense
            // record returns the stock to inventory instead of losing it permanently. INV-2.
            foreach ($medicine_history->dispenseItems()->get() as $item) {
                $this->medicineInventoryService->restoreStock(
                    (int) $item->medicine_id,
                    (int) $item->quantity,
                    getLogInUserId(),
                    $medicine_history,
                    'Reversed for deleted dispense record #' . $medicine_history->id,
                    $item->dosage,
                    $item->expires_at
                );
            }

            $medicine_history->dispenseItems()->delete();
            $medicine_history->delete();
        });

        return $this->sendSuccess(
            __('messages.medicine_bills.medicine_bill') . ' ' . __('messages.common.deleted_successfully')
        );
    }

    public function storePatient(CreatePatientRequest $request): JsonResponse
    {
        // A pharmacist with the medicines permission only may not register people; doctors view and edit patients,
        // they do not register them (owner decision). Row 5.7.
        abort_unless(canUseModule('patients') && ! isRole('doctor'), 403);

        $input           = $request->all();
        $input['status'] = isset($input['status']) ? 1 : 0;

        $this->patientRepository->store($input);
        $patients = $this->prescriptionRepository->getPatients();

        return $this->sendResponse($patients, __('messages.flash.Patient_saved'));
    }

    public function convertToPDF($id): View
    {
        $data = cache()->remember('prescription_settings', 300, function () {
            return $this->prescriptionRepository->getSettingList();
        });

        $medicineBill = DispenseRecord::select(
            'id',
            'history_number',
            'bill_date',
            'patient_id',
            'doctor_id',
            'meds_only'
        )->with([
            'dispenseItems' => fn($q) => $q->select('id', 'medicine_bill_id', 'medicine_id', 'dosage', 'expiry_date', 'sale_quantity'),
            'dispenseItems.medicine' => fn($q) => $q->select('id', 'name'),
            'patient.user:id,first_name,last_name,email,contact,gender,dob',
            'doctor.user:id,first_name,last_name',
        ])->findOrFail($id);

        // No per-record print for Meds Only (it may have no registered patient for the template to name).
        abort_if($medicineBill->meds_only, 404);

        // View still uses $medicineBill variable name for backward compat with PDF template
        return view('medicine-history.medicine_bill_pdf', compact('medicineBill', 'data'));
    }

    public function getMedicineCategory(Category $category): JsonResponse
    {
        $data             = [];
        $data['category'] = $category;

        $dispensableMedicineIds = MedicineBatch::query()
            ->where('quantity', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expiration_date')
                    ->orWhereDate('expiration_date', '>=', Carbon::today()->toDateString());
            })
            ->distinct()
            ->pluck('medicine_id')
            ->all();

        $fallbackWithoutBatches = function ($query) {
            $query->where('available_quantity', '>', 0)
                ->whereDoesntHave('batches', function ($batchQuery) {
                    $batchQuery->where('quantity', '>', 0);
                });
        };

        $medicinesQuery = Medicine::whereCategoryId($category->id)->orderBy('name');

        if (! empty($dispensableMedicineIds)) {
            $medicinesQuery->where(function ($query) use ($dispensableMedicineIds, $fallbackWithoutBatches) {
                $query->whereIn('id', $dispensableMedicineIds)
                    ->orWhere($fallbackWithoutBatches);
            });
        } else {
            $medicinesQuery->where($fallbackWithoutBatches);
        }

        $medicines = $medicinesQuery->get(['id', 'name', 'dosage', 'available_quantity']);

        // One query for the batches of all the listed medicines (it was two queries per medicine).
        $batchesByMedicine = MedicineBatch::whereIn('medicine_id', $medicines->pluck('id')->all())
            ->where('quantity', '>', 0)
            ->get(['medicine_id', 'dosage', 'quantity', 'expiration_date'])
            ->groupBy('medicine_id');

        $data['medicine'] = $medicines->pluck('name', 'id')->toArray();
        $data['medicine_details'] = $medicines->map(function (Medicine $medicine) use ($batchesByMedicine) {
            return [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'available_quantity' => (int) $medicine->available_quantity,
                'dosages' => $this->resolveDosageAvailability($medicine, $batchesByMedicine->get($medicine->id, collect())),
            ];
        })->values();

        return $this->sendResponse($data, 'retrieved');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, MedicineBatch>  $positiveBatches  the medicine's batches that still hold units
     */
    private function resolveDosageAvailability(Medicine $medicine, \Illuminate\Support\Collection $positiveBatches): array
    {
        $today = Carbon::today()->toDateString();

        $dosages = $positiveBatches
            ->filter(fn (MedicineBatch $batch) => $batch->expiration_date === null || $batch->expiration_date->toDateString() >= $today)
            ->groupBy(function (MedicineBatch $batch) use ($medicine) {
                $batchDosage = trim((string) ($batch->dosage ?? ''));
                if ($batchDosage !== '') {
                    return $batchDosage;
                }

                $medicineDosage = trim((string) ($medicine->dosage ?? ''));

                return $medicineDosage !== '' ? $medicineDosage : 'N/A';
            })
            ->map(function ($rows, $dosage) {
                $expiryDate = collect($rows)
                    ->pluck('expiration_date')
                    ->filter()
                    ->map(fn($date) => Carbon::parse($date)->toDateString())
                    ->sort()
                    ->first();

                return [
                    'dosage' => $dosage,
                    'available_quantity' => (int) collect($rows)->sum('quantity'),
                    'expiry_date' => $expiryDate,
                ];
            })
            ->values()
            ->all();

        $hasPositiveBatch = $positiveBatches->isNotEmpty();

        if (empty($dosages) && ! $hasPositiveBatch && (int) $medicine->available_quantity > 0) {
            $fallbackDosage = trim((string) ($medicine->dosage ?? ''));
            $dosages[] = [
                'dosage' => $fallbackDosage !== '' ? $fallbackDosage : 'N/A',
                'available_quantity' => (int) $medicine->available_quantity,
                'expiry_date' => null,
            ];
        }

        return $dosages;
    }
}
