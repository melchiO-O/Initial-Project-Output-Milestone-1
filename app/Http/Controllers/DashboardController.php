<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Rental;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCars = Car::count();
        $availableCars = Car::where('status', 'available')->count();
        $rentedCars = Car::where('status', 'rented')->count();
        $totalUsers = \App\Models\User::count();
        
        // Get cars for display
        $cars = Car::latest()->get();
        
        // For admin, show all cars; for regular users, show available cars
        if (Auth::user()->isAdmin()) {
            $cars = Car::latest()->get();
        } else {
            $cars = Car::where('status', 'available')->latest()->get();
        }
        
        return view('dashboard.index', compact('totalCars', 'availableCars', 'rentedCars', 'totalUsers', 'cars'));
    }
}