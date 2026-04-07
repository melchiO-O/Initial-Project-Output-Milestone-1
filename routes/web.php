<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CarController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RentalController;

// ---------------------------------------------------------------
// PUBLIC ROUTES — no login required
// ---------------------------------------------------------------

// CHANGE THIS PART ONLY:
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return app(HomeController::class)->index();
})->name('home');

// Anyone can browse the car list
Route::get('/cars', [CarController::class, 'index'])->name('cars.index');


// ---------------------------------------------------------------
// AUTHENTICATED ROUTES — must be logged in
// ---------------------------------------------------------------

Route::middleware(['auth'])->group(function () {

    // Dashboard (all logged-in users)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // -------------------------------------------------------
    // RENTAL ROUTES — available to all authenticated users
    // -------------------------------------------------------
    
    // View rentals (my rentals)
    Route::get('/rentals', [RentalController::class, 'index'])->name('rentals.index');
    
    // Create rental (rent a specific car)
    Route::get('/rentals/create/{car}', [RentalController::class, 'create'])->name('rentals.create');
    Route::post('/rentals/{car}', [RentalController::class, 'store'])->name('rentals.store');
    
    // View specific rental details
    Route::get('/rentals/{rental}', [RentalController::class, 'show'])->name('rentals.show');
    
    // Return or cancel rental
    Route::put('/rentals/{rental}/return', [RentalController::class, 'return'])->name('rentals.return');
    Route::put('/rentals/{rental}/cancel', [RentalController::class, 'cancel'])->name('rentals.cancel');

    // -------------------------------------------------------
    // ADMIN-ONLY ROUTES — must be logged in AND be admin
    // -------------------------------------------------------
    Route::middleware(['admin'])->group(function () {

        // Car management routes
        Route::get('/cars/create',        [CarController::class, 'create'])->name('cars.create');
        Route::post('/cars',              [CarController::class, 'store'])->name('cars.store');
        Route::get('/cars/{car}/edit',    [CarController::class, 'edit'])->name('cars.edit');
        Route::put('/cars/{car}',         [CarController::class, 'update'])->name('cars.update');
        Route::delete('/cars/{car}',      [CarController::class, 'destroy'])->name('cars.destroy');
        
        // Admin rental management (view all rentals)
        Route::get('/admin/rentals', [RentalController::class, 'adminIndex'])->name('admin.rentals');

    });
    

});


// ---------------------------------------------------------------
// BREEZE AUTH ROUTES — login, register, logout
// ---------------------------------------------------------------

require __DIR__ . '/auth.php';

// TEMPORARY DEBUG ROUTE — remove after fixing images
Route::get('/debug-images', function () {
    $cars = \App\Models\Car::all();
    return view('cars.debug', compact('cars'));
})->middleware('auth');