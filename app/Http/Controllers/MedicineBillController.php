<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMedicineBillRequest;
use App\Http\Requests\CreatePatientRequest;
use App\Http\Requests\UpdateMedicineBillRequest;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\MedicineBill;
use App\Models\SaleMedicine;
use App\Repositories\DoctorRepository;
use App\Repositories\MedicineBillRepository;
use App\Repositories\MedicineRepository;
use App\Repositories\PatientRepository;
use App\Repositories\PrescriptionRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Laracasts\Flash\Flash;

class MedicineBillController extends AppBaseController
{
    /* @var  PrescriptionRepository
          @var DoctorRepository
         */
    private $prescriptionRepository;

    private $medicineRepository;

    private $patientRepository;

    private $medicineBillRepository;

    /**
     * Get the appropriate medicine-history index route based on user role
     */
    private function getMedicineHistoryIndexRoute(): string
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

    /**
     * Get the appropriate medicine-history create route based on user role
     */
    private function getMedicineHistoryCreateRoute(): string
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
        $this->medicineRepository = $medicineRepository;
        $this->patientRepository = $patientRepo;
        $this->medicineBillRepository = $medicineBillRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {

        return view('medicine-history.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {

        $patients = $this->prescriptionRepository->getPatients();
        $doctors = $this->prescriptionRepository->getDoctors();
        $medicines = $this->prescriptionRepository->getMedicines();
        $data = $this->medicineRepository->getSyncList();
        $medicineList = $this->medicineRepository->getMedicineList();
        $mealList = $this->medicineRepository->getMealList();
        $medicineCategories = $this->medicineBillRepository->getMedicinesCategoriesData();
        $medicineCategoriesList = $this->medicineBillRepository->getMedicineCategoriesList();

        return view(
            'medicine-history.create',
            compact('patients', 'doctors', 'medicines', 'medicineList', 'mealList', 'medicineCategoriesList', 'medicineCategories')
        )->with($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateMedicineBillRequest $request): RedirectResponse
    {
        $input = $request->all();
        if (empty($input['medicine'])) {

            flash::error(__('messages.medicine_bills.medicine_not_selected'));

            return redirect($this->getMedicineHistoryCreateRoute());
        }
        $arr = collect($input['medicine']);
        $duplicateIds = $arr->duplicates();

        $input['payment_status'] = isset($input['payment_status']) ? 1 : 0;

        foreach ($input['medicine'] as $key => $value) {
            $medicine = Medicine::find($input['medicine'][$key]);
            if (! empty($duplicateIds)) {
                foreach ($duplicateIds as $key => $value) {
                    $medicine = Medicine::find($duplicateIds[$key]);

                    Flash::error(__('messages.medicine_bills.duplicate_medicine'));

                    return redirect($this->getMedicineHistoryCreateRoute());
                }
            }
            $qty = $input['quantity'][$key];
            if ($medicine->available_quantity < $qty) {
                $available = $medicine->available_quantity == null ? 0 : $medicine->available_quantity;
                Flash::error(__('messages.medicine_bills.available_quantity') . ' ' . $medicine->name . ' ' . __('messages.medicine_bills.is') . ' ' . $available . '.');

                return redirect($this->getMedicineHistoryCreateRoute());
            }
        }

        $medicineBill = MedicineBill::create([
            'history_number' => 'HIS' . generateUniqueHistoryNumber(),
            'patient_id' => $input['patient_id'],
            'net_amount' => $input['net_amount'] ?? 0,
            'discount' => $input['discount'] ?? 0,
            'payment_status' => 1,
            'payment_type' => $input['payment_type'] ?? 0,
            'note' => $input['note'] ?? null,
            'total' => $input['total'] ?? 0,
            'tax_amount' => $input['tax'] ?? 0,
            'payment_note' => $input['payment_note'] ?? null,
            'model_type' => \App\Models\MedicineBill::class,
            'bill_date' => $input['bill_date'],
        ]);
        $medicineBill->update([
            'model_id' => $medicineBill->id,
        ]);
        if ($input['category_id']) {
            foreach ($input['category_id'] as $key => $value) {
                $medicine = Medicine::find($input['medicine'][$key]);
                $tax = $input['tax_medicine'][$key] == null ? $input['tax_medicine'][$key] : 0;
                SaleMedicine::create([
                    'dispense_id' => $medicineBill->id,
                    'medicine_id' => $medicine->id,
                    'unit_price' => $input['sale_price'][$key],
                    'expires_at' => $input['expiry_date'][$key],
                    'quantity' => $input['quantity'][$key],
                    'charge_amount' => $tax,
                    'line_total' => ((float) ($input['sale_price'][$key] ?? 0) * (int) ($input['quantity'][$key] ?? 0)) + (float) ($tax ?? 0),

                ]);
                $medicine->update([
                    'available_quantity' => max(0, ($medicine->available_quantity ?? 0) - $input['quantity'][$key]),
                ]);
            }
            Flash::success(__('messages.medicine_bills.saved_created'));

            return redirect($this->getMedicineHistoryIndexRoute());
        }

        // Ensure a return value for all paths
        Flash::error(__('messages.medicine_bills.something_went_wrong'));
        return redirect($this->getMedicineHistoryCreateRoute());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show(MedicineBill $medicine_history): View
    {
        $medicineBill = $medicine_history;
        $medicineBill->load(['saleMedicine.medicine']);

        return view('medicine-history.show', compact('medicineBill'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MedicineBill $medicine_history): View
    {
        $medicineBill = $medicine_history;
        $medicineBill->load(['saleMedicine.medicine.category', 'saleMedicine.medicine.purchasedMedicine', 'patient', 'doctor']);

        $patients = $this->prescriptionRepository->getPatients();
        $doctors = $this->prescriptionRepository->getDoctors();
        $medicines = $this->prescriptionRepository->getMedicines();
        $data = $this->medicineRepository->getSyncList();
        $medicineList = $this->medicineRepository->getMedicineList();
        $mealList = $this->medicineRepository->getMealList();
        $medicineCategories = $this->medicineBillRepository->getMedicinesCategoriesData();
        $medicineCategoriesList = $this->medicineBillRepository->getMedicineCategoriesList();

        return view(
            'medicine-history.edit',
            compact('patients', 'doctors', 'medicines', 'medicineList', 'mealList', 'medicineBill', 'medicineCategoriesList', 'medicineCategories')
        )->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(MedicineBill $medicine_history, UpdateMedicineBillRequest $request)
    {
        $medicineBill = $medicine_history;
        $input = $request->all();
        if (empty($input['medicine'])) {
            return $this->sendError(__('messages.medicine_bills.medicine_not_selected'));
        }
        $this->medicineBillRepository->update($medicineBill, $input);

        return $this->sendSuccess(__('messages.medicine_bills.saved_updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * *  @return \Illuminate\Http\Response
     */
    public function destroy(MedicineBill $medicine_history)
    {
        $medicine_history->saleMedicine()->delete();
        $medicine_history->delete();

        return $this->sendSuccess(__('messages.medicine_bills.medicine_bill') . ' ' . __('messages.common.deleted_successfully'));
    }

    /** Store a newly created Patient in storage.
     */
    public function storePatient(CreatePatientRequest $request): JsonResponse
    {
        $input = $request->all();
        $input['status'] = isset($input['status']) ? 1 : 0;

        $this->patientRepository->store($input);
        $this->patientRepository->createNotification($input);
        $patients = $this->prescriptionRepository->getPatients();

        return $this->sendResponse($patients, __('messages.flash.Patient_saved'));
    }

    public function convertToPDF($id): View // Response
    {
        // // Cache settings for 5 minutes to reduce DB hits
        // $data = cache()->remember('prescription_settings', 300, function () {
        //     return $this->prescriptionRepository->getSettingList();
        // });

        // // Only select necessary columns for the main model and relationships
        // $medicineBill = MedicineBill::select('id', 'history_number', 'bill_date', 'patient_id', 'doctor_id', 'total', 'tax_amount', 'discount', 'net_amount')
        //     ->with([
        //         'saleMedicine' => function ($q) {
        //             $q->select('id', 'medicine_bill_id', 'medicine_id', 'sale_price', 'expiry_date', 'sale_quantity', 'tax');
        //         },
        //         'saleMedicine.medicine' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'patient.user:id,first_name,last_name,email,contact,gender,dob',
        //         'doctor.user:id,first_name,last_name'
        //     ])
        //     ->findOrFail($id);

        // $pdf = Pdf::loadView('medicine-history.medicine_bill_pdf', compact('medicineBill', 'data'));

        // return $pdf->stream('medicine-bill.pdf');

        $data = cache()->remember('prescription_settings', 300, function () {
            return $this->prescriptionRepository->getSettingList();
        });

        $medicineBill = MedicineBill::select('id', 'history_number', 'bill_date', 'patient_id', 'doctor_id', 'total', 'tax_amount', 'discount', 'net_amount')
            ->with([
                'saleMedicine' => function ($q) {
                    $q->select('id', 'medicine_bill_id', 'medicine_id', 'sale_price', 'expiry_date', 'sale_quantity', 'tax');
                },
                'saleMedicine.medicine' => function ($q) {
                    $q->select('id', 'name');
                },
                'patient.user:id,first_name,last_name,email,contact,gender,dob',
                'doctor.user:id,first_name,last_name'
            ])
            ->findOrFail($id);

        // Use the correct view name here
        return view('medicine-history.medicine_bill_pdf', compact('medicineBill', 'data'));
    }

    public function getMedicineCategory(Category $category): JsonResponse
    {
        $data = [];
        $data['category'] = $category;
        $data['medicine'] = Medicine::whereCategoryId($category->id)->pluck('name', 'id')->toArray();

        return $this->sendResponse($data, 'retrieved');
    }
}
