<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Medicine;
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

// Medicines API for consultation form - grouped by category with dosage
// Staff / doctor / admin only (it is only used by the consultation form). Patients cannot see stock.
Route::middleware(['web', 'auth', 'checkUserStatus', 'role:clinic_admin|doctor|staff|nurse'])->get('/medicines', function () {
    $medicines = Medicine::with(['medicineCategory', 'generic'])
        ->select('id', 'name', 'available_quantity', 'salt_composition', 'category_id', 'generic_id')
        ->where('available_quantity', '>', 0)
        ->orderBy('category_id', 'asc')
        ->orderBy('name', 'asc')
        ->get();

    // Get dosages for each medicine
    $medicinesWithDosages = $medicines->map(function ($medicine) {
        $dosages = \App\Models\PurchasedMedicine::where('medicine_id', $medicine->id)
            ->where('quantity', '>', 0)
            ->select('dosage', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as available_quantity'))
            ->groupBy('dosage')
            ->orderBy('dosage')
            ->get()
            ->map(function ($item) {
                return [
                    'dosage' => $item->dosage ?? 'N/A',
                    'quantity' => $item->available_quantity
                ];
            });

        return [
            'id' => $medicine->id,
            'name' => $medicine->name,
            'category_id' => $medicine->category_id,
            'category_name' => $medicine->category ?: $medicine->category_name ?: optional($medicine->medicineCategory)->name ?: 'Uncategorized',
            'generic_name' => $medicine->generic ? $medicine->generic->name : 'N/A',
            'salt_composition' => $medicine->salt_composition,
            'available_quantity' => $medicine->available_quantity,
            'dosages' => $dosages
        ];
    });

    // Group by category
    $groupedByCategory = $medicinesWithDosages->groupBy('category_name');

    return response()->json($groupedByCategory);
});

// Patient Queue Related API Routes
// These live in the stateless "api" group, where `auth:web` never sees the login session, so the
// queue-creation preview always got "Unauthenticated". Run them through the session ("web")
// middleware with the same role / staff-module checks as the queue pages. M-06.
Route::middleware(['web', 'auth', 'checkUserStatus', 'role:clinic_admin|doctor|staff|nurse', 'staff.module:queue,consultations'])->group(function () {
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
