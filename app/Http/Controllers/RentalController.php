<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RentalController extends Controller
{
    public function index()
    {
        $rentals = Rental::where('user_id', Auth::id())
            ->with('car')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $activeRentals = $rentals->where('status', 'active');
        $pastRentals = $rentals->whereIn('status', ['completed', 'cancelled', 'overdue']);
        
        return view('rentals.index', compact('rentals', 'activeRentals', 'pastRentals'));
    }

    public function create(Car $car)
    {
        // Check if car is available
        if ($car->status !== 'available') {
            return redirect()->route('dashboard')
                ->with('error', 'This car is not available for rent.');
        }
        
        return view('rentals.create', compact('car'));
    }

    public function store(Request $request, Car $car)
    {
        // Validate the request
        $validated = $request->validate([
            'pickup_datetime' => 'required|date|after:now',
            'duration_hours' => 'required|integer|min:1|max:720',
            'pickup_location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        // Explicitly convert duration_hours to integer
        $durationHours = (int) $validated['duration_hours'];
        
        // Parse the datetime
        $pickupDatetime = Carbon::parse($validated['pickup_datetime']);
        
        // Add hours using integer value
        $returnDatetime = $pickupDatetime->copy()->addHours($durationHours);
        
        // Check if car is available
        if ($car->status !== 'available') {
            return back()->with('error', 'This car is not available for rent.');
        }
        
        // Calculate total price (price_per_day * number of days)
        $days = $durationHours / 24;
        $totalPrice = $car->price_per_day * $days;
        
        // Create the rental
        $rental = Rental::create([
            'user_id' => Auth::id(),
            'car_id' => $car->id,
            'pickup_datetime' => $pickupDatetime,
            'return_datetime' => $returnDatetime,
            'duration_hours' => $durationHours,
            'total_price' => $totalPrice,
            'status' => 'active',
            'pickup_location' => $validated['pickup_location'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);
        
        // Update car status to rented
        $car->update(['status' => 'rented']);
        
        return redirect()->route('rentals.show', $rental)
            ->with('success', 'Car rented successfully! Your rental has been confirmed.');
    }

    public function show(Rental $rental)
    {
        // Ensure user owns this rental or is admin
        if (Auth::id() !== $rental->user_id && !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        return view('rentals.show', compact('rental'));
    }

    public function return(Rental $rental)
    {
        if (Auth::id() !== $rental->user_id && !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $rental->update(['status' => 'completed']);
        
        // Make car available again
        $rental->car->update(['status' => 'available']);
        
        return redirect()->route('rentals.show', $rental)
            ->with('success', 'Car returned successfully! Thank you for renting with fastLANE!');
    }

    public function cancel(Rental $rental)
    {
        if (Auth::id() !== $rental->user_id && !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        if ($rental->status === 'active') {
            $rental->update(['status' => 'cancelled']);
            $rental->car->update(['status' => 'available']);
            
            return redirect()->route('rentals.index')
                ->with('success', 'Rental cancelled successfully.');
        }
        
        return back()->with('error', 'Cannot cancel this rental.');
    }

    public function adminIndex()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $rentals = Rental::with(['user', 'car'])->orderBy('created_at', 'desc')->get();
        $activeRentals = $rentals->where('status', 'active');
        $overdueRentals = $rentals->where('status', 'overdue');
        
        return view('rentals.admin', compact('rentals', 'activeRentals', 'overdueRentals'));
    }
}