<?php

namespace App\Http\Controllers;

use Laracasts\Flash\Flash;
use Exception;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use App\Repositories\PatientRepository;
use App\Services\PatientService;
use App\Services\SettingsService;
use App\Http\Requests\CreatePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PatientController extends AppBaseController
{
    /** @var PatientRepository */
    private $patientRepository;

    /** @var PatientService */
    private $patientService;

    public function __construct(PatientRepository $patientRepo, PatientService $patientService)
    {
        $this->patientRepository = $patientRepo;
        $this->patientService = $patientService;
    }

    /**
     * Get the appropriate patient index route based on user role
     */
    private function getPatientIndexRoute(): string
    {
        if (isRole('clinic_admin')) {
            return route('patients.index');
        } elseif (isRole('staff')) {
            return route('staff.patients.index');
        } elseif (isRole('doctor')) {
            return route('doctors.patients.index');
        }

        return route('patients.index');
    }

    /**
     * Display a listing of the Patient.
     *
     * @return Application|Factory|View
     */
    public function index(): \Illuminate\View\View
    {
        return view('patients.index');
    }

    /**
     * Show the form for creating a new Patient.
     *
     * @return Application|Factory|View
     */
    public function create(): \Illuminate\View\View
    {
        $data = $this->patientRepository->getData();

        return view('patients.create', compact('data'));
    }

    /**
     * Store a newly created Patient in storage.
     *
     * @return Application|Redirector|RedirectResponse
     */
    public function store(CreatePatientRequest $request): RedirectResponse
    {
        $input = $request->all();
        unset($input['campus_address'], $input['permanent_address']);

        $patient = $this->patientRepository->store($input);

        Flash::success(__('messages.flash.patient_create'));

        return redirect($this->getPatientIndexRoute());
    }

    /**
     * Display the specified Patient.
     *
     * @return Application|Factory|View|RedirectResponse
     */
    public function show(Patient $patient)
    {
        if (empty($patient)) {
            Flash::error(__('messages.flash.patient_not_found'));

            return redirect($this->getPatientIndexRoute());
        }

        $patient = $this->patientRepository->getPatientData($patient);
        $data = [];

        return view('patients.show', compact('patient', 'data'));
    }

    /**
     * Show the form for editing the specified Patient.
     *
     * @return Application|Factory|View
     */
    public function edit(Patient $patient)
    {
        if (empty($patient)) {
            Flash::error(__('messages.flash.patient_not_found'));

            return redirect($this->getPatientIndexRoute());
        }
        $data = $this->patientRepository->getData();

        // Load cities and barangays for existing patient address
        if ($patient->address) {
            if ($patient->address->state_id) {
                $data['cities'] = getCities($patient->address->state_id);
            }
            if ($patient->address->city_id) {
                $data['barangays'] = getBarangays($patient->address->city_id);
            }
        }

        return view('patients.edit', compact('data', 'patient'));
    }

    /**
     * Update the specified Patient in storage.
     *
     * @return Application|Redirector|RedirectResponse
     */
    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        $input = request()->except(['_method', '_token']);
        unset($input['campus_address'], $input['permanent_address']);

        if (empty($patient)) {
            Flash::error(__('messages.flash.patient_not_found'));

            return redirect($this->getPatientIndexRoute());
        }

        $patient = $this->patientRepository->update($input, $patient);

        Flash::success(__('messages.flash.patient_update'));

        return redirect($this->getPatientIndexRoute());
    }

    /**
     * Remove the specified Patient from storage.
     * Note: Cascade delete is handled in Patient model's boot() method
     */
    public function destroy(Patient $patient): JsonResponse
    {
        try {
            DB::beginTransaction();

            $patient->delete();

            DB::commit();

            return $this->sendSuccess('Patient and all related data archived successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function restore($id): JsonResponse
    {
        try {
            $patient = Patient::withTrashed()->findOrFail($id);
            $patient->restore();

            return $this->sendSuccess('Patient restored successfully!');
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function deleteOldPatient()
    {
        // Include archived (soft-deleted) patients in the keep-set — Patient::pluck applies
        // the SoftDeletes scope, which would otherwise treat archived patients as "old" and
        // wipe their user accounts too. CRUD-DL. (Route is POST-only so CSRF applies.)
        $patients = Patient::withTrashed()->pluck('user_id')->toArray();

        User::whereType(User::PATIENT)->whereNotIn('id', $patients)->delete();

        return $this->sendSuccess(__('messages.common.deleted_successfully'));
    }

    public function showMyHistory(Patient $patient)
    {
        // Load consultations and related data
        $patient->load([
            'documentIssuances',
        ]);

        // Fetch all consultations with `consultation_form` type
        $consultations = $patient->documentIssuances->where('document_type', 'consultation_form');

        // Fetch all medical certificates
        $medicalCertificates = $patient->documentIssuances->where('document_type', 'medical_certificate');

        // Load prescriptions with associated medicines and dispenser info
        $prescriptions = \App\Models\Prescription::with([
            'getMedicine.medicines',   // PrescriptionMedicine::medicines() → Medicine
            'doctor.user',             // Prescription::doctor() → Doctor::user() → User
            'dispensedBy',             // Prescription::dispensedBy() → User
        ])->where('patient_id', $patient->id)->latest()->get();

        // Load dispense records (medicine bills) with items
        $dispenseRecords = \App\Models\DispenseRecord::with([
            'dispenseItems.medicine',  // DispenseRecord::dispenseItems() → DispenseRecordItem::medicine() → Medicine
            'doctor.user',             // DispenseRecord::doctor() → Doctor::user() → User
        ])->where('patient_id', $patient->id)->latest()->get();

        // Pass the data to the view
        return view('patients.view_patient', compact(
            'patient',
            'consultations',
            'medicalCertificates',
            'prescriptions',
            'dispenseRecords'
        ));
    }

    public function resetPassword(User $user): JsonResponse
    {
        // Only PATIENT accounts may be reset here — otherwise a doctor/staff with
        // manage_patients could reset the clinic_admin's password and take over. CRUD-AUTH-4.
        abort_unless((int) $user->type === User::PATIENT, 403);

        try {
            $user->update([
                'password' => Hash::make('123456')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password has been reset to default (123456) successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
