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

// Medicines API for consultation form
Route::middleware(['web', 'auth'])->get('/medicines', function () {
    return Medicine::select('id', 'name', 'available_quantity', 'salt_composition')
        ->where('available_quantity', '>', 0)
        ->orderBy('name', 'asc')
        ->get();
});
