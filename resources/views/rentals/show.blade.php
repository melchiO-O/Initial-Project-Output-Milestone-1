{{-- resources/views/rentals/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Rental Details')

@section('content')
<div class="page-header">
    <h1>Rental Details</h1>
    <p>Rental #{{ $rental->id }}</p>
</div>

<div class="rental-details">
    <div class="details-grid">
        <div class="details-card">
            <h3>Car Information</h3>
            <div class="car-info">
                @if($rental->car->image_path)
                    <img src="{{ asset('storage/' . $rental->car->image_path) }}" 
                         alt="{{ $rental->car->brand }}" class="car-image">
                @elseif($rental->car->image_url)
                    <img src="{{ $rental->car->image_url }}" 
                         alt="{{ $rental->car->brand }}" class="car-image">
                @endif
                <h4>{{ $rental->car->brand }} {{ $rental->car->model }}</h4>
                <p>Plate: {{ $rental->car->plate_number }}</p>
                <p>Year: {{ $rental->car->year }}</p>
                <p>Seats: {{ $rental->car->seat_capacity }}</p>
            </div>
        </div>
        
        <div class="details-card">
            <h3>Rental Information</h3>
            <div class="info-list">
                <div class="info-item">
                    <span class="label">Status:</span>
                    <span class="status-badge status-{{ $rental->status }}">
                        {{ ucfirst($rental->status) }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="label">Pickup Date:</span>
                    <span>{{ $rental->pickup_datetime->format('F d, Y h:i A') }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Return Date:</span>
                    <span>{{ $rental->return_datetime->format('F d, Y h:i A') }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Duration:</span>
                    <span>{{ $rental->duration_hours }} hours ({{ number_format($rental->duration_hours / 24, 2) }} days)</span>
                </div>
                <div class="info-item">
                    <span class="label">Total Price:</span>
                    <span class="total-price">₱{{ number_format($rental->total_price, 2) }}</span>
                </div>
                @if($rental->pickup_location)
                <div class="info-item">
                    <span class="label">Pickup Location:</span>
                    <span>{{ $rental->pickup_location }}</span>
                </div>
                @endif
                @if($rental->notes)
                <div class="info-item">
                    <span class="label">Notes:</span>
                    <span>{{ $rental->notes }}</span>
                </div>
                @endif
            </div>
        </div>
        
        @if($rental->status === 'active')
        <div class="details-card countdown-card">
            <h3>Time Remaining</h3>
            <div class="big-countdown" data-return-time="{{ $rental->return_datetime }}">
                <div class="countdown-display" id="countdownDisplay">
                    Calculating...
                </div>
            </div>
            <div class="progress-section">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $rental->progress_percentage }}%"></div>
                </div>
                <p>{{ $rental->progress_percentage }}% of rental period completed</p>
            </div>
        </div>
        @endif
    </div>
    
    <div class="action-buttons">
        @if($rental->status === 'active')
            <form action="{{ route('rentals.return', $rental) }}" method="POST" style="display: inline;">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-success btn-large" onclick="return confirm('Confirm car return?')">
                    ✓ Return Car Now
                </button>
            </form>
            <form action="{{ route('rentals.cancel', $rental) }}" method="POST" style="display: inline;">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-danger btn-large" onclick="return confirm('Cancel this rental?')">
                    ✗ Cancel Rental
                </button>
            </form>
        @endif
        <a href="{{ route('rentals.index') }}" class="btn btn-secondary">Back to My Rentals</a>
    </div>
</div>

@if($rental->status === 'active')
<script>
function updateCountdown() {
    const container = document.querySelector('.big-countdown');
    if (!container) return;
    
    const returnTime = container.dataset.returnTime;
    const display = document.getElementById('countdownDisplay');
    const now = new Date();
    const returnDate = new Date(returnTime);
    const diff = returnDate - now;
    
    if (diff <= 0) {
        display.innerHTML = '<span style="color: #dc3545; font-size: 2rem;">⚠️ OVERDUE - Please return car immediately ⚠️</span>';
    } else {
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        let displayText = '';
        if (days > 0) displayText += `${days}d `;
        displayText += `${hours}h ${minutes}m ${seconds}s`;
        display.innerHTML = displayText;
    }
}

setInterval(updateCountdown, 1000);
updateCountdown();
</script>
@endif
@endsection