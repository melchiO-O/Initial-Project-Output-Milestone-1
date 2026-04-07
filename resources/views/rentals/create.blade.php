{{-- resources/views/rentals/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Rent Car')

@section('content')
<div class="page-header">
    <h1>Rent a Car</h1>
    <p>{{ $car->brand }} {{ $car->model }} - {{ $car->plate_number }}</p>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('rentals.store', $car) }}">
        @csrf
        
        <div class="car-summary">
            <div class="car-summary-item">
                <span class="label">Price per day:</span>
                <span class="value">₱{{ number_format($car->price_per_day, 2) }}</span>
            </div>
            <div class="car-summary-item">
                <span class="label">Seat capacity:</span>
                <span class="value">{{ $car->seat_capacity }} seats</span>
            </div>
            <div class="car-summary-item">
                <span class="label">Year:</span>
                <span class="value">{{ $car->year }}</span>
            </div>
        </div>
        
        <div class="form-group">
            <label for="pickup_datetime">Pickup Date & Time *</label>
            <input type="datetime-local" id="pickup_datetime" name="pickup_datetime" 
                   value="{{ old('pickup_datetime') }}" min="{{ now()->format('Y-m-d\TH:i') }}" required>
            @error('pickup_datetime')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        
        <div class="form-group">
            <label for="duration_hours">Duration (hours) *</label>
            <select id="duration_hours" name="duration_hours" required onchange="updateTotalPrice()">
                <option value="">Select duration</option>
                <option value="3">3 hours (minimum)</option>
                <option value="6">6 hours</option>
                <option value="12">12 hours</option>
                <option value="24">1 day</option>
                <option value="48">2 days</option>
                <option value="72">3 days</option>
                <option value="96">4 days</option>
                <option value="120">5 days</option>
                <option value="168">1 week</option>
            </select>
            @error('duration_hours')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        
        <div class="form-group">
            <label for="pickup_location">Pickup Location (optional)</label>
            <input type="text" id="pickup_location" name="pickup_location" 
                   value="{{ old('pickup_location') }}" placeholder="e.g., Main Office, Airport, etc.">
        </div>
        
        <div class="form-group">
            <label for="notes">Special Requests / Notes (optional)</label>
            <textarea id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
        </div>
        
        <div class="price-summary" id="priceSummary" style="display: none;">
            <h3>Rental Summary</h3>
            <div class="price-details">
                <div class="price-row">
                    <span>Daily Rate:</span>
                    <span>₱{{ number_format($car->price_per_day, 2) }}</span>
                </div>
                <div class="price-row">
                    <span>Duration:</span>
                    <span><span id="durationDisplay">0</span> hours (<span id="daysDisplay">0</span> days)</span>
                </div>
                <div class="price-row total">
                    <span>Total Price:</span>
                    <span>₱<span id="totalPrice">0.00</span></span>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Confirm Rental</button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
function updateTotalPrice() {
    const durationSelect = document.getElementById('duration_hours');
    const duration = parseInt(durationSelect.value);
    const pricePerDay = {{ $car->price_per_day }};
    
    if (duration && !isNaN(duration)) {
        const days = duration / 24;
        const total = pricePerDay * days;
        
        document.getElementById('durationDisplay').textContent = duration;
        document.getElementById('daysDisplay').textContent = days.toFixed(2);
        document.getElementById('totalPrice').textContent = total.toFixed(2);
        document.getElementById('priceSummary').style.display = 'block';
    } else {
        document.getElementById('priceSummary').style.display = 'none';
    }
}
</script>
@endsection