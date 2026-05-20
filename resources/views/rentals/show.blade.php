@extends('layouts.app')
@section('title', 'Rental Details')

@section('content')

<div class="page-header">
    <h1>Rental Details</h1>
    <p>Rental #{{ $rental->id }} — <strong>{{ $rental->car->brand }} {{ $rental->car->model }}</strong></p>
</div>

<div class="details-grid">

    {{-- Car Info --}}
    <div class="details-card">
        <h3>Car Information</h3>
        @php $src = $rental->car->getImageSrc(); @endphp
        @if($src)
            <img src="{{ $src }}" alt="{{ $rental->car->brand }}"
                 style="width:100%;height:160px;object-fit:cover;border-radius:6px;margin-bottom:1rem;">
        @endif
        <div class="info-list">
            <div class="info-item">
                <span class="label">Car</span>
                <span>{{ $rental->car->brand }} {{ $rental->car->model }}</span>
            </div>
            <div class="info-item">
                <span class="label">Plate</span>
                <span class="mono">{{ $rental->car->plate_number }}</span>
            </div>
            <div class="info-item">
                <span class="label">Year</span>
                <span>{{ $rental->car->year }}</span>
            </div>
            <div class="info-item">
                <span class="label">Seats</span>
                <span>{{ $rental->car->seat_capacity }}</span>
            </div>
        </div>
    </div>

    {{-- Renter Info --}}
    <div class="details-card">
        <h3>Renter Information</h3>
        <div class="info-list">
            <div class="info-item">
                <span class="label">Name</span>
                <span>{{ $rental->user->name }}</span>
            </div>
            <div class="info-item">
                <span class="label">Email</span>
                <span>{{ $rental->user->email }}</span>
            </div>
            <div class="info-item">
                <span class="label">License #</span>
                <span class="mono">{{ $rental->license_number }}</span>
            </div>
            <div class="info-item">
                <span class="label">License Expiry</span>
                <span>{{ \Carbon\Carbon::parse($rental->license_expiry)->format('F d, Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Rental Info --}}
    <div class="details-card">
        <h3>Rental Information</h3>
        <div class="info-list">
            <div class="info-item">
                <span class="label">Status</span>
                <span class="rental-status status-{{ $rental->status }}">{{ ucfirst($rental->status) }}</span>
            </div>
            <div class="info-item">
                <span class="label">Pickup</span>
                <span>{{ $rental->pickup_datetime->format('M d, Y h:i A') }}</span>
            </div>
            <div class="info-item">
                <span class="label">Return</span>
                <span>{{ $rental->return_datetime->format('M d, Y h:i A') }}</span>
            </div>
            @if($rental->actual_return_datetime)
                <div class="info-item">
                    <span class="label">Actual Return</span>
                    <span>{{ $rental->actual_return_datetime->format('M d, Y h:i A') }}</span>
                </div>
            @endif
            <div class="info-item">
                <span class="label">Duration</span>
                <span>
                    {{ $rental->duration_hours }} hours
                    @if($rental->duration_hours >= 24)
                        @php
                            $days = $rental->duration_hours / 24;
                            $daysDisplay = is_int($days) ? $days : number_format($days, 1);
                        @endphp
                        ({{ $daysDisplay }} day{{ $daysDisplay != 1 ? 's' : '' }})
                    @endif
                </span>
            </div>
            @if($rental->pickup_location)
                <div class="info-item">
                    <span class="label">Pickup Location</span>
                    <span>{{ $rental->pickup_location }}</span>
                </div>
            @endif
            @if($rental->notes)
                <div class="info-item">
                    <span class="label">Notes</span>
                    <span>{{ $rental->notes }}</span>
                </div>
            @endif
        </div>

        {{-- Time remaining for active rentals --}}
        @if($rental->status === 'active')
            @if(now()->gt($rental->return_datetime))
                @php
                    $overdueFee = $rental->calculateOverdueFee();
                    $overdueTime = $rental->overdue_time;
                @endphp
                <div class="alert alert-danger" style="background: #dc3545; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <strong>⚠️ OVERDUE RENTAL!</strong><br>
                    This car is overdue by {{ $overdueTime }}.<br>
                    Additional fee of ₱{{ number_format($overdueFee, 2) }} has been added (₱200/hour).
                </div>
            @endif
            
            <div class="countdown-timer" data-return-time="{{ $rental->return_datetime }}" style="margin-top:1rem;">
                <div class="timer-label">Time Remaining:</div>
                <div class="timer-display" id="timer-show" style="font-size:1.2rem;font-weight:700;color:var(--yellow);">
                    Calculating...
                </div>
            </div>
        @endif
    </div>

    {{-- Payment Breakdown --}}
    <div class="details-card">
        <h3>Payment Breakdown</h3>
        <div class="info-list">
            <div class="info-item">
                <span class="label">Price / Day</span>
                <span>₱{{ number_format($rental->price_per_day, 2) }}</span>
            </div>
            <div class="info-item">
                <span class="label">Total Usage (Days)</span>
                <span>{{ $rental->duration_hours / 24 }}</span>
            </div>
            <div class="info-item">
                <span class="label">Hourly Rental Cost</span>
                <span>₱{{ number_format($rental->price_per_day / 24, 2) }}</span>
            </div>

            <div style="border-top:1px solid var(--gray-2);margin:0.25rem 0;"></div>

            <div class="info-item highlight-row">
                <span class="label">✅ Down Payment <small>(Paid on Pickup)</small></span>
                <span style="color:var(--green);font-weight:700;">
                    ₱{{ number_format($rental->payment->down_payment, 2) }}
                </span>
            </div>
            <div class="info-item">
                <span class="label">Remaining Balance</span>
                <span>₱{{ number_format($rental->payment->remaining_balance, 2) }}</span>
            </div>

            @if($rental->payment->damage_fee > 0)
                <div class="info-item">
                    <span class="label">⚠️ Damage Fee</span>
                    <span style="color:var(--red);font-weight:600;">
                        + ₱{{ number_format($rental->payment->damage_fee, 2) }}
                    </span>
                </div>
                @if($rental->payment->damage_notes)
                    <div class="info-item">
                        <span class="label">Damage Notes</span>
                        <span style="color:var(--gray-lt);">{{ $rental->payment->damage_notes }}</span>
                    </div>
                @endif
            @endif

            @if($rental->status === 'returned' && $rental->actual_return_datetime)
                @php $actualCost = $rental->calculateActualCost(); @endphp
                @if($actualCost && $actualCost['overdue_hours'] > 0)
                    <div class="alert alert-danger" style="background: #dc3545; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <strong>⚠️ OVERDUE RENTAL!</strong><br>
                        This car was returned <strong>{{ $actualCost['formatted_overdue'] }}</strong> late.<br>
                        Overdue fee: ₱{{ number_format($actualCost['overdue_fee'], 2) }} (₱200/hour)
                    </div>
                @endif
            @endif

            <div style="border-top:1px solid var(--gray-2);margin:0.25rem 0;"></div>

            {{-- Grand Total = base + damage --}}
            <div class="info-item highlight-row">
                <span class="label">🧾 Sub Total</span>
                <span style="color:var(--yellow);font-weight:700;font-size:1.05rem;">
                    ₱{{ number_format($rental->sub_total, 2) }}
                </span>
            </div>

            @if($rental->status === 'returned')
                <div class="info-item highlight-row">
                    <span class="label">💰 GRAND TOTAL</span>
                    <span style="color:var(--yellow);font-weight:700;">
                        ₱{{ number_format($rental->grand_total, 2) }}
                    </span>
                </div>

            @elseif(in_array($rental->status, ['pending', 'active']))
                <div class="info-item">
                    <span class="label">Due on Return</span>
                    <span style="color:var(--gray-lt);">
                        ₱{{ number_format($rental->payment->remaining_balance, 2) }} + any damage/overdue fees
                    </span>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ADMIN ACTIONS --}}
@if(auth()->user()->isAdmin())
    <div style="margin-top:1.5rem;">

        @if($rental->status === 'pending')
            <form method="POST" action="{{ route('rentals.activate', $rental) }}" style="display:inline;">
                @csrf @method('PUT')
                <button type="submit" class="btn btn-primary"
                        onclick="return confirm('Confirm pickup and down payment of ₱{{ number_format($rental->payment->down_payment, 2) }} received?')">
                    ✓ Activate Rental
                </button>
            </form>
        @endif

        @if($rental->status === 'active')
            <div class="return-form-wrap">
                <div class="return-form-title">Process Car Return</div>
                <form method="POST" action="{{ route('rentals.return', $rental) }}">
                    @csrf @method('PUT')
                    <div class="form-row">
                        <div class="form-group">
                            <label>Damage Fee (₱) <span class="optional">(0 if none)</span></label>
                            <input type="number" name="damage_fee" id="damageInput"
                                   value="0" min="0" step="0.01"
                                   oninput="updateFinalDue(this.value)">
                        </div>
                        <div class="form-group">
                            <label>Damage Notes <span class="optional">(optional)</span></label>
                            <input type="text" name="damage_notes"
                                   placeholder="Describe any damage...">
                        </div>
                    </div>

                    <div class="final-due-preview">
                        <div>Remaining Balance: <strong>₱{{ number_format($rental->payment->remaining_balance, 2) }}</strong></div>
                            <div>+ Damage Fee: <strong id="damageFeeDisplay">₱0.00</strong></div>
                            @if(now()->gt($rental->return_datetime))
                                <div>+ Overdue Fee: <strong style="color:var(--red);">₱{{ number_format($rental->calculateOverdueFee(), 2) }}</strong></div>
                            @endif
                            Partial Payment Due: <strong style="color:var(--yellow);" id="finalDueDisplay">₱{{ number_format($rental->payment->remaining_balance, 2) }}</strong>
                            &nbsp;|&nbsp;
                            Sub Total: <strong style="color:var(--yellow);" id="grandTotalDisplay">₱{{ number_format($rental->grand_total, 2) }}</strong>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Confirm car return and collect final payment?')">
                        ✓ Return Car & Collect Payment
                    </button>
                </form>
            </div>
        @endif

        @if(in_array($rental->status, ['pending', 'active']))
            <form method="POST" action="{{ route('rentals.cancel', $rental) }}"
                  style="display:inline;margin-left:0.5rem;">
                @csrf @method('PUT')
                <button type="submit" class="btn btn-danger"
                        onclick="return confirm('Cancel this rental? The car will be made available again.')">
                    ✗ Cancel Rental
                </button>
            </form>
        @endif

        <a href="{{ route('admin.rentals') }}" class="btn btn-secondary" style="margin-left:0.5rem;">
            Back to All Rentals
        </a>
    </div>
@else
    <div style="margin-top:1.5rem;">
        <a href="{{ route('rentals.index') }}" class="btn btn-secondary">Back to My Rentals</a>
    </div>
@endif

@endsection

@push('scripts')
<script>
const remainingBalance = {{ $rental->payment->remaining_balance }};
const baseTotal        = {{ $rental->total_price }};
const overdueFee       = {{ $rental->status === 'active' && now()->gt($rental->return_datetime) ? $rental->calculateOverdueFee() : 0 }};

function updateFinalDue(damage) {
    const fee        = parseFloat(damage) || 0;
    const finalDue   = remainingBalance + fee + overdueFee;
    const grandTotal = baseTotal + fee + overdueFee;

    document.getElementById('damageFeeDisplay').textContent   = '₱' + fee.toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('finalDueDisplay').textContent    = '₱' + finalDue.toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('grandTotalDisplay').textContent  = '₱' + grandTotal.toLocaleString('en-PH', {minimumFractionDigits: 2});
}

// Countdown for active rentals
@if($rental->status === 'active')
function updateTimer() {
    const display    = document.getElementById('timer-show');
    if (!display) return;
    const now        = new Date();
    const returnDate = new Date('{{ $rental->return_datetime }}');
    const diff       = returnDate - now;

    if (diff <= 0) {
        display.innerHTML = '<span style="color:var(--red);">⚠️ Overdue</span>';
    } else {
        const hours   = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        const days    = Math.floor(hours / 24);

        if (days > 0) {
            display.textContent = `${days}d ${hours % 24}h ${minutes}m remaining`;
        } else {
            display.textContent = `${hours}h ${minutes}m ${seconds}s remaining`;
        }
    }
}
setInterval(updateTimer, 1000);
updateTimer();
@endif
</script>
@endpush