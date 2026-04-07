<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------
// GUEST-ONLY ROUTES — redirect to dashboard if already logged in
// ---------------------------------------------------------------

Route::middleware('guest')->group(function () {

    Route::get('register', [RegisteredUserController::class, 'create'])
         ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
         ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

});


// ---------------------------------------------------------------
// AUTHENTICATED-ONLY ROUTES
// ---------------------------------------------------------------

Route::middleware('auth')->group(function () {

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
         ->name('logout');

});