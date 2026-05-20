<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RentalController extends Controller
{
    // ── USER: View own rentals ─────────────────────────────────
    public function index()
    {
        $rentals = Rental::where('user_id', Auth::id())
            ->with('car', 'payment')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeRentals = $rentals->whereIn('status', ['pending', 'active']);
        $pastRentals   = $rentals->whereIn('status', ['returned', 'completed', 'cancelled']);

        return view('rentals.index', compact('rentals', 'activeRentals', 'pastRentals'));
    }

    // ── USER: Show rental booking form ────────────────────────
    public function create(Car $car)
    {
        $user = Auth::user();

        // Must have a driver's license on file
        if (!$user->hasLicense()) {
            return redirect()->route('profile.edit')
                ->with('error', 'You must add your driver\'s license before renting a car.');
        }

        // 1:1 — one active/pending rental per user at a time
        if ($user->rental) {
            return redirect()->route('rentals.index')
                ->with('error', 'You already have an active rental. It must be returned or cancelled first.');
        }

        if ($car->status !== 'available') {
            return redirect()->route('dashboard')
                ->with('error', 'This car is not available for rent.');
        }

        return view('rentals.create', compact('car', 'user'));
    }

    // ── USER: Store new rental ─────────────────────────────────
    public function store(Request $request, Car $car)
    {
        $user = Auth::user();

        if (!$user->hasLicense()) {
            return redirect()->route('profile.edit')
                ->with('error', 'You must add your driver\'s license before renting a car.');
        }

        if ($user->rental) {
            return redirect()->route('rentals.index')
                ->with('error', 'You already have an active rental.');
        }

        if ($car->status !== 'available') {
            return back()->with('error', 'This car is not available for rent.');
        }

        if ($request->has('pickup_date') && $request->has('pickup_time') && !$request->has('pickup_datetime')) {
            $request->merge([
                'pickup_datetime' => $request->pickup_date . 'T' . $request->pickup_time . ':00'
            ]);
        }

        $validated = $request->validate([
            'pickup_datetime' => 'required|date|after:now',
            'duration_hours'  => 'required|integer|min:3|max:720',
            'pickup_location' => 'nullable|string|max:255',
            'notes'           => 'nullable|string|max:500',
        ]);

        $durationHours  = (int) $validated['duration_hours'];
        $pickupDatetime = Carbon::parse($validated['pickup_datetime']);
        $returnDatetime = $pickupDatetime->copy()->addHours($durationHours);

        // Calculate total based on actual hours (hourly rate)
        $hourlyRate = $car->price_per_day / 24;
        $total = round($durationHours * $hourlyRate, 2);
        $down = round($total * 0.5, 2);
        $balance = round($total - $down, 2);

        // Create rental WITHOUT payment fields
        $rental = Rental::create([
            'user_id'           => $user->id,
            'car_id'            => $car->id,
            'license_number'    => $user->license_number,
            'license_expiry'    => $user->license_expiry,
            'pickup_datetime'   => $pickupDatetime,
            'return_datetime'   => $returnDatetime,
            'duration_hours'    => $durationHours,
            'price_per_day'     => $car->price_per_day,
            'total_days'        => 0,
            'total_price'       => $total,
            'status'            => 'pending',
            'pickup_location'   => $validated['pickup_location'] ?? null,
            'notes'             => $validated['notes'] ?? null,
        ]);

        // Create associated payment record
        $rental->payment()->create([
            'user_id' => $user->id,
            'down_payment' => $down,
            'remaining_balance' => $balance,
            'damage_fee' => 0,
            'payment_status' => 'pending',
        ]);

        $car->update(['status' => 'rented']);

        return redirect()->route('rentals.index')
            ->with('success', 'Rental booked! Please pay the down payment of ₱' . number_format($down, 2) . ' upon pickup.');
    }
    
    // ── USER/ADMIN: View single rental ─────────────────────────
    public function show(Rental $rental)
    {
        if (Auth::id() !== $rental->user_id && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $rental->load('payment');
        return view('rentals.show', compact('rental'));
    }

    // ── ADMIN ONLY: Activate rental (pickup confirmed, down payment received) ──
    public function activate(Rental $rental)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $rental->update(['status' => 'active']);
        
        // Update payment record
        if ($rental->payment) {
            $rental->payment->update([
                'down_payment_paid_at' => now(),
                'payment_status' => $rental->payment->remaining_balance > 0 ? 'partial' : 'completed',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Rental activated. Down payment of ₱' . number_format($rental->payment->down_payment, 2) . ' confirmed.');
    }

    // ── ADMIN ONLY: Return car with optional damage fee ──
    public function return(Request $request, Rental $rental)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'damage_fee'   => 'nullable|numeric|min:0',
            'damage_notes' => 'nullable|string|max:500',
        ]);

        // In RentalController@return

        $actualReturnDatetime = now();
        $damageFee = (float) ($validated['damage_fee'] ?? 0);

        $returnTime = Carbon::parse($rental->return_datetime);
        $pickup = Carbon::parse($rental->pickup_datetime);
        $originalHours = $rental->duration_hours;
        $actualHours = ceil($pickup->diffInHours($actualReturnDatetime, true));

        // Overdue fee
        $overdueFee = 0;
        $hoursOverdue = 0;
        if ($actualReturnDatetime->gt($returnTime)) {
            $hoursOverdue = ceil($returnTime->diffInHours($actualReturnDatetime, true));
            $overdueFee = $hoursOverdue * 200;
        }

        $originalDownPayment = $rental->payment->down_payment;
        $originalRemainingBalance = $rental->payment->remaining_balance; // ORIGINAL, don't touch this

        // Early return adjustment
        if ($actualHours < $originalHours) {
            $remainingPerHour = $originalRemainingBalance / $originalHours;
            $adjustedRemaining = round($remainingPerHour * $actualHours, 2);
            $adjustedRemaining = max(0, $adjustedRemaining);
        } else {
            // On-time or overdue: pay full original remaining balance
            $adjustedRemaining = $originalRemainingBalance;
        }

        $finalAmount = round($adjustedRemaining + $damageFee + $overdueFee, 2);

        $rental->payment->update([
            // DO NOT overwrite remaining_balance — it should stay as originally booked
            'damage_fee'           => $damageFee,
            'damage_notes'         => $validated['damage_notes'] ?? null,
            'overdue_fee'          => $overdueFee,
            'final_amount_paid'    => $finalAmount,
            'full_payment_paid_at' => now(),
            'payment_status'       => 'completed',
            'payment_method'       => 'cash',
        ]);

        // Update rental
        $rental->update([
            'status'                 => 'returned',
            'actual_return_datetime' => $actualReturnDatetime,
        ]);

        $rental->car->update(['status' => 'available']);

        $message = "Car returned. Actual hours: {$actualHours}h. ";
        if ($hoursOverdue > 0) {
            $message .= "Overdue by {$hoursOverdue}hrs (₱" . number_format($overdueFee, 2) . "). ";
        }
        $message .= "Final payment: ₱" . number_format($finalAmount, 2);

        return redirect()->route('admin.rentals')->with('success', $message);
    }

    // ── ADMIN ONLY: Cancel rental ──────────────────────────────
    public function cancel(Rental $rental)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $rental->update(['status' => 'cancelled']);
        
        if ($rental->payment) {
            $rental->payment->update(['payment_status' => 'refunded']);
        }
        
        $rental->car->update(['status' => 'available']);

        return redirect()->route('admin.rentals')
            ->with('success', 'Rental cancelled and car is now available.');
    }

    // ── ADMIN: View all rentals ────────────────────────────────
    public function adminIndex()
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $rentals        = Rental::with(['user', 'car', 'payment'])->orderBy('created_at', 'desc')->get();
        $activeRentals  = $rentals->whereIn('status', ['pending', 'active']);
        $overdueRentals = $rentals->filter(function ($r) {
            return $r->status === 'active' && now()->gt($r->return_datetime);
        });

        return view('rentals.admin', compact('rentals', 'activeRentals', 'overdueRentals'));
    }
}