<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMedicineBillRequest;
use App\Http\Requests\CreatePatientRequest;
use App\Http\Requests\UpdateMedicineBillRequest;
use App\Models\Category;
use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\Medicine;
use App\Repositories\MedicineBillRepository;
use App\Repositories\MedicineRepository;
use App\Repositories\PatientRepository;
use App\Repositories\PrescriptionRepository;
use Barryvdh\DomPDF\Facade\Pdf;
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
    private $medicineBillRepository;

    private function getIndexRoute(): string
    {
        if (isRole('clinic_admin')) {
            return route('medicine-history.index');
        } elseif (isRole('staff')) {
            return route('staff.medicine-history.index');
        } elseif (isRole('doctor')) {
            return route('doctors.medicine-history.index');
        }

        return route('medicine-history.index');
    }

    private function getCreateRoute(): string
    {
        if (isRole('clinic_admin')) {
            return route('medicine-history.create');
        } elseif (isRole('staff')) {
            return route('staff.medicine-history.create');
        } elseif (isRole('doctor')) {
            return route('doctors.medicine-history.create');
        }

        return route('medicine-history.create');
    }

    public function __construct(
        PrescriptionRepository $prescriptionRepo,
        MedicineRepository $medicineRepository,
        PatientRepository $patientRepo,
        MedicineBillRepository $medicineBillRepository,
    ) {
        $this->prescriptionRepository = $prescriptionRepo;
        $this->medicineRepository     = $medicineRepository;
        $this->patientRepository      = $patientRepo;
        $this->medicineBillRepository = $medicineBillRepository;
    }

    public function index(): View
    {
        return view('medicine-history.index');
    }

    public function create(): View
    {
        $patients              = $this->prescriptionRepository->getPatients();
        $doctors               = $this->prescriptionRepository->getDoctors();
        $medicines             = $this->prescriptionRepository->getMedicines();
        $data                  = $this->medicineRepository->getSyncList();
        $medicineList          = $this->medicineRepository->getMedicineList();
        $mealList              = $this->medicineRepository->getMealList();
        $medicineCategories    = $this->medicineBillRepository->getMedicinesCategoriesData();
        $medicineCategoriesList = $this->medicineBillRepository->getMedicineCategoriesList();

        return view(
            'medicine-history.create',
            compact('patients', 'doctors', 'medicines', 'medicineList', 'mealList', 'medicineCategoriesList', 'medicineCategories')
        )->with($data);
    }

    public function store(CreateMedicineBillRequest $request): JsonResponse|RedirectResponse
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

        $record = DispenseRecord::create([
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
        $record->update(['model_id' => $record->id]);

        if (! empty($input['category_id'])) {
            foreach ($input['category_id'] as $key => $value) {
                $medicine = Medicine::find($input['medicine'][$key]);
                $tax      = $input['tax_medicine'][$key] ?? 0;
                DispenseRecordItem::create([
                    'medicine_bill_id' => $record->id,
                    'medicine_id'      => $medicine->id,
                    'sale_price'       => $input['sale_price'][$key],
                    'expiry_date'      => $input['expiry_date'][$key],
                    'sale_quantity'    => $input['quantity'][$key],
                    'tax'              => $tax,
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
        $medicineBill = $medicine_history;
        $medicineBill->load(['dispenseItems.medicine']);

        return view('medicine-history.show', compact('medicineBill'));
    }

    public function edit(DispenseRecord $medicine_history): View
    {
        $medicineBill = $medicine_history;
        $medicineBill->load(['dispenseItems.medicine.category', 'dispenseItems.medicine.purchasedMedicine', 'patient', 'doctor']);

        $patients               = $this->prescriptionRepository->getPatients();
        $doctors                = $this->prescriptionRepository->getDoctors();
        $medicines              = $this->prescriptionRepository->getMedicines();
        $data                   = $this->medicineRepository->getSyncList();
        $medicineList           = $this->medicineRepository->getMedicineList();
        $mealList               = $this->medicineRepository->getMealList();
        $medicineCategories     = $this->medicineBillRepository->getMedicinesCategoriesData();
        $medicineCategoriesList = $this->medicineBillRepository->getMedicineCategoriesList();

        return view(
            'medicine-history.edit',
            compact('patients', 'doctors', 'medicines', 'medicineList', 'mealList', 'medicineBill', 'medicineCategoriesList', 'medicineCategories')
        )->with($data);
    }

    public function update(DispenseRecord $medicine_history, UpdateMedicineBillRequest $request)
    {
        $medicineBill = $medicine_history;
        $input        = $request->all();
        if (empty($input['medicine'])) {
            return $this->sendError(__('messages.medicine_bills.medicine_not_selected'));
        }
        $this->medicineBillRepository->update($medicineBill, $input);

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

    public function storePatient(\App\Http\Requests\CreatePatientRequest $request): JsonResponse
    {
        $input           = $request->all();
        $input['status'] = isset($input['status']) ? 1 : 0;

        $this->patientRepository->store($input);
        $this->patientRepository->createNotification($input);
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
