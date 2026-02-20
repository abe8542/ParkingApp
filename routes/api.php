<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParkingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// M-Pesa Callback
Route::post('/mpesa/callback', [ParkingController::class, 'callback']);

// Test route to verify the API is fixed
Route::get('/test', function () {
    return response()->json(['message' => 'API is now working!']);
});
use App\Http\Controllers\MpesaController;

Route::post('/v1/stkpush', [MpesaController::class, 'stkPush']);
Route::post('/mpesa/callback', [MpesaController::class, 'handleCallback']);
