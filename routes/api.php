<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Medicine;

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
Route::middleware(['web', 'auth'])->get('/medicines', function () {
    $medicines = Medicine::with(['category', 'brand'])
        ->select('id', 'name', 'available_quantity', 'salt_composition', 'category_id', 'brand_id')
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
            'category_name' => $medicine->category ? $medicine->category->name : 'Uncategorized',
            'brand_name' => $medicine->brand ? $medicine->brand->name : 'N/A',
            'salt_composition' => $medicine->salt_composition,
            'available_quantity' => $medicine->available_quantity,
            'dosages' => $dosages
        ];
    });

    // Group by category
    $groupedByCategory = $medicinesWithDosages->groupBy('category_name');

    return response()->json($groupedByCategory);
});
