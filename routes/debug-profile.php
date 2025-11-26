<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient;

Route::get('/debug-profile-data', function () {
    $user = Auth::user();

    $patient = Patient::with(['user', 'address'])->where('user_id', $user->id)->first();

    return response()->json([
        'logged_in_user' => [
            'id' => $user->id,
            'email' => $user->email,
            'department_id' => $user->department_id,
            'office_id' => $user->office_id,
            'year_level_id' => $user->year_level_id,
            'college_id' => $user->college_id,
        ],
        'patient' => [
            'id' => $patient->id ?? null,
            'user_id' => $patient->user_id ?? null,
        ],
        'patient_user_relationship' => [
            'exists' => !empty($patient->user),
            'department_id' => $patient->user->department_id ?? 'NULL',
            'office_id' => $patient->user->office_id ?? 'NULL',
            'year_level_id' => $patient->user->year_level_id ?? 'NULL',
        ],
        'patient_address' => [
            'exists' => !empty($patient->address),
            'country_id' => $patient->address->country_id ?? 'NULL',
            'state_id' => $patient->address->state_id ?? 'NULL',
            'city_id' => $patient->address->city_id ?? 'NULL',
        ]
    ]);
})->middleware('auth');
