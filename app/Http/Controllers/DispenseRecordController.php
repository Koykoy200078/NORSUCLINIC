<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateDispenseRecordRequest;
use App\Http\Requests\CreatePatientRequest;
use App\Http\Requests\UpdateDispenseRecordRequest;
use App\Models\Category;
use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\Medicine;
use App\Repositories\DispenseRecordRepository;
use App\Repositories\MedicineRepository;
use App\Repositories\PatientRepository;
use App\Repositories\PrescriptionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
    ) {
        $this->prescriptionRepository = $prescriptionRepo;
        $this->medicineRepository     = $medicineRepository;
        $this->patientRepository      = $patientRepo;
        $this->dispenseRecordRepository = $dispenseRecordRepository;
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

        $arr          = collect($input['medicine']);
        $duplicateIds = $arr->duplicates();

        foreach ($input['medicine'] as $key => $value) {
            $medicine = Medicine::find($input['medicine'][$key]);
            if (! empty($duplicateIds)) {
                foreach ($duplicateIds as $k => $v) {
                    if ($request->ajax()) {
                        return $this->sendError(__('messages.medicine_bills.duplicate_medicine'));
                    }
                    Flash::error(__('messages.medicine_bills.duplicate_medicine'));
                    return redirect($this->getCreateRoute());
                }
            }
            $qty = $input['quantity'][$key];
            if ($medicine->available_quantity < $qty) {
                $available = $medicine->available_quantity ?? 0;
                $msg = __('messages.medicine_bills.available_quantity') . ' ' .
                    $medicine->name . ' ' .
                    __('messages.medicine_bills.is') . ' ' . $available . '.';
                if ($request->ajax()) {
                    return $this->sendError($msg);
                }
                Flash::error($msg);
                return redirect($this->getCreateRoute());
            }
        }

        $dispenseRecord = DispenseRecord::create([
            'history_number' => 'HIS' . generateUniqueHistoryNumber(),
            'patient_id'     => $input['patient_id'],
            'note'           => $input['note'] ?? null,
            'model_type'     => DispenseRecord::class,
            'bill_date'      => $input['bill_date'],
            // legacy nullable fields still in DB schema
            'net_amount'     => 0,
            'discount'       => 0,
            'payment_status' => 1,
            'payment_type'   => 0,
            'total'          => 0,
            'tax_amount'     => 0,
        ]);
        $dispenseRecord->update(['model_id' => $dispenseRecord->id]);

        if (! empty($input['category_id'])) {
            foreach ($input['category_id'] as $key => $value) {
                $medicine = Medicine::find($input['medicine'][$key]);
                $unitPrice = (float) ($input['sale_price'][$key] ?? 0);
                $quantity = (int) ($input['quantity'][$key] ?? 0);
                $chargeAmount = (float) ($input['tax_medicine'][$key] ?? 0);
                DispenseRecordItem::create([
                    'dispense_id'   => $dispenseRecord->id,
                    'medicine_id'   => $medicine->id,
                    'unit_price'    => $unitPrice,
                    'expires_at'    => $input['expiry_date'][$key] ?? null,
                    'quantity'      => $quantity,
                    'charge_amount' => $chargeAmount,
                    'line_total'    => ($unitPrice * $quantity) + $chargeAmount,
                ]);
                $medicine->update([
                    'available_quantity' => max(0, ($medicine->available_quantity ?? 0) - $input['quantity'][$key]),
                ]);
            }
            if ($request->ajax()) {
                return $this->sendSuccess(__('messages.medicine_bills.saved_created'));
            }
            Flash::success(__('messages.medicine_bills.saved_created'));
            return redirect($this->getIndexRoute());
        }

        if ($request->ajax()) {
            return $this->sendError(__('messages.medicine_bills.something_went_wrong'));
        }
        Flash::error(__('messages.medicine_bills.something_went_wrong'));
        return redirect($this->getCreateRoute());
    }

    public function show(DispenseRecord $medicine_history): View
    {
        $dispenseRecord = $medicine_history;
        $dispenseRecord->load(['dispenseItems.medicine']);

        // Keep view variable name for legacy template compatibility.
        $medicineBill = $dispenseRecord;

        return view('medicine-history.show', compact('medicineBill'));
    }

    public function edit(DispenseRecord $medicine_history): View
    {
        $dispenseRecord = $medicine_history;
        $dispenseRecord->load(['dispenseItems.medicine.category', 'dispenseItems.medicine.purchasedMedicine', 'patient', 'doctor']);

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
        $medicine_history->dispenseItems()->delete();
        $medicine_history->delete();

        return $this->sendSuccess(
            __('messages.medicine_bills.medicine_bill') . ' ' . __('messages.common.deleted_successfully')
        );
    }

    public function storePatient(CreatePatientRequest $request): JsonResponse
    {
        $input           = $request->all();
        $input['status'] = isset($input['status']) ? 1 : 0;

        $this->patientRepository->store($input);
        $this->prescriptionRepository->createNotification($input);
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
            'total',
            'tax_amount',
            'discount',
            'net_amount'
        )->with([
            'dispenseItems' => fn($q) => $q->select('id', 'medicine_bill_id', 'medicine_id', 'sale_price', 'expiry_date', 'sale_quantity', 'tax'),
            'dispenseItems.medicine' => fn($q) => $q->select('id', 'name'),
            'patient.user:id,first_name,last_name,email,contact,gender,dob',
            'doctor.user:id,first_name,last_name',
        ])->findOrFail($id);

        // View still uses $medicineBill variable name for backward compat with PDF template
        return view('medicine-history.medicine_bill_pdf', compact('medicineBill', 'data'));
    }

    public function getMedicineCategory(Category $category): JsonResponse
    {
        $data             = [];
        $data['category'] = $category;
        $data['medicine'] = Medicine::whereCategoryId($category->id)->pluck('name', 'id')->toArray();

        return $this->sendResponse($data, 'retrieved');
    }
}
