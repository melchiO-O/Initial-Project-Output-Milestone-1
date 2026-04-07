<?php
// app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        // Redirect authenticated users to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        // For guests - show landing page
        $totalCars = Car::count();
        $availableCars = Car::where('status', 'available')->count();
        $brands = Car::distinct('brand')->count('brand');
        $featuredCars = Car::where('status', 'available')->latest()->take(3)->get();
        
        return view('home', compact('totalCars', 'availableCars', 'brands', 'featuredCars'));
    }
}