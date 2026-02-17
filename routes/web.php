<?php

use App\Http\Controllers\ParkingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes (Driver Portal)
|--------------------------------------------------------------------------
*/

// Main Landing Page for Drivers
Route::get('/', [ParkingController::class, 'publicView'])->name('parking.public');

// Search for a vehicle by plate
Route::post('/search-vehicle', [ParkingController::class, 'search'])->name('vehicles.search');

// Initiate M-Pesa Payment
Route::post('/pay/{id}', [ParkingController::class, 'pay'])->name('vehicles.pay');

// Self-Checkout / Exit
Route::post('/exit/{id}', [ParkingController::class, 'exit'])->name('vehicles.exit');

// Digital Receipt (Opens in a new tab for printing)
Route::get('/receipt/{id}', [ParkingController::class, 'downloadReceipt'])->name('vehicles.receipt');


/*
|--------------------------------------------------------------------------
| Admin Routes (Dashboard)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {
    // Admin Dashboard (Stats & List)
    Route::get('/dashboard', [ParkingController::class, 'index'])->name('admin.dashboard');

    // Check-in new vehicle
    Route::post('/store', [ParkingController::class, 'store'])->name('vehicles.store');

    // Manual Cash Payment Override
    Route::post('/manual-pay/{id}', [ParkingController::class, 'manualPay'])->name('vehicles.manual-pay');

    // Force Remove/Cancel entry
    Route::post('/force-delete/{id}', [ParkingController::class, 'forceDelete'])->name('vehicles.force-delete');
});


/*
|--------------------------------------------------------------------------
| System Routes (M-Pesa Callbacks)
|--------------------------------------------------------------------------
*/

// M-Pesa Callback URL (Exclude this from CSRF in VerifyCsrfToken.php)
Route::post('/mpesa/callback', [ParkingController::class, 'callback'])->name('mpesa.callback');
