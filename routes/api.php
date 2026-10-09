<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Patient;
use App\Models\RequestDocuments;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Patient Queue Related API Routes
// These live in the stateless "api" group, where `auth:web` never sees the login session, so the
// queue-creation preview always got "Unauthenticated". Run them through the session ("web")
// middleware with the same role / staff-module checks as the queue pages. M-06.
Route::middleware(['web', 'auth', 'checkUserStatus', 'role:clinic_admin|doctor|staff', 'permission:manage_patients|manage_request_documents'])->group(function () {
    // Get latest consultation form for a patient (for queue management)
    Route::get('/patient/{patient}/latest-consultation', function (Patient $patient) {
        try {
            $latestConsultation = RequestDocuments::where('user_id', $patient->user_id)
                ->where('document_type', 'consultation_form')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latestConsultation) {
                return response()->json([
                    'success' => true,
                    'consultation' => [
                        'id' => $latestConsultation->id,
                        'created_at' => $latestConsultation->created_at,
                        'complaints' => $latestConsultation->complaints,
                        'assessment' => $latestConsultation->assessment,
                        'has_images' => !empty($latestConsultation->consultation_images)
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No consultation forms found for this patient.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving consultation form information.'
            ], 500);
        }
    });
});
