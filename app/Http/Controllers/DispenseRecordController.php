<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateDispenseRecordRequest;
use App\Http\Requests\CreatePatientRequest;
use App\Http\Requests\UpdateDispenseRecordRequest;
use App\Models\Category;
use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
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

    private function getIndexRoute(): string
    {
        if (isRole('clinic_admin')) {
            return route('medicine-dispensing.index', ['tab' => 'dispense-history']);
        } elseif (isRole('staff')) {
            return route('staff.medicine-dispensing.index', ['tab' => 'dispense-history']);
        } elseif (isRole('doctor')) {
            return route('doctors.medicine-dispensing.index', ['tab' => 'dispense-history']);
        }

        return route('medicine-dispensing.index', ['tab' => 'dispense-history']);
    }

    private function getCreateRoute(): string
    {
        if (isRole('clinic_admin')) {
            return route('dispense-records.create');
        } elseif (isRole('staff')) {
            return route('staff.dispense-records.create');
        } elseif (isRole('doctor')) {
            return route('doctors.dispense-records.create');
        }

        return route('dispense-records.create');
    }

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
            return redirect($this->getCreateRoute());
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

            return redirect($this->getCreateRoute());
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

                $this->storeDispenseItemsFromAllocations(
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

            return redirect($this->getIndexRoute());
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

            return redirect($this->getCreateRoute());
        }
    }

    public function show(DispenseRecord $medicine_history): View
    {
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

    public function edit(DispenseRecord $medicine_history): View|RedirectResponse
    {
        if ($this->isPrescriptionOwned($medicine_history)) {
            Flash::error($this->prescriptionOwnedMessage());

            return redirect($this->getIndexRoute());
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
            'doctor_id'
        )->with([
            'dispenseItems' => fn($q) => $q->select('id', 'medicine_bill_id', 'medicine_id', 'dosage', 'expiry_date', 'sale_quantity'),
            'dispenseItems.medicine' => fn($q) => $q->select('id', 'name'),
            'patient.user:id,first_name,last_name,email,contact,gender,dob',
            'doctor.user:id,first_name,last_name',
        ])->findOrFail($id);

        // View still uses $medicineBill variable name for backward compat with PDF template
        return view('medicine-history.medicine_bill_pdf', compact('medicineBill', 'data'));
    }

    /**
     * Persist one or more dispense lines based on FEFO allocations.
     * Falls back to the requested quantity/expiry when no allocation payload is returned.
     */
    private function storeDispenseItemsFromAllocations(
        int $dispenseRecordId,
        int $medicineId,
        string $requestedDosage,
        ?string $requestedExpiry,
        int $requestedQuantity,
        array $allocations
    ): void {
        if (empty($allocations)) {
            DispenseRecordItem::create([
                'dispense_id' => $dispenseRecordId,
                'medicine_id' => $medicineId,
                'dosage' => $requestedDosage,
                'expires_at' => $requestedExpiry,
                'quantity' => $requestedQuantity,
            ]);

            return;
        }

        $groupedAllocations = collect($allocations)
            ->filter(function (array $allocation) {
                return (int) ($allocation['deducted'] ?? 0) > 0;
            })
            ->groupBy(function (array $allocation) use ($requestedDosage) {
                $allocationDosage = trim((string) ($allocation['dosage'] ?? $requestedDosage));
                $allocationExpiry = $allocation['expiration_date'] ?? '';

                return $allocationDosage . '|' . (string) $allocationExpiry;
            });

        foreach ($groupedAllocations as $rows) {
            $firstRow = $rows->first();
            $allocationDosage = trim((string) ($firstRow['dosage'] ?? $requestedDosage));
            $allocationExpiry = $firstRow['expiration_date'] ?? $requestedExpiry;
            $deductedQuantity = (int) collect($rows)->sum(function (array $allocation) {
                return (int) ($allocation['deducted'] ?? 0);
            });

            if ($deductedQuantity <= 0) {
                continue;
            }

            DispenseRecordItem::create([
                'dispense_id' => $dispenseRecordId,
                'medicine_id' => $medicineId,
                'dosage' => $allocationDosage !== '' ? $allocationDosage : $requestedDosage,
                'expires_at' => $allocationExpiry,
                'quantity' => $deductedQuantity,
            ]);
        }
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

        $data['medicine'] = $medicines->pluck('name', 'id')->toArray();
        $data['medicine_details'] = $medicines->map(function (Medicine $medicine) {
            return [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'available_quantity' => (int) $medicine->available_quantity,
                'dosages' => $this->resolveDosageAvailability($medicine),
            ];
        })->values();

        return $this->sendResponse($data, 'retrieved');
    }

    private function resolveDosageAvailability(Medicine $medicine): array
    {
        $dosages = MedicineBatch::where('medicine_id', $medicine->id)
            ->where('quantity', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expiration_date')
                    ->orWhereDate('expiration_date', '>=', Carbon::today()->toDateString());
            })
            ->get(['dosage', 'quantity', 'expiration_date'])
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

        $hasPositiveBatch = MedicineBatch::where('medicine_id', $medicine->id)
            ->where('quantity', '>', 0)
            ->exists();

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
