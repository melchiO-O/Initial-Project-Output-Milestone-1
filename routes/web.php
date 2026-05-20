<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------
// PUBLIC ROUTES
// ---------------------------------------------------------------
Route::get('/', function () {
    if (auth()->check()) return redirect()->route('dashboard');
    return app(HomeController::class)->index();
})->name('home');

Route::get('/cars', [CarController::class, 'index'])->name('cars.index');

// ---------------------------------------------------------------
// AUTHENTICATED ROUTES
// ---------------------------------------------------------------
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');

    // Rentals — user
    Route::get('/rentals',                   [RentalController::class, 'index'])->name('rentals.index');
    Route::get('/rentals/create/{car}',      [RentalController::class, 'create'])->name('rentals.create');
    Route::post('/rentals/{car}',            [RentalController::class, 'store'])->name('rentals.store');
    Route::get('/rentals/{rental}',          [RentalController::class, 'show'])->name('rentals.show');

    // -------------------------------------------------------
    // ADMIN-ONLY ROUTES
    // -------------------------------------------------------
    Route::middleware(['admin'])->group(function () {

        // Car management
        Route::get('/cars/create',        [CarController::class, 'create'])->name('cars.create');
        Route::post('/cars',              [CarController::class, 'store'])->name('cars.store');
        Route::get('/cars/{car}/edit',    [CarController::class, 'edit'])->name('cars.edit');
        Route::put('/cars/{car}',         [CarController::class, 'update'])->name('cars.update');
        Route::delete('/cars/{car}',      [CarController::class, 'destroy'])->name('cars.destroy');

        // Rental management (admin only)
        Route::get('/admin/rentals',                    [RentalController::class, 'adminIndex'])->name('admin.rentals');
        Route::put('/rentals/{rental}/activate',        [RentalController::class, 'activate'])->name('rentals.activate');
        Route::put('/rentals/{rental}/return',          [RentalController::class, 'return'])->name('rentals.return');
        Route::put('/rentals/{rental}/cancel',          [RentalController::class, 'cancel'])->name('rentals.cancel');
    });
});

// ---------------------------------------------------------------
// BREEZE AUTH ROUTES
// ---------------------------------------------------------------
require __DIR__ . '/auth.php';