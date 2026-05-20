@extends('layouts.app')
@section('title', 'Rent a Car')

@section('content')

<div class="page-header">
    <h1>Rent a Car</h1>
    <p>Booking: <strong>{{ $car->brand }} {{ $car->model }}</strong> — ₱{{ number_format($car->price_per_day) }}/day</p>
</div>

<div class="form-card">

    {{-- Car summary --}}
    <div class="car-rental-summary">
        @php $src = $car->getImageSrc(); @endphp
        @if($src)
            <img src="{{ $src }}" alt="{{ $car->brand }}" class="rental-car-img">
        @endif
        <div>
            <div style="font-size:1.1rem;font-weight:600; color: #333;">{{ $car->brand }} {{ $car->model }} ({{ $car->year }})</div>
            <div class="plate" style="color:#666;">{{ $car->plate_number }} · {{ $car->seat_capacity }} seats</div>
            <div class="price" style="font-size:1.4rem; color: #ff6b35; font-weight: bold;">₱{{ number_format($car->price_per_day) }} <span>/ day</span></div>
            <div class="hourly-rate" style="font-size:0.9rem; color:#28a745; font-weight:500; margin-top:5px;">
                💰 Hourly rate: ₱{{ number_format($car->price_per_day / 24, 2) }}/hour
            </div>
        </div>
    </div>

    {{-- License info --}}
    <div class="license-info-box">
        <div class="license-label">🪪 Driver's License on File</div>
        <div style="color: #333;"><strong>{{ $user->license_number }}</strong> — Expires: {{ $user->license_expiry->format('F d, Y') }}</div>
    </div>

    <form method="POST" action="{{ route('rentals.store', $car) }}">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label style="font-weight: 600;">Pickup Date *</label>
                <div class="date-input-wrapper">
                    <input type="date" name="pickup_date" id="pickup_date"
                           value="{{ old('pickup_date') }}"
                           min="{{ now()->addDay()->format('Y-m-d') }}"
                           onchange="updatePickupDateTime()" required>
                    <span class="calendar-icon">📅</span>
                </div>
                @error('pickup_date')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Pickup Time *</label>
                <select name="pickup_time" id="pickup_time" onchange="updatePickupDateTime()" required>
                    <option value="">Select time</option>
                    <option value="08:00">8:00 AM</option>
                    <option value="09:00">9:00 AM</option>
                    <option value="10:00">10:00 AM</option>
                    <option value="11:00">11:00 AM</option>
                    <option value="12:00">12:00 PM</option>
                    <option value="13:00">1:00 PM</option>
                    <option value="14:00">2:00 PM</option>
                    <option value="15:00">3:00 PM</option>
                    <option value="16:00">4:00 PM</option>
                    <option value="17:00">5:00 PM</option>
                </select>
                @error('pickup_time')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Hidden field for combined datetime --}}
        <input type="hidden" name="pickup_datetime" id="pickup_datetime" value="">

        <div class="form-group">
            <label>Duration (hours) *</label>
            <select name="duration_hours" id="duration_hours" onchange="calculateCost()" required>
                <option value="">Select duration</option>
                <option value="3">3 hours (minimum)</option>
                <option value="6">6 hours</option>
                <option value="12">12 hours</option>
                <option value="24">24 hours (1 day)</option>
                <option value="48">48 hours (2 days)</option>
                <option value="72">72 hours (3 days)</option>
                <option value="96">96 hours (4 days)</option>
                <option value="120">120 hours (5 days)</option>
                <option value="168">168 hours (1 week)</option>
                <option value="336">336 hours (2 weeks)</option>
                <option value="504">504 hours (3 weeks)</option>
                <option value="720">720 hours (1 month)</option>
            </select>
            @error('duration_hours')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label>Pickup Location (optional)</label>
            <input type="text" name="pickup_location" value="{{ old('pickup_location') }}" placeholder="e.g., Main Office, Airport Branch">
        </div>

        <div class="form-group">
            <label>Notes <span class="optional">(optional)</span></label>
            <textarea name="notes" placeholder="Any special requests or notes...">{{ old('notes') }}</textarea>
        </div>

        {{-- Cost calculator --}}
        <div class="cost-calculator" id="costBox" style="display:none;">
            <div class="cost-title">💰 Payment Summary</div>
            <div class="cost-row">
                <span>📅 Pickup Date & Time</span>
                <span id="displayPickup" style="color:#333; font-weight:500;">—</span>
            </div>
            <div class="cost-row">
                <span>🔄 Return Date & Time</span>
                <span id="displayReturn" style="color:#333; font-weight:500;">—</span>
            </div>
            <div class="cost-row">
                <span>⏱️ Duration</span>
                <span id="calcDuration" style="color:#333; font-weight:500;">—</span>
            </div>
            <div class="cost-row">
                <span>💰 Hourly Rate</span>
                <span>₱{{ number_format($car->price_per_day / 24, 2) }}/hour</span>
            </div>
            <div class="cost-row">
                <span>💵 Total Rental Cost</span>
                <span id="calcTotal" style="color:#28a745; font-weight:bold; font-size:1.1rem;">—</span>
            </div>
            <div class="cost-row highlight">
                <span>⬇ Down Payment (50%) — Pay on Pickup</span>
                <span id="calcDown" style="color:#e65100; font-weight:bold;">—</span>
            </div>
            <div class="cost-row">
                <span>💳 Remaining Balance — Pay on Return</span>
                <span id="calcBalance" style="color:#ff6b35; font-weight:bold;">—</span>
            </div>
            <div class="info-note" style="margin-top:10px; padding:8px; background:#e8f4f8; border-radius:6px; font-size:0.8rem;">
                ℹ️ <strong>Hourly Billing:</strong> You only pay for the hours you use. Minimum 3 hours rental.
                If you return early, you will be refunded for the unused hours.
            </div>
            <div class="cost-note">⚠️ Additional fees may apply for damages and late returns.</div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Confirm Booking</button>
            <a href="{{ route('cars.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
const pricePerDay = {{ $car->price_per_day }};
const hourlyRate = pricePerDay / 24;

function updatePickupDateTime() {
    const pickupDate = document.getElementById('pickup_date').value;
    const pickupTime = document.getElementById('pickup_time').value;
    
    if (pickupDate && pickupTime) {
        const datetime = pickupDate + 'T' + pickupTime + ':00';
        document.getElementById('pickup_datetime').value = datetime;
        calculateCost();
    }
}

function calculateCost() {
    const pickupDatetime = document.getElementById('pickup_datetime').value;
    const durationHours = parseInt(document.getElementById('duration_hours').value);
    
    if (!pickupDatetime || !durationHours || durationHours <= 0) {
        document.getElementById('costBox').style.display = 'none';
        return;
    }

    const pickupDate = new Date(pickupDatetime);
    const now = new Date();
    
    if (pickupDate <= now) {
        document.getElementById('costBox').style.display = 'none';
        return;
    }
    
    // Calculate return date and time
    const returnDate = new Date(pickupDate.getTime() + (durationHours * 60 * 60 * 1000));
    
    // Calculate total based on actual hours
    const total = durationHours * hourlyRate;
    const down = Math.round(total * 0.5 * 100) / 100;
    const balance = Math.round((total - down) * 100) / 100;

    // Format duration display
    let durationDisplay = durationHours + (durationHours === 1 ? ' hour' : ' hours');
    
    // Only show days if 24 hours or more
    if (durationHours >= 24) {
        const days = durationHours / 24;
        const daysDisplay = Number.isInteger(days) ? days : days.toFixed(1);
        durationDisplay += ` (${daysDisplay} day${days !== 1 ? 's' : ''})`;
    }

    // Format dates for display
    const pickupFormatted = pickupDate.toLocaleString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
    
    const returnFormatted = returnDate.toLocaleString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
    
    document.getElementById('displayPickup').innerHTML = pickupFormatted;
    document.getElementById('displayReturn').innerHTML = returnFormatted;
    document.getElementById('calcDuration').innerHTML = durationDisplay;
    document.getElementById('calcTotal').innerHTML = '₱' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('calcDown').innerHTML = '₱' + down.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('calcBalance').innerHTML = '₱' + balance.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('costBox').style.display = 'block';
}

// Set minimum date for calendar (tomorrow)
document.getElementById('pickup_date').addEventListener('change', function() {
    const selectedDate = new Date(this.value);
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(0, 0, 0, 0);
    
    if (selectedDate < tomorrow) {
        alert('Pickup date must be at least tomorrow');
        this.value = '';
        document.getElementById('pickup_time').value = '';
        calculateCost();
    }
});

// Initial calculation if values exist
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date for calendar
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDate = tomorrow.toISOString().split('T')[0];
    document.getElementById('pickup_date').min = minDate;
    
    calculateCost();
});
</script>

<style>
.car-rental-summary {
    display: flex;
    gap: 1.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    align-items: center;
}

.rental-car-img {
    width: 120px;
    height: 90px;
    object-fit: cover;
    border-radius: 8px;
}

.license-info-box {
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.license-label {
    font-weight: 600;
    color: #1976d2;
    margin-bottom: 0.5rem;
}

/* Date input styling */
.date-input-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
}

.date-input-wrapper input[type="date"] {
    width: 100%;
    padding: 10px;
    padding-right: 35px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
}

.date-input-wrapper input[type="date"]:focus {
    outline: none;
    border-color: #ff6b35;
    box-shadow: 0 0 0 2px rgba(255,107,53,0.1);
}

.date-input-wrapper .calendar-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    font-size: 1.2rem;
}

/* Custom calendar icon for webkit browsers */
input[type="date"]::-webkit-calendar-picker-indicator {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    opacity: 0.6;
    width: 20px;
    height: 20px;
}

input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}

.cost-calculator {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin: 1.5rem 0;
    border: 2px solid #ff6b35;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.cost-title {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #1a1a2e;
    border-bottom: 2px solid #ff6b35;
    padding-bottom: 0.5rem;
    display: inline-block;
}

.cost-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
    color: #333;
    font-size: 1rem;
}

.cost-row span:first-child {
    font-weight: 500;
    color: #555;
}

.cost-row.highlight {
    background: #fff8f0;
    padding: 0.75rem;
    margin: 0.5rem 0;
    border-radius: 8px;
    border-left: 3px solid #e65100;
}

.cost-row.highlight span:first-child {
    color: #e65100;
    font-weight: 600;
}

.cost-note {
    margin-top: 1rem;
    font-size: 0.85rem;
    color: #dc3545;
    padding: 0.75rem;
    background: #ffe6e6;
    border-radius: 6px;
    border-left: 3px solid #dc3545;
}

.info-note {
    color: #31708f;
    background: #e8f4f8;
    border-left: 3px solid #2196f3;
}

/* Time select styling */
select {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
    width: 100%;
    cursor: pointer;
    background-color: white;
}

select:focus {
    outline: none;
    border-color: #ff6b35;
    box-shadow: 0 0 0 2px rgba(255,107,53,0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-actions {
    display: flex;
    margin-top: 1.5rem;
}

.btn-primary {
    background: #var(--confirm);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: var(--yellow);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255,107,53,0.3);
}

.btn-secondary {
    background: #6c757d;
    color: white;
    padding: 12px 24px;
    border: none;
    margin: 10px;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .car-rental-summary {
        flex-direction: column;
        text-align: center;
    }
    
    .rental-car-img {
        width: 100%;
        height: 150px;
    }
    
    .cost-row {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .cost-row span:first-child {
        margin-bottom: 0.25rem;
    }
}
</style>

@endsection