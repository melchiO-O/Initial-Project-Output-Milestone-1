{{-- resources/views/rentals/index.blade.php --}}
@extends('layouts.app')
@section('title', 'My Rentals')

@section('content')
<div class="page-header">
    <h1>My Rentals</h1>
    <p>Track your active and past rentals</p>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($activeRentals->count() > 0)
    <div class="section-header">
        <h2>Active Rentals</h2>
    </div>
    
    <div class="rentals-grid">
        @foreach($activeRentals as $rental)
            <div class="rental-card active">
                <div class="rental-header">
                    <h3>{{ $rental->car->brand }} {{ $rental->car->model }}</h3>
                    <span class="status-badge status-{{ $rental->status }}">
                        {{ ucfirst($rental->status) }}
                    </span>
                </div>
                
                <div class="rental-info">
                    <div class="info-row">
                        <span class="label">Plate Number:</span>
                        <span class="value">{{ $rental->car->plate_number }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Pickup:</span>
                        <span class="value">{{ $rental->pickup_datetime->format('M d, Y h:i A') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Return:</span>
                        <span class="value">{{ $rental->return_datetime->format('M d, Y h:i A') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Total Price:</span>
                        <span class="value">₱{{ number_format($rental->total_price, 2) }}</span>
                    </div>
                </div>
                
                {{-- Countdown Timer --}}
                <div class="countdown-timer" data-return-time="{{ $rental->return_datetime }}">
                    <div class="timer-label">Time Remaining:</div>
                    <div class="timer-display" id="timer-{{ $rental->id }}">
                        Calculating...
                    </div>
                </div>
                
                {{-- Progress Bar --}}
                <div class="progress-section">
                    <div class="progress-label">Rental Progress</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $rental->progress_percentage }}%"></div>
                    </div>
                    <div class="progress-text">{{ $rental->progress_percentage }}% completed</div>
                </div>
                
                <div class="rental-actions">
                    <a href="{{ route('rentals.show', $rental) }}" class="btn btn-secondary">View Details</a>
                    @if($rental->status === 'active')
                        <form action="{{ route('rentals.return', $rental) }}" method="POST" class="inline-form">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success" onclick="return confirm('Confirm return?')">
                                Return Car
                            </button>
                        </form>
                        <form action="{{ route('rentals.cancel', $rental) }}" method="POST" class="inline-form">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this rental?')">
                                Cancel
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

@if($pastRentals->count() > 0)
    <div class="section-header">
        <h2>Past Rentals</h2>
    </div>
    
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Car</th>
                    <th>Pickup Date</th>
                    <th>Return Date</th>
                    <th>Duration</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pastRentals as $rental)
                    <tr>
                        <td>{{ $rental->car->brand }} {{ $rental->car->model }}</td>
                        <td>{{ $rental->pickup_datetime->format('M d, Y') }}</td>
                        <td>{{ $rental->return_datetime->format('M d, Y') }}</td>
                        <td>{{ $rental->duration_hours }} hours</td>
                        <td>₱{{ number_format($rental->total_price, 2) }}</td>
                        <td>
                            <span class="status-badge status-{{ $rental->status }}">
                                {{ ucfirst($rental->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('rentals.show', $rental) }}" class="btn btn-sm btn-secondary">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if($rentals->count() === 0)
    <div class="empty-state">
        <div class="icon">🚗</div>
        <p>You haven't rented any cars yet.</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">Browse Cars</a>
    </div>
@endif

@endsection

@push('scripts')
<script>
// Auto-updating countdown timers
function updateAllTimers() {
    document.querySelectorAll('.countdown-timer').forEach(container => {
        const returnTime = container.dataset.returnTime;
        const timerDisplay = container.querySelector('.timer-display');
        
        if (timerDisplay) {
            const now = new Date();
            const returnDate = new Date(returnTime);
            const diff = returnDate - now;
            
            if (diff <= 0) {
                timerDisplay.innerHTML = '<span class="overdue">Overdue - Please return car</span>';
                timerDisplay.style.color = '#dc3545';
                timerDisplay.style.fontWeight = 'bold';
            } else {
                const hours = Math.floor(diff / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                
                if (hours > 24) {
                    const days = Math.floor(hours / 24);
                    const remainingHours = hours % 24;
                    timerDisplay.innerHTML = `${days}d ${remainingHours}h ${minutes}m remaining`;
                } else {
                    timerDisplay.innerHTML = `${hours}h ${minutes}m ${seconds}s remaining`;
                }
            }
        }
    });
}

// Update timers every second
setInterval(updateAllTimers, 1000);
updateAllTimers();
</script>
@endpush