<?php

namespace App\Http\Controllers;

use Laracasts\Flash\Flash;
use Exception;
use App\Models\ConsultationMedicine;
use App\Models\DispenseHistoryEntry;
use App\Models\DispenseRecord;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use App\Repositories\PatientRepository;
use App\Services\SettingsService;
use App\Traits\LogsActivity;
use App\Http\Requests\CreatePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PatientController extends AppBaseController
{
    use LogsActivity;

    /** @var PatientRepository */
    private $patientRepository;

    public function __construct(PatientRepository $patientRepo)
    {
        $this->patientRepository = $patientRepo;
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

        return redirectByRole('patients.index');
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

            return redirectByRole('patients.index');
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

            return redirectByRole('patients.index');
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

            return redirectByRole('patients.index');
        }

        $patient = $this->patientRepository->update($input, $patient);

        Flash::success(__('messages.flash.patient_update'));

        return redirectByRole('patients.index');
    }

    /**
     * Remove the specified Patient from storage.
     * Note: Cascade delete is handled in Patient model's boot() method
     */
    public function destroy(Patient $patient): JsonResponse
    {
        try {
            DB::beginTransaction();

            $patientName = $patient->user?->full_name ?? ('Patient #' . $patient->id);

            $patient->delete();

            self::logPatientDeletion($patient, $patientName);

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

            self::logPatientRestoration($patient, $patient->user?->full_name ?? ('Patient #' . $patient->id));

            return $this->sendSuccess('Patient restored successfully!');
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function showMyHistory(Patient $patient)
    {
        // The page belongs to the Patients module, but each history part belongs to its own module: a staff member with
        // Manage Patients alone sees the patient's record and none of the clinical content of the others (the parts are
        // neither drawn nor read). The plan's rule: a role's permissions are the only thing that decides what it may open.
        $mayConsultations = canUseModule('consultations');
        $mayCertificates = canUseModule('certificates');
        $mayPrescriptions = canUseModule('prescriptions');
        $mayDispensing = canUseModule('dispensing');

        // Load consultations and related data. The compare table reads each consultation's medicines and nurse in charge,
        // so they are loaded for all of them at once (not one query per consultation).
        if ($mayConsultations) {
            $patient->load([
                'documentIssuances.consultationMedicines.medicine:id,name',
                'documentIssuances.nursingInCharge:id,first_name,last_name',
            ]);
        } elseif ($mayCertificates) {
            // Certificates and excuse slips only: the consultations' medicines and nurses are not read.
            $patient->load('documentIssuances');
        } else {
            $patient->setRelation('documentIssuances', collect());
        }

        // Fetch all consultations with `consultation_form` type
        $consultations = $mayConsultations ? $patient->documentIssuances->where('document_type', 'consultation_form') : collect();

        // Fetch all medical certificates
        $medicalCertificates = $mayCertificates ? $patient->documentIssuances->where('document_type', 'medical_certificate') : collect();

        // Fetch all excuse slips (they were issued but never listed in the history)
        $excuseSlips = $mayCertificates ? $patient->documentIssuances->where('document_type', 'excuse_slip') : collect();

        // Load prescriptions with associated medicines and dispenser info
        $prescriptions = $mayPrescriptions
            ? \App\Models\Prescription::with([
                'getMedicine.medicines',   // PrescriptionMedicine::medicines() → Medicine
                'doctor.user',             // Prescription::doctor() → Doctor::user() → User
                'dispensedBy',             // Prescription::dispensedBy() → User
            ])->where('patient_id', $patient->id)->latest()->get()
            : collect();

        // The same history as Dispensing > Dispense History: dispense records, DISPENSED prescriptions and the
        // medicines recorded in consultations (a pending or cancelled prescription handed nothing out).
        $dispenseHistory = $mayDispensing ? $this->dispenseHistoryOf($patient) : collect();

        // Pass the data to the view
        return view('patients.view_patient', compact(
            'patient',
            'consultations',
            'medicalCertificates',
            'excuseSlips',
            'prescriptions',
            'dispenseHistory'
        ));
    }

    /**
     * What left the clinic for this patient, newest first: one element per history row,
     * `['entry' => DispenseHistoryEntry, 'items' => [['name', 'quantity', 'used_for'], ...]]`.
     *
     * @return Collection<int, array{entry: DispenseHistoryEntry, items: Collection}>
     */
    private function dispenseHistoryOf(Patient $patient): Collection
    {
        $entries = DispenseHistoryEntry::where('patient_id', $patient->id)
            ->orderByDesc('dispensed_at')
            ->orderByDesc('record_id')
            ->orderByDesc('id')
            ->get();

        $records = DispenseRecord::with('dispenseItems.medicine:id,name')
            ->whereIn('id', $entries->where('source', '!=', DispenseHistoryEntry::SOURCE_CONSULTATION)->pluck('record_id'))
            ->get()
            ->keyBy('id');

        $consultationLines = ConsultationMedicine::with('medicine:id,name')
            ->whereIn('request_document_id', $entries->where('source', DispenseHistoryEntry::SOURCE_CONSULTATION)->pluck('record_id'))
            ->get()
            ->groupBy('request_document_id');

        return $entries->map(function (DispenseHistoryEntry $entry) use ($records, $consultationLines) {
            $items = $entry->isConsultation()
                ? ($consultationLines[$entry->record_id] ?? collect())->map(fn (ConsultationMedicine $line) => [
                    'name' => $line->medicine?->name ?? __('messages.common.n/a'),
                    'quantity' => (int) $line->quantity,
                    'used_for' => $line->used_for,
                ])
                : ($records[$entry->record_id]?->dispenseItems ?? collect())->map(fn ($item) => [
                    'name' => $item->medicine?->name ?? __('messages.common.n/a'),
                    'quantity' => (int) $item->quantity,
                    'used_for' => null,
                ]);

            return ['entry' => $entry, 'items' => $items->values()];
        });
    }

    public function resetPassword(User $user): JsonResponse
    {
        // Only PATIENT accounts may be reset here — otherwise a doctor/staff with
        // manage_patients could reset the clinic_admin's password and take over. CRUD-AUTH-4.
        abort_unless((int) $user->type === User::PATIENT, 403);

        try {
            $user->update([
                'password' => Hash::make(User::DEFAULT_PASSWORD)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password has been reset to default ('.User::DEFAULT_PASSWORD.') successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
