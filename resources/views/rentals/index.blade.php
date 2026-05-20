@extends('layouts.app')
@section('title', 'My Rentals')

@section('content')

<div class="page-header">
    <h1>My Rentals</h1>
    <p>Track your active and past rentals.</p>
</div>

@if(!auth()->user()->hasLicense())
    <div class="alert alert-error">
        ⚠️ You have not added your driver's license yet.
        <a href="{{ route('profile.edit') }}" style="color:inherit;font-weight:700;text-decoration:underline;">
            Add it here
        </a>
        before renting a car.
    </div>
@endif

{{-- Active / Pending Rentals --}}
@if($activeRentals->count() > 0)
    <div class="section-header">
        <h2>Active Rentals</h2>
    </div>

    <div class="rentals-grid">
        @foreach($activeRentals as $rental)
            <div class="rental-card">
                <div class="rental-header">
                    <h3>{{ $rental->car->brand }} {{ $rental->car->model }}</h3>
                    <span class="rental-status status-{{ $rental->status }}">
                        {{ ucfirst($rental->status) }}
                    </span>
                </div>

                <div class="rental-info">
                    <div class="info-row">
                        <span class="label">Plate:</span>
                        <span class="value mono">{{ $rental->car->plate_number }}</span>
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
                        <span class="label">Down Payment:</span>
                        <span class="value" style="color:var(--green);font-weight:600;">
                            ₱{{ number_format($rental->payment->down_payment, 2) }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Balance on Return:</span>
                        <span class="value">₱{{ number_format($rental->payment->remaining_balance, 2) }}</span>
                    </div>
                </div>

                {{-- Countdown Timer --}}
                @if($rental->status === 'active')
                    <div class="countdown-timer" data-return-time="{{ $rental->return_datetime }}">
                        <div class="timer-label">Time Remaining:</div>
                        <div class="timer-display" id="timer-{{ $rental->id }}">Calculating...</div>
                    </div>

                    <div class="progress-section">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $rental->progress_percentage }}%"></div>
                        </div>
                        <div class="progress-text">{{ $rental->progress_percentage }}% completed</div>
                    </div>
                @endif

                @if($rental->status === 'pending')
                    <div class="license-info-box" style="margin-top:0.75rem;">
                        <div class="license-label" style="color:orange;">⏳ Awaiting Admin Confirmation</div>
                        <div style="font-size:0.82rem;color:var(--gray-lt);">
                            Please pay the down payment of
                            <strong style="color:var(--yellow);">₱{{ number_format($rental->payment->down_payment, 2) }}</strong>
                            upon pickup. Admin will activate your rental once confirmed.
                        </div>
                    </div>
                @endif

                <div class="rental-actions">
                    <a href="{{ route('rentals.show', $rental) }}" class="btn btn-secondary btn-sm">
                        View Details
                    </a>
                    {{-- Return and Cancel are ADMIN ONLY --}}
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- Past Rentals --}}
@if($pastRentals->count() > 0)
    <div class="section-header" style="margin-top:2rem;">
        <h2>Past Rentals</h2>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Car</th>
                    <th>Pickup</th>
                    <th>Return</th>
                    <th>Duration</th>
                    <th>Price</th>
                    <th>Damage Fee</th>
                    <th>Grand Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pastRentals as $rental)
                <tr>
                    <td><strong>{{ $rental->car->brand }} {{ $rental->car->model }}</strong></td>
                    <td>{{ $rental->pickup_datetime->format('M d, Y') }}</td>
                    <td>{{ $rental->return_datetime->format('M d, Y') }}</td>
                    <td><span>{{ $rental->formatted_duration }}</span></td>
                    <td>₱{{ number_format($rental->price_per_day, 2) }}</td>
                    <td>
                        @if($rental->payment->damage_fee > 0)
                            <span style="color:var(--red);">₱{{ number_format($rental->payment->damage_fee, 2) }}</span>
                        @else
                            <span style="color:var(--gray-lt);">—</span>
                        @endif
                    </td>
                    <td><strong>₱{{ number_format($rental->grand_total, 2) }}</strong></td>
                    <td>
                        <span class="rental-status status-{{ $rental->status }}">
                            {{ ucfirst($rental->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('rentals.show', $rental) }}" class="btn btn-secondary btn-sm">View</a>
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
        <a href="{{ route('cars.index') }}" class="btn btn-primary" style="margin-top:1rem;">Browse Cars</a>
    </div>
@endif

@endsection

@push('scripts')
<script>
function updateAllTimers() {
    document.querySelectorAll('.countdown-timer').forEach(container => {
        const returnTime  = container.dataset.returnTime;
        const timerDisplay = container.querySelector('.timer-display');
        if (!timerDisplay) return;

        const now        = new Date();
        const returnDate = new Date(returnTime);
        const diff       = returnDate - now;

        if (diff <= 0) {
            timerDisplay.innerHTML = '<span style="color:var(--red);font-weight:700;">⚠️ Overdue — Please return car</span>';
        } else {
            const hours   = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            if (hours > 24) {
                const days = Math.floor(hours / 24);
                const rem  = hours % 24;
                timerDisplay.textContent = `${days}d ${rem}h ${minutes}m remaining`;
            } else {
                timerDisplay.textContent = `${hours}h ${minutes}m ${seconds}s remaining`;
            }
        }
    });
}
setInterval(updateAllTimers, 1000);
updateAllTimers();
</script>
@endpush