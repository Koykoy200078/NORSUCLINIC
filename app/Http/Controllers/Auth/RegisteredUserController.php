<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laracasts\Flash\Flash;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|unique:users,email',
            'university_id_number' => 'nullable|string|max:100|unique:users,university_id_number',
            // 'password' => ['required', 'confirmed', 'min:6'],
            'toc' => 'required',
        ]);

        $universityIdNumber = strtoupper(trim((string) $request->input('university_id_number', '')));
        $patientIdentifier = $universityIdNumber !== '' ? $universityIdNumber : mb_strtoupper(Patient::generatePatientUniqueId());

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'university_id_number' => $universityIdNumber !== '' ? $universityIdNumber : null,
            // 'password' => Hash::make($request->password),
            'type' => User::PATIENT,
            'language' => 'en',
            'country_code' => getSettingValue('country_code'),
            'time_zone' => 'Asia/Manila',
        ]);

        $user->patient()->create([
            'patient_unique_id' => $patientIdentifier,
        ]);

        $user->assignRole('patient');

        // $user->sendEmailVerificationNotification();

        Flash::success(__('messages.flash.your_reg_success'));

        return redirect()->route('login');
    }
}
