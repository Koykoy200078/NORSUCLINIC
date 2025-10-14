<?php

namespace App\Http\Controllers;

use App\Models\PatientQueue;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TransactionController extends AppBaseController
{
    /** @var TransactionRepository */
    private $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->TransactionRepository = $transactionRepository;
    }

    /**
     * @return Application|Factory|View
     */
    public function index(): \Illuminate\View\View
    {
        if (getLogInUser()->hasRole('patient')) {
            $logo = Setting::where('key', 'logo')->pluck('value');
            return view('transactions.patient_transaction', compact('logo'));
        }

        if (getLogInUser()->hasRole('doctor')) {
            return view('transactions.doctors_transaction');
        }

        return view('transactions.index');
    }

    public function show($id): View|Factory|RedirectResponse|Application
    {
        if (getLogInUser()->hasRole('patient')) {
            $transaction = Transaction::findOrFail($id);
            if ($transaction->user_id !== getLogInUserId()) {
                return redirect()->back();
            }
        }
        if (getLogInUser()->hasRole('doctor')) {
            $transaction = Transaction::with('doctorappointment')->findOrFail($id);
            if (! $transaction->doctorappointment) {
                return redirect()->back();
            }
        }
        $transaction = $this->TransactionRepository->show($id);

        return view('transactions.show', compact('transaction'));
    }

    public function changeTransactionStatus(Request $request): JsonResponse
    {
        $input = $request->all();

        $transaction = Transaction::findOrFail($input['id']);
        $appointment = PatientQueue::where('appointment_unique_id', $transaction->appointment_id)->first();

        if (getLogInUser()->hasrole('doctor')) {
            $doctor = PatientQueue::where('appointment_unique_id', $transaction->appointment_id)->whereDoctorId(getLogInUser()->doctor->id);
            if (! $doctor->exists()) {
                return $this->sendError(__('messages.common.not_allow__assess_record'));
            }
        }

        $appointment->update([
            'payment_method' => PatientQueue::MANUALLY,
            'payment_type' => PatientQueue::PAID,
        ]);

        $transaction->update([
            'status' => ! $transaction->status,
            'accepted_by' => $input['acceptPaymentUserId'],
        ]);

        $appointmentNotification = Transaction::with('acceptedPaymentUser')->whereAppointmentId($appointment['appointment_unique_id'])->first();

        $fullTime = $appointment->from_time.''.$appointment->from_time_type.' - '.$appointment->to_time.''.$appointment->to_time_type.' '.' '.Carbon::parse($appointment->date)->format('jS M, Y');
        $patient = Patient::whereId($appointment->patient_id)->with('user')->first();
        Notification::create([
            'title' => $appointmentNotification->acceptedPaymentUser->full_name.' changed the payment status '.PatientQueue::PAYMENT_TYPE[PatientQueue::PENDING].' to '.PatientQueue::PAYMENT_TYPE[$appointment->payment_type].' for appointment '.$fullTime,
            'type' => Notification::PAYMENT_DONE,
            'user_id' => $patient->user_id,
        ]);

        return $this->sendSuccess(__("messages.flash.status_update"));
    }
}

