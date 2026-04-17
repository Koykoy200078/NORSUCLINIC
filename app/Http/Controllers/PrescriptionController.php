<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMedicineRequest;
use App\Http\Requests\CreatePrescriptionRequest;
use App\Http\Requests\UpdatePrescriptionRequest;
use App\Models\Diagnose;
use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\DocumentIssuance;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Repositories\MedicineRepository;
use App\Repositories\PrescriptionRepository;
use App\Services\PrescriptionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Laracasts\Flash\Flash;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class PrescriptionController extends AppBaseController
{
    /** @var  PrescriptionRepository
     * @var DoctorRepository
     */
    private $prescriptionRepository;

    private $medicineRepository;

    private $prescriptionService;

    public function __construct(
        PrescriptionRepository $prescriptionRepo,
        MedicineRepository $medicineRepository,
        PrescriptionService $prescriptionService
    ) {
        $this->prescriptionRepository = $prescriptionRepo;
        $this->medicineRepository = $medicineRepository;
        $this->prescriptionService = $prescriptionService;
    }

    /**
     * Display a listing of prescriptions.
     */
    public function index(): View
    {
        return view('prescriptions.index');
    }

    /**
     * Show the form for creating a new Prescription.
     *
     * @return Factory|View
     */
    public function create($patientId = null): View
    {
        if (empty($patientId)) {
            abort(404, 'Patient is required for prescription creation.');
        }

        $patient = Patient::with('user:id,first_name,last_name,university_id_number')->findOrFail($patientId);
        $latestConsultation = $this->getLatestPatientConsultation($patient->user_id);
        $patientSummary = $this->buildPatientSummary($patient, $latestConsultation);

        $doctorOptions = $this->prescriptionRepository->getDoctors();
        $doctors = $doctorOptions instanceof \Illuminate\Support\Collection ? $doctorOptions->toArray() : (array) $doctorOptions;

        $doctorMeta = Doctor::query()
            ->select('id', 'prc_license_number', 's2_license_number')
            ->whereIn('id', array_keys($doctors))
            ->get()
            ->mapWithKeys(function (Doctor $doctor) {
                return [
                    $doctor->id => [
                        'license' => $doctor->s2_license_number ?: $doctor->prc_license_number,
                    ],
                ];
            });

        $medicineOptions = Medicine::query()
            ->select('id', 'name', 'available_quantity')
            ->where('available_quantity', '>', 0)
            ->orderBy('name')
            ->get();

        $diagnosisOptions = Diagnose::query()->orderBy('diagnoses')->pluck('diagnoses', 'id')->toArray();

        return view('prescriptions.create', compact(
            'patient',
            'patientSummary',
            'doctors',
            'doctorMeta',
            'medicineOptions',
            'diagnosisOptions'
        ));
    }

    /**
     * Store a newly created Prescription in storage.
     *
     * @return RedirectResponse|Redirector
     */
    public function store(CreatePrescriptionRequest $request): RedirectResponse
    {
        $input = $request->validated();
        $medicineRows = $this->normalizeMedicineRows($input['medicines'] ?? []);
        $duplicateIds = collect($medicineRows)->pluck('medicine_id')->duplicates();

        if ($duplicateIds->isNotEmpty()) {
            Flash::error(__('messages.prescription.not_add_duplicate_medicines'));

            return Redirect::back()->withInput();
        }

        $patient = Patient::findOrFail($input['patient_id']);
        $latestConsultation = $this->getLatestPatientConsultation($patient->user_id);
        $patientSummary = $this->buildPatientSummary($patient, $latestConsultation);

        $doctor = Doctor::find($input['doctor_id']);
        $doctorLicense = $input['doctor_license_s2_number']
            ?? optional($doctor)->s2_license_number
            ?? optional($doctor)->prc_license_number;

        $prescriptionData = Arr::only($input, [
            'patient_id',
            'doctor_id',
            'consultation_date',
            'icd10_diagnosis_id',
            'problem_description',
            'advice',
            'next_visit_days',
            'weight_kg',
            'pulse_rate',
            'body_temperature',
            'blood_pressure',
            'height_cm',
        ]);
        $prescriptionData['doctor_license_s2_number'] = $doctorLicense;
        $prescriptionData['weight_kg'] = $prescriptionData['weight_kg'] ?? $patientSummary['weight_kg'];
        $prescriptionData['pulse_rate'] = $prescriptionData['pulse_rate'] ?? $patientSummary['pulse_rate'];
        $prescriptionData['body_temperature'] = $prescriptionData['body_temperature'] ?? $patientSummary['body_temperature'];
        $prescriptionData['blood_pressure'] = $prescriptionData['blood_pressure'] ?? $patientSummary['blood_pressure'];
        $prescriptionData['height_cm'] = $prescriptionData['height_cm'] ?? $patientSummary['height_cm'];
        $prescriptionData['status'] = 1;

        DB::beginTransaction();
        try {
            $prescription = Prescription::create($prescriptionData);
            $medicineBill = DispenseRecord::create([
                'history_number' => 'HIS' . generateUniqueHistoryNumber(),
                'patient_id' => $prescription->patient_id,
                'doctor_id' => $prescription->doctor_id,
                'model_type' => Prescription::class,
                'model_id' => $prescription->id,
                'bill_date' => now(),
                'payment_status' => DispenseRecord::STATUS_UNPAID,
                'net_amount' => 0,
                'total' => 0,
            ]);

            $totalAmount = 0;

            foreach ($medicineRows as $row) {
                $medicine = Medicine::findOrFail($row['medicine_id']);
                $totalQuantity = $this->resolveTotalQuantity($row);

                if ((int) $medicine->available_quantity < $totalQuantity) {
                    $available = $medicine->available_quantity ?? 0;
                    throw new Exception(__('messages.prescription.available_quantity_of') . $medicine->name . ' ' . __('messages.prescription.is') . ' ' . $available . '.');
                }

                PrescriptionMedicine::create([
                    'prescription_id' => $prescription->id,
                    'medicine' => $row['medicine_id'],
                    'dosage' => $row['dosage'],
                    'route_of_administration' => $row['route_of_administration'],
                    'frequency' => $row['frequency'],
                    'duration_value' => $row['duration_value'],
                    'duration_unit' => $row['duration_unit'],
                    'total_quantity' => $totalQuantity,
                    'instructions' => $row['instructions'] ?: null,
                    // Keep legacy fields populated for compatibility with older readers.
                    'day' => (string) $row['duration_value'],
                    'dose_interval' => $row['frequency'],
                    'comment' => $row['instructions'] ?: null,
                ]);

                DispenseRecordItem::create([
                    'dispense_id' => $medicineBill->id,
                    'category_id' => null,
                    'item_type' => Medicine::class,
                    'item_id' => $medicine->id,
                    'item_name' => $medicine->name,
                    'item_price' => $medicine->selling_price,
                    'sale_quantity' => $totalQuantity,
                    'purchase_quantity' => 1,
                    'tax' => 0,
                    'amount' => $medicine->selling_price,
                    'sub_total' => $medicine->selling_price,
                    'total' => ((float) $medicine->selling_price * $totalQuantity),
                ]);

                $totalAmount += ((float) $medicine->selling_price * $totalQuantity);
            }

            $medicineBill->update([
                'net_amount' => $totalAmount,
                'total' => $totalAmount,
            ]);

            DB::commit();
            Flash::success(__('messages.prescription.prescription_saved'));

            return redirect(route($this->resolvePrescriptionShowRoute(), $prescription->id));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Prescription store failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Flash::error($e->getMessage());

            return Redirect::back()->withInput();
        }
    }

    /**
     * @return Factory|RedirectResponse|Redirector|View
     */
    public function show(Prescription $prescription)
    {
        if (! canAccessRecord(Prescription::class, $prescription->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $prescription = $this->prescriptionRepository->find($prescription->id);
        if (empty($prescription)) {
            Flash::error(__('messages.flash.prescription_not_found'));

            return Redirect::back();
        }

        return redirect(route($this->resolvePrescriptionShowRoute(), $prescription->id));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|RedirectResponse
     */
    public function edit(Prescription $prescription)
    {
        if (! canAccessRecord(Prescription::class, $prescription->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $user = getLogInUser();
        if ($user && $user->hasRole('doctor')) {
            $patientPrescriptionHasDoctor = Prescription::whereId($prescription->id)->whereDoctorId($user->doctor->id)->exists();
            if (! $patientPrescriptionHasDoctor) {
                return Redirect::back();
            }
        }

        $prescription->load([
            'patient.user:id,first_name,last_name,university_id_number',
            'doctor',
            'diagnosis',
            'getMedicine.medicines',
        ]);

        $patient = $prescription->patient;
        $latestConsultation = $this->getLatestPatientConsultation($patient->user_id);
        $patientSummary = $this->buildPatientSummary($patient, $latestConsultation);

        $doctorOptions = $this->prescriptionRepository->getDoctors();
        $doctors = $doctorOptions instanceof \Illuminate\Support\Collection ? $doctorOptions->toArray() : (array) $doctorOptions;

        $doctorMeta = Doctor::query()
            ->select('id', 'prc_license_number', 's2_license_number')
            ->whereIn('id', array_keys($doctors))
            ->get()
            ->mapWithKeys(function (Doctor $doctor) {
                return [
                    $doctor->id => [
                        'license' => $doctor->s2_license_number ?: $doctor->prc_license_number,
                    ],
                ];
            });

        $medicineOptions = Medicine::query()
            ->select('id', 'name', 'available_quantity')
            ->orderBy('name')
            ->get();

        $diagnosisOptions = Diagnose::query()->orderBy('diagnoses')->pluck('diagnoses', 'id')->toArray();

        $medicineRows = $prescription->getMedicine
            ->map(function (PrescriptionMedicine $medicineRow) {
                $durationValue = (int) ($medicineRow->duration_value ?: $medicineRow->day ?: 1);
                $frequency = (int) ($medicineRow->frequency ?: $medicineRow->dose_interval ?: 1);

                return [
                    'medicine_id' => (int) $medicineRow->medicine,
                    'dosage' => (string) $medicineRow->dosage,
                    'route_of_administration' => $medicineRow->route_of_administration ?: Prescription::ROUTE_ORAL,
                    'frequency' => $frequency,
                    'duration_value' => $durationValue,
                    'duration_unit' => $medicineRow->duration_unit ?: Prescription::DURATION_UNIT_DAY,
                    'total_quantity' => (int) ($medicineRow->total_quantity ?: ($durationValue * $frequency)),
                    'instructions' => $medicineRow->instructions ?: (string) $medicineRow->comment,
                ];
            })
            ->values()
            ->toArray();

        return view('prescriptions.edit', compact(
            'prescription',
            'patient',
            'patientSummary',
            'doctors',
            'doctorMeta',
            'medicineOptions',
            'diagnosisOptions',
            'medicineRows'
        ));
    }

    /**
     * @return RedirectResponse|Redirector
     */
    public function update(Prescription $prescription, UpdatePrescriptionRequest $request): RedirectResponse
    {
        $prescription = $this->prescriptionRepository->find($prescription->id);
        if (empty($prescription)) {
            Flash::error(__('messages.flash.prescription_not_found'));

            return Redirect::back();
        }

        $input = $request->validated();
        $medicineRows = $this->normalizeMedicineRows($input['medicines'] ?? []);
        $duplicateIds = collect($medicineRows)->pluck('medicine_id')->duplicates();

        if ($duplicateIds->isNotEmpty()) {
            Flash::error(__('messages.prescription.not_add_duplicate_medicines'));

            return Redirect::back()->withInput();
        }

        $patient = Patient::findOrFail($input['patient_id']);
        $latestConsultation = $this->getLatestPatientConsultation($patient->user_id);
        $patientSummary = $this->buildPatientSummary($patient, $latestConsultation);

        $doctor = Doctor::find($input['doctor_id']);
        $doctorLicense = $input['doctor_license_s2_number']
            ?? optional($doctor)->s2_license_number
            ?? optional($doctor)->prc_license_number;

        $prescriptionData = Arr::only($input, [
            'patient_id',
            'doctor_id',
            'consultation_date',
            'icd10_diagnosis_id',
            'problem_description',
            'advice',
            'next_visit_days',
            'weight_kg',
            'pulse_rate',
            'body_temperature',
            'blood_pressure',
            'height_cm',
        ]);
        $prescriptionData['doctor_license_s2_number'] = $doctorLicense;
        $prescriptionData['weight_kg'] = $prescriptionData['weight_kg'] ?? $patientSummary['weight_kg'];
        $prescriptionData['pulse_rate'] = $prescriptionData['pulse_rate'] ?? $patientSummary['pulse_rate'];
        $prescriptionData['body_temperature'] = $prescriptionData['body_temperature'] ?? $patientSummary['body_temperature'];
        $prescriptionData['blood_pressure'] = $prescriptionData['blood_pressure'] ?? $patientSummary['blood_pressure'];
        $prescriptionData['height_cm'] = $prescriptionData['height_cm'] ?? $patientSummary['height_cm'];

        DB::beginTransaction();
        try {
            $prescription->update($prescriptionData);

            $medicineBill = DispenseRecord::whereModelType(Prescription::class)
                ->whereModelId($prescription->id)
                ->first();

            if (empty($medicineBill)) {
                $medicineBill = DispenseRecord::create([
                    'history_number' => 'HIS' . generateUniqueHistoryNumber(),
                    'patient_id' => $prescription->patient_id,
                    'doctor_id' => $prescription->doctor_id,
                    'model_type' => Prescription::class,
                    'model_id' => $prescription->id,
                    'bill_date' => now(),
                    'payment_status' => DispenseRecord::STATUS_UNPAID,
                    'net_amount' => 0,
                    'total' => 0,
                ]);
            } else {
                $medicineBill->dispenseItems()->delete();
            }

            $prescription->getMedicine()->delete();

            $totalAmount = 0;
            foreach ($medicineRows as $row) {
                $medicine = Medicine::findOrFail($row['medicine_id']);
                $totalQuantity = $this->resolveTotalQuantity($row);

                if ((int) $medicine->available_quantity < $totalQuantity) {
                    $available = $medicine->available_quantity ?? 0;
                    throw new Exception(__('messages.prescription.available_quantity_of') . $medicine->name . ' ' . __('messages.prescription.is') . ' ' . $available . '.');
                }

                PrescriptionMedicine::create([
                    'prescription_id' => $prescription->id,
                    'medicine' => $row['medicine_id'],
                    'dosage' => $row['dosage'],
                    'route_of_administration' => $row['route_of_administration'],
                    'frequency' => $row['frequency'],
                    'duration_value' => $row['duration_value'],
                    'duration_unit' => $row['duration_unit'],
                    'total_quantity' => $totalQuantity,
                    'instructions' => $row['instructions'] ?: null,
                    // Keep legacy fields populated for compatibility with older readers.
                    'day' => (string) $row['duration_value'],
                    'dose_interval' => $row['frequency'],
                    'comment' => $row['instructions'] ?: null,
                ]);

                DispenseRecordItem::create([
                    'dispense_id' => $medicineBill->id,
                    'category_id' => null,
                    'item_type' => Medicine::class,
                    'item_id' => $medicine->id,
                    'item_name' => $medicine->name,
                    'item_price' => $medicine->selling_price,
                    'sale_quantity' => $totalQuantity,
                    'purchase_quantity' => 1,
                    'tax' => 0,
                    'amount' => $medicine->selling_price,
                    'sub_total' => $medicine->selling_price,
                    'total' => ((float) $medicine->selling_price * $totalQuantity),
                ]);

                $totalAmount += ((float) $medicine->selling_price * $totalQuantity);
            }

            $medicineBill->update([
                'patient_id' => $prescription->patient_id,
                'net_amount' => $totalAmount,
                'total' => $totalAmount,
            ]);

            DB::commit();
            Flash::success(__('messages.prescription.prescription_updated'));

            return redirect(route($this->resolvePrescriptionShowRoute(), $prescription->id));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Prescription update failed', [
                'prescription_id' => $prescription->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Flash::error($e->getMessage());

            return Redirect::back()->withInput();
        }
    }

    private function getLatestPatientConsultation(?int $userId): ?DocumentIssuance
    {
        if (empty($userId)) {
            return null;
        }

        return DocumentIssuance::query()
            ->where('user_id', $userId)
            ->where('document_type', 'consultation_form')
            ->latest('id')
            ->first();
    }

    private function buildPatientSummary(Patient $patient, ?DocumentIssuance $consultation): array
    {
        $comorbidities = $patient->comorbidities;
        if (is_array($comorbidities)) {
            $comorbidities = implode(', ', array_filter($comorbidities));
        }

        return [
            'allergies' => $patient->allergies ?: optional($consultation)->allergies,
            'comorbidities' => $comorbidities ?: optional($consultation)->comorbidities,
            'maintenance' => $patient->maintenance ?: optional($consultation)->maintenance,
            'weight_kg' => optional($consultation)->vital_signs_weight,
            'pulse_rate' => optional($consultation)->vital_signs_pr,
            'body_temperature' => optional($consultation)->vital_signs_temp,
            'blood_pressure' => optional($consultation)->vital_signs_bp,
            'height_cm' => optional($consultation)->vital_signs_height,
            'consultation_date' => optional($consultation)->examined_on
                ? Carbon::parse($consultation->examined_on)->format('Y-m-d')
                : now()->format('Y-m-d'),
        ];
    }

    private function normalizeMedicineRows(array $rows): array
    {
        return collect($rows)
            ->map(function (array $row) {
                return [
                    'medicine_id' => (int) $row['medicine_id'],
                    'dosage' => trim((string) $row['dosage']),
                    'route_of_administration' => (string) $row['route_of_administration'],
                    'frequency' => (int) $row['frequency'],
                    'duration_value' => (int) $row['duration_value'],
                    'duration_unit' => (string) $row['duration_unit'],
                    'total_quantity' => ! empty($row['total_quantity']) ? (int) $row['total_quantity'] : null,
                    'instructions' => isset($row['instructions']) ? trim((string) $row['instructions']) : null,
                ];
            })
            ->values()
            ->toArray();
    }

    private function resolveTotalQuantity(array $row): int
    {
        if (! empty($row['total_quantity'])) {
            return max(1, (int) $row['total_quantity']);
        }

        $durationDays = $this->durationToDays((int) $row['duration_value'], (string) $row['duration_unit']);

        return max(1, ((int) $row['frequency'] * $durationDays));
    }

    private function durationToDays(int $value, string $unit): int
    {
        $safeValue = max(1, $value);

        return match ($unit) {
            Prescription::DURATION_UNIT_WEEK => $safeValue * 7,
            Prescription::DURATION_UNIT_MONTH => $safeValue * 30,
            default => $safeValue,
        };
    }

    private function resolvePrescriptionShowRoute(): string
    {
        $user = getLogInUser();

        if ($user && $user->hasRole('staff')) {
            return 'staff.prescription.medicine.show';
        }

        if ($user && $user->hasRole('doctor')) {
            return 'doctors.prescription.medicine.show';
        }

        return 'prescription.medicine.show';
    }

    /**
     * @return JsonResponse|RedirectResponse|Redirector
     *
     * @throws Exception
     */
    public function destroy(Prescription $prescription)
    {
        if (! canAccessRecord(Prescription::class, $prescription->id)) {
            return $this->sendError(__('messages.flash.prescription_not_found'));
        }

        if (getLogInUser()->hasRole('doctor')) {
            $patientPrescriptionHasDoctor = Prescription::whereId($prescription->id)->whereDoctorId(getLogInUser()->doctor->id)->exists();
            if (! $patientPrescriptionHasDoctor) {
                return $this->sendError(__('messages.flash.prescription_not_found'));
            }
        }

        $prescription = $this->prescriptionRepository->find($prescription->id);
        if (empty($prescription)) {
            Flash::error(__('messages.flash.prescription_not_found'));

            return Redirect::back();
        }
        $prescription->delete();

        return $this->sendSuccess(__('messages.flash.prescription_deleted'));
    }

    public function activeDeactiveStatus(int $id): JsonResponse
    {
        $prescription = Prescription::findOrFail($id);
        $status = ! $prescription->status;
        $prescription->update(['status' => $status]);

        return $this->sendSuccess(__('messages.flash.status_update'));
    }

    public function showModal($id): JsonResponse
    {
        if (getLogInUser()->hasRole('doctor')) {
            $patientPrescriptionHasDoctor = Prescription::whereId($id)->whereDoctorId(getLogInUser()->doctor->id)->exists();
            if (! $patientPrescriptionHasDoctor) {
                return $this->sendError(__('messages.flash.prescription_not_found'));
            }
        }

        $prescription = $this->prescriptionRepository->find($id);
        $prescription->load(['patient.patientUser', 'doctor.doctorUser']);
        if (empty($prescription)) {
            return $this->sendError(__('messages.flash.prescription_not_found'));
        }

        return $this->sendResponse($prescription, __('messages.flash.prescription_retrieved'));
    }

    public function prescreptionMedicineStore(CreateMedicineRequest $request): JsonResponse
    {
        $input = $request->all();
        $this->medicineRepository->create($input);

        return $this->sendSuccess(__('messages.medicine.medicine') . ' ' . __('messages.medicine.saved_successfully'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View
     */
    public function prescriptionMedicineShowFunction($id)
    {
        if (getLogInUser()->hasRole('doctor')) {
            $patientPrescriptionHasDoctor = Prescription::whereId($id)->whereDoctorId(getLogInUser()->doctor->id)->exists();
            if (! $patientPrescriptionHasDoctor) {
                return Redirect::back();
            }
        }

        $data = $this->prescriptionRepository->getSettingList();

        $prescription = $this->prescriptionRepository->getData($id);

        $medicines = $this->prescriptionRepository->getMedicineData($id);

        return view('prescriptions.show_with_medicine', compact('prescription', 'medicines', 'data'));
    }

    public function convertToPDF($id): \Illuminate\Http\Response
    {
        try {
            // Get settings
            $data = $this->prescriptionRepository->getSettingList();

            // Load prescription with all required relationships
            $prescriptionModel = Prescription::with([
                'patient.user',
                'doctor.user',
                'doctor.address',
                'getMedicine.medicines',
                'appointment'
            ])->findOrFail($id);

            // Prepare patient information
            $patientInfo = [
                'name' => $prescriptionModel->patient->user->full_name ?? '',
                'address' => $prescriptionModel->patient->address ?? $prescriptionModel->patient->user->address ?? '',
                'age' => null,
                'date' => \Carbon\Carbon::parse($prescriptionModel->created_at)->format('M d, Y')
            ];

            // Calculate age if DOB exists
            if ($prescriptionModel->patient->user->dob) {
                $patientInfo['age'] = \Carbon\Carbon::parse($prescriptionModel->patient->user->dob)
                    ->diff(\Carbon\Carbon::now())->y . ' years';
            }

            // Prepare prescription content
            $prescriptionContent = [];

            if ($prescriptionModel->problem_description) {
                $prescriptionContent['problem'] = $prescriptionModel->problem_description;
            }

            if (!$prescriptionModel->getMedicine->isEmpty()) {
                $medications = [];
                foreach ($prescriptionModel->getMedicine as $medicine) {
                    $medications[] = [
                        'name' => $medicine->medicines->name ?? 'N/A',
                        'dosage' => $medicine->dosage,
                        'timing' => ($medicine->time == 0) ? 'after meal' : 'before meal',
                        'duration' => $medicine->day . ' days'
                    ];
                }
                $prescriptionContent['medications'] = $medications;
            }

            if ($prescriptionModel->test) {
                $prescriptionContent['tests'] = $prescriptionModel->test;
            }

            if ($prescriptionModel->advice) {
                $prescriptionContent['advice'] = $prescriptionModel->advice;
            }

            // Prepare doctor information
            $doctorInfo = [
                'name' => $prescriptionModel->doctor->user->full_name ?? '',
                'specialty' => $prescriptionModel->doctor->specialist ?? '',
                'contact' => $prescriptionModel->doctor->user->contact ?? '',
                'email' => $prescriptionModel->doctor->user->email ?? ''
            ];

            // Prepare signature information
            $signatureInfo = [
                'date' => \Carbon\Carbon::parse($prescriptionModel->created_at)->format('M d, Y'),
                'doctor_name' => $prescriptionModel->doctor->user->full_name ?? ''
            ];


            // Create PDF with optimized settings
            $pdf = Pdf::loadView('prescriptions.prescription_pdf', compact(
                'patientInfo',
                'prescriptionContent',
                'doctorInfo',
                'signatureInfo',
                'data'
            ));
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => false,
            ]);

            // Return inline PDF
            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="prescription-' . $id . '.pdf"');
        } catch (\Exception $e) {
            Log::error('PDF generation failed: ' . $e->getMessage(), [
                'prescription_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response('PDF generation failed: ' . $e->getMessage(), 500)
                ->header('Content-Type', 'text/plain');
        }
    }
}
