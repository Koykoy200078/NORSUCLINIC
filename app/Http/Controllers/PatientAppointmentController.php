<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class PatientAppointmentController extends AppBaseController
{
    /**
     * @return Application|Factory|View
     */
    public function index(): \Illuminate\View\View
    {
        $paymentStatus = [];
        $logo = Setting::where('key', 'logo')->pluck('value');

        return view('patients.appointments.index', compact('paymentStatus', 'logo'));
    }
}
