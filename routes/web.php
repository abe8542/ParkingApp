<?php

use App\Http\Controllers\ParkingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// MOVE THIS HERE - TOP PRIORITY
Route::get('/admin/dashboard', [ParkingController::class, 'index'])->name('admin.dashboard.index');

/*
|--------------------------------------------------------------------------
| Public Routes (Driver Portal)
|--------------------------------------------------------------------------
| These routes are accessible to everyone, even without logging in.
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
| Protected by 'auth' middleware. If a user is not logged in,
| they will be redirected to the /login page automatically.
*/
Route::prefix('admin')->group(function () {

    // 1. Admin Dashboard - List all vehicles
    Route::get('/dashboard', [ParkingController::class, 'index'])->name('dashboard');

    // 2. Add a new vehicle manually
    Route::post('/store', [ParkingController::class, 'store'])->name('vehicles.store');

    // 3. Manually mark a vehicle as paid
    Route::post('/manual-pay/{id}', [ParkingController::class, 'manualPay'])->name('vehicles.manual-pay');

    // 4. Force delete/remove a vehicle record
    Route::delete('/force-delete/{id}', [ParkingController::class, 'forceDelete'])->name('vehicles.force-delete');
});

/*
|--------------------------------------------------------------------------
| System Routes (M-Pesa Callbacks)
|--------------------------------------------------------------------------
*/

// M-Pesa Callback URL (Exclude this from CSRF in VerifyCsrfToken.php)
Route::post('/mpesa/callback', [ParkingController::class, 'callback'])->name('mpesa.callback');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
| Includes Login, Register, Forgot Password, etc.
*/
require __DIR__.'/auth.php';


use App\Http\Controllers\ProfileController;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
