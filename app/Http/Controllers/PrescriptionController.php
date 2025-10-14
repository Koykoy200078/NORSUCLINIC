<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMedicineRequest;
use App\Http\Requests\CreatePrescriptionRequest;
use App\Http\Requests\UpdatePrescriptionRequest;
use App\Models\PatientQueue;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Repositories\DoctorRepository;
use App\Repositories\MedicineRepository;
use App\Repositories\PrescriptionRepository;
use App\Services\PrescriptionService;
use App\Services\SettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
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
     * Show the form for creating a new Prescription.
     *
     * @return Factory|View
     */
    public function create($appointmentId): View
    {
        $patients = $this->prescriptionRepository->getPatients();
        $doctors = $this->prescriptionRepository->getDoctors();
        $medicines = $this->prescriptionRepository->getMedicines();
        $medicinesQuantity = $this->prescriptionRepository->getMedicinesQuantity();
        $data = $this->medicineRepository->getSyncList();
        $medicineList = $this->medicineRepository->getMedicineList();
        $mealList = $this->medicineRepository->getMealList();
        $doseDuration = $this->medicineRepository->getDoseDurationList();
        $doseInverval = $this->medicineRepository->getDoseInterValList();
        $appointment = PatientQueue::with('doctor', 'patient')->find($appointmentId);

        return view(
            'prescriptions.create',
            compact('patients', 'doctors', 'appointment', 'medicines', 'medicinesQuantity', 'medicineList', 'mealList', 'doseDuration', 'doseInverval', 'appointmentId')
        )->with($data);
    }

    /**
     * Store a newly created Prescription in storage.
     *
     * @return RedirectResponse|Redirector
     */
    public function store(CreatePrescriptionRequest $request): RedirectResponse
    {
        $input = $request->all();
        $input['status'] = isset($input['status']) ? 1 : 0;

        if (isset($input['medicine'])) {
            $arr = collect($input['medicine']);
            $duplicateIds = $arr->duplicates();
            foreach ($input['medicine'] as $key => $value) {
                $medicine = Medicine::find($input['medicine'][$key]);
                if (! empty($duplicateIds)) {
                    foreach ($duplicateIds as $key => $value) {
                        $medicine = Medicine::find($duplicateIds[$key]);
                        Flash::error(__('messages.prescription.not_add_duplicate_medicines'));

                        return Redirect::back();
                    }
                }
            }
            foreach ($input['medicine'] as $key => $value) {
                $medicine = Medicine::find($input['medicine'][$key]);
                $qty = $input['day'][$key] * $input['dose_interval'][$key];
                if ($medicine->available_quantity < $qty) {
                    $available = $medicine->available_quantity == null ? 0 : $medicine->available_quantity;
                    // Flash::error('The available quantity of '.$medicine->name.' is '.$available.'.');
                    Flash::error(__('messages.prescription.available_quantity_of') . $medicine->name . ' ' . __('messages.prescription.is') . ' ' . $available . '.');

                    return Redirect::back();
                }
            }
        }

        $prescription = $this->prescriptionRepository->create($input);
        $showRoute = isRole('doctor') ? 'doctors.appointment.detail' : (isRole('patient') ? 'patients.appointment.detail' : 'appointments.show');
        $this->prescriptionRepository->createPrescription($input, $prescription);
        Flash::success(__('messages.prescription.prescription_saved'));

        return redirect(route($showRoute, $input['appointment_id']));
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

        return view('prescriptions.show')->with('prescription', $prescription);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|RedirectResponse
     */
    public function edit($appointmentId, Prescription $prescription)
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

        $appointment = PatientQueue::with('doctor', 'patient')->find($appointmentId);

        $patients = $this->prescriptionRepository->getPatients();
        $doctors = $this->prescriptionRepository->getDoctors();
        $data['medicines'] = Medicine::pluck('name', 'id')->toArray();
        $medicines = $data;
        $data = $this->medicineRepository->getSyncList();
        $medicineList = $this->medicineRepository->getMedicineList();
        $mealList = $this->medicineRepository->getMealList();
        $doseDuration = $this->medicineRepository->getDoseDurationList();
        $doseInverval = $this->medicineRepository->getDoseInterValList();

        return view('prescriptions.edit', compact('patients', 'appointment', 'appointmentId', 'prescription', 'doctors', 'medicines', 'medicineList', 'mealList', 'doseDuration', 'doseInverval'))->with($data);
    }

    /**
     * @return RedirectResponse|Redirector
     */
    public function update(Prescription $prescription, UpdatePrescriptionRequest $request): RedirectResponse
    {
        $prescription = $this->prescriptionRepository->find($prescription->id);
        $input = $request->all();
        $input['status'] = isset($input['status']) ? 1 : 0;
        $prescription->load('getMedicine');
        $arr = collect($input['medicine']);
        $duplicateIds = $arr->duplicates();
        foreach ($input['medicine'] as $key => $value) {
            $medicine = Medicine::find($input['medicine'][$key]);
            if (! empty($duplicateIds)) {
                foreach ($duplicateIds as $key => $value) {
                    $medicine = Medicine::find($duplicateIds[$key]);
                    Flash::error(__('messages.prescription.not_add_duplicate_medicines'));

                    return Redirect::back();
                }
            }
        }
        $prescriptionMedicineArray = [];
        $inputdoseAndMedicine = [];
        foreach ($prescription->getMedicine as $prescriptionMedicine) {
            $prescriptionMedicineArray[$prescriptionMedicine->medicine] = $prescriptionMedicine->dosage;
        }
        foreach ($request->medicine as $key => $value) {
            $inputdoseAndMedicine[$value] = $request->dosage[$key];
        }

        if (empty($prescription)) {
            Flash::error(__('messages.flash.prescription_not_found'));

            return Redirect::back();
        }

        foreach ($input['medicine'] as $key => $value) {
            $result = array_intersect($prescriptionMedicineArray, $inputdoseAndMedicine);
            $medicine = Medicine::find($input['medicine'][$key]);
            $qty = $input['day'][$key] * $input['dose_interval'][$key];

            if (! array_key_exists($input['medicine'][$key], $result) && $medicine->available_quantity < $qty) {
                $available = $medicine->available_quantity == null ? 0 : $medicine->available_quantity;
                // Flash::error('The available quantity of '.$medicine->name.' is '.$available.'.');
                Flash::error(__('messages.prescription.available_quantity_of') . $medicine->name . __('messages.prescription.is') . $available . '.');

                return Redirect::back();
            }
        }
        $showRoute = isRole('doctor') ? 'doctors.appointment.detail' : (isRole('patient') ? 'patients.appointment.detail' : 'appointments.show');
        $this->prescriptionRepository->prescriptionUpdate($prescription, $request->all());
        Flash::success(__('messages.prescription.prescription_updated'));

        return redirect(route($showRoute, $input['appointment_id']));
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

