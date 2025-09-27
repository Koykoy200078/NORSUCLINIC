<?php

namespace App\Http\Controllers;

use Laracasts\Flash\Flash;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Visit;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\DataTables\PatientDataTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use App\Repositories\PatientRepository;
use App\Services\PatientService;
use App\Services\SettingsService;
use Yajra\DataTables\Facades\DataTables;
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
        if (getLogInUser()->hasRole('doctor')) {
            $doctor = Appointment::wherePatientId($patient->id)->whereDoctorId(getLogInUser()->doctor->id);
            if (! $doctor->exists()) {
                return redirect()->back();
            }
        }

        if (empty($patient)) {
            Flash::error(__('messages.flash.patient_not_found'));

            return redirect($this->getPatientIndexRoute());
        }

        $patient = $this->patientRepository->getPatientData($patient);
        $appointmentStatus = Appointment::ALL_STATUS;
        $todayDate = Carbon::now()->format('Y-m-d');
        $data['todayAppointmentCount'] = Appointment::wherePatientId($patient['id'])->where(
            'date',
            '=',
            $todayDate
        )->count();
        $data['upcomingAppointmentCount'] = Appointment::wherePatientId($patient['id'])->where(
            'date',
            '>',
            $todayDate
        )->count();
        $data['completedAppointmentCount'] = Appointment::wherePatientId($patient['id'])->where(
            'date',
            '<',
            $todayDate
        )->count();

        return view('patients.show', compact('patient', 'appointmentStatus', 'data'));
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
        unset($data['patientUniqueId']);

        return view('patients.edit', compact('data', 'patient'));
    }

    /**
     * Update the specified Patient in storage.
     *
     * @return Application|Redirector|RedirectResponse
     */
    public function update(Patient $patient, UpdatePatientRequest $request): RedirectResponse
    {
        $input = request()->except(['_method', '_token', 'patient_unique_id']);

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
     */
    public function destroy(Patient $patient): JsonResponse
    {
        $existAppointment = Appointment::wherePatientId($patient->id)
            ->whereNotIn('status', [Appointment::CANCELLED, Appointment::FINISHED])
            ->exists();

        $existVisit = Visit::wherePatientId($patient->id)->exists();

        $transactions = Transaction::whereUserId($patient->user_id)->exists();

        if ($existAppointment || $existVisit || $transactions) {
            return $this->sendError(__('messages.flash.patient_used'));
        }

        try {
            DB::beginTransaction();

            $patient->delete();
            $patient->media()->delete();
            $patient->user()->delete();
            $patient->address()->delete();

            DB::commit();

            return $this->sendSuccess(__('messages.flash.patient_delete'));
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @return Application|RedirectResponse|Redirector
     *
     * @throws Exception
     */
    public function patientAppointment(Patient $patient, Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of((new PatientDataTable())->getAppointment($request->only([
                'status',
                'patientId',
                'filter_date',
            ])))->make(true);
        }

        return redirect($this->getPatientIndexRoute());
    }

    public function deleteOldPatient()
    {
        $patients =  Patient::pluck('user_id')->toArray();

        User::whereType(User::PATIENT)->whereNotIn('id', $patients)->delete();
    }

    public function showMyHistory(Patient $patient)
    {
        // Load consultations and related data
        $patient->load([
            'appointments.doctor',
            'requestDocuments' => function ($query) {
                $query->where('document_type', 'consultation_form');
            }
        ]);

        // Fetch all consultations with `consultation_form` type
        $consultations = $patient->requestDocuments->where('document_type', 'consultation_form');

        // Pass the data to the view
        return view('patients.view_patient', compact('patient', 'consultations'));
    }
}
