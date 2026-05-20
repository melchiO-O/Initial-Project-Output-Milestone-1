@extends('layouts.app')
@section('title', 'All Rentals')

@section('content')

<div class="page-header">
    <h1>All Rentals</h1>
    <p>Manage all customer rentals.</p>
</div>

{{-- Summary stats --}}
<div class="stats-grid" style="margin-bottom:2rem;">
    <div class="stat-card">
        <div class="stat-label">Total Rentals</div>
        <div class="stat-value">{{ $rentals->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending</div>
        <div class="stat-value" style="color: var(--orange);">
            {{ $rentals->where('status', 'pending')->count() }}
        </div>
        <div class="stat-sub">Awaiting activation</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active</div>
        <div class="stat-value" style="color: var(--green);">
            {{ $rentals->where('status', 'active')->count() }}
        </div>
        <div class="stat-sub">Currently rented</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Returned</div>
        <div class="stat-value" style="color: var(--gray-lt);">
            {{ $rentals->where('status', 'returned')->count() }}
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Cancelled</div>
        <div class="stat-value" style="color: var(--red);">
            {{ $rentals->where('status', 'cancelled')->count() }}
        </div>
    </div>
</div>

{{-- Filter Tabs --}}
<div class="filter-tabs">
    <button class="filter-tab active" data-filter="all" data-color="yellow">
        All
        <span class="filter-count">{{ $rentals->count() }}</span>
    </button>
    <button class="filter-tab" data-filter="pending" data-color="orange">
        Pending
        <span class="filter-count">{{ $rentals->where('status', 'pending')->count() }}</span>
    </button>
    <button class="filter-tab" data-filter="active" data-color="green">
        Active
        <span class="filter-count">{{ $rentals->where('status', 'active')->count() }}</span>
    </button>
    <button class="filter-tab" data-filter="returned" data-color="gray">
        Returned
        <span class="filter-count">{{ $rentals->where('status', 'returned')->count() }}</span>
    </button>
    <button class="filter-tab" data-filter="cancelled" data-color="red">
        Cancelled
        <span class="filter-count">{{ $rentals->where('status', 'cancelled')->count() }}</span>
    </button>
</div>

{{-- No results message --}}
<div id="no-results" style="display:none;" class="empty-state">
    <div class="icon">🔍</div>
    <p>No rentals found for this filter.</p>
</div>

<div class="table-wrap" id="table-wrap">
    <table class="table" id="rentals-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Renter</th>
                <th>License #</th>
                <th>Car</th>
                <th>Pickup</th>
                <th>Return</th>
                <th>Duration</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rentals as $rental)
            <tr data-status="{{ $rental->status }}">
                <td>{{ $rental->id }}</td>
                <td>
                    <strong>{{ $rental->user->name }}</strong><br>
                    <small style="color:var(--gray-lt);">{{ $rental->user->email }}</small>
                </td>
                <td class="mono">{{ $rental->license_number }}</td>
                <td>{{ $rental->car->brand }} {{ $rental->car->model }}</td>
                <td>{{ $rental->pickup_datetime->format('M d, Y h:i A') }}</td>
                <td>{{ $rental->return_datetime->format('M d, Y h:i A') }}</td>
                <td>
                    {{ $rental->duration_hours }} hrs
                    @if($rental->duration_hours >= 24)
                        @php
                            $days = $rental->duration_hours / 24;
                            $daysDisplay = is_int($days) ? $days : number_format($days, 1);
                        @endphp
                        <br><small>({{ $daysDisplay }} day{{ $daysDisplay != 1 ? 's' : '' }})</small>
                    @endif
                    @if($rental->status === 'active' && now()->gt($rental->return_datetime))
                        <br><small class="text-danger">⚠️ OVERDUE</small>
                    @endif
                </td>
                <td>
                    <div class="payment-info">
                        <button class="btn btn-sm btn-info" onclick="showPaymentDetails({{ $rental->id }})">
                            Show Payment
                        </button>
                    </div>
                    <div id="payment-{{ $rental->id }}" style="display:none; margin-top:10px;">
                        <small>💰 Base Rental: ₱{{ number_format($rental->sub_total, 2) }}</small><br>
                        <small>✅ Down Payment (Non-refundable): - ₱{{ number_format($rental->payment->down_payment, 2) }}</small><br>
                        <small>💵 Remaining Balance: ₱{{ number_format($rental->payment->remaining_balance, 2) }}</small><br>
    
                        @if($rental->payment->damage_fee > 0)
                            <small class="text-danger">⚠️ Damage Fee: + ₱{{ number_format($rental->payment->damage_fee, 2) }}</small><br>
                        @endif

                        @if($rental->status === 'active' && now()->gt($rental->return_datetime))
                            @php $overdueFee = $rental->calculateOverdueFee(); @endphp
                            <small class="text-danger">⏰ Overdue Fee: + ₱{{ number_format($overdueFee, 2) }} (₱200/hour)</small><br>
                        @endif

                        <hr style="margin:5px 0;">
                        <small><strong>🧾 SUB TOTAL: ₱{{ number_format($rental->payment->sub_total, 2) }}</strong></small><br>
                        <small>✅ Down Payment Paid: - ₱{{ number_format($rental->payment->down_payment, 2) }}</small><br>
                        <small><strong>💵 DUE UPON RETURN: ₱{{ number_format($rental->payment->due_on_return, 2) }}</strong></small>
    
                        @if($rental->status === 'returned')
                            @php $actualCost = $rental->calculateActualCost(); @endphp
                            @if($actualCost)
                                <hr style="margin:5px 0;">
                                <small>📊 <strong>Return Breakdown:</strong></small><br>
                                <small>Actual Hours Used: {{ $actualCost['actual_hours'] }} (of {{ $actualCost['original_hours'] }} hrs)</small><br>
                                <small>Down Payment Kept: ₱{{ number_format($actualCost['down_payment_kept'], 2) }}</small><br>
                                <small>Paid from Remaining: ₱{{ number_format($actualCost['amount_paid_from_remaining'], 2) }}</small><br>
                                @if($actualCost['overdue_hours'] > 0)
                                    <small class="text-danger">Overdue Fee: ₱{{ number_format($actualCost['overdue_fee'], 2) }}</small><br>
                                @endif
                                @if($actualCost['savings'] > 0)
                                    <small class="text-success">Savings: ₱{{ number_format($actualCost['savings'], 2) }}</small><br>
                                @endif
                            @endif
                            <hr style="margin:5px 0;">
                            <small><strong>💰 GRAND TOTAL: ₱{{ number_format($rental->grand_total, 2) }}</strong></small><br>
                            <small><strong>✅ FINAL PAID: ₱{{ number_format($rental->final_amount_paid, 2) }}</strong></small>
                        @endif
                    </div>
                </td>
                <td>
                    <span class="rental-status status-{{ $rental->status }}">
                        {{ ucfirst($rental->status) }}
                    </span>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('rentals.show', $rental) }}" class="btn btn-secondary btn-sm">Manage</a>

                        @if($rental->status === 'pending')
                            <form action="{{ route('rentals.activate', $rental) }}" method="POST" style="display:inline;">
                                @csrf @method('PUT')
                                <button type="submit" class="btn btn-success btn-sm" style="color:var(--green);"
                                    onclick="return confirm('Confirm down payment received?')">
                                    Activate
                                </button>
                            </form>
                        @endif

                        @if($rental->status === 'active')
                            <button class="btn btn-return btn-sm" onclick="showReturnForm({{ $rental->id }})">
                                Return Car
                            </button>
                        @endif

                        @if(in_array($rental->status, ['pending', 'active']))
                            <form action="{{ route('rentals.cancel', $rental) }}" method="POST" style="display:inline;">
                                @csrf @method('PUT')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Cancel this rental?')">
                                    Cancel
                                </button>
                            </form>
                        @endif
                    </div>

                    {{-- Return Form --}}
                    <div id="return-form-{{ $rental->id }}" style="display:none; margin-top:10px;">
                        <form method="POST" action="{{ route('rentals.return', $rental) }}">
                            @csrf @method('PUT')
                            <div class="form-group">
                                <label>Damage Fee (₱)</label>
                                <input type="number" name="damage_fee" step="0.01" min="0" value="0">
                            </div>
                            <div class="form-group">
                                <label>Damage Notes</label>
                                <textarea name="damage_notes" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm">Confirm Return</button>
                            <button type="button" class="btn btn-secondary btn-sm"
                                onclick="hideReturnForm({{ $rental->id }})">Cancel</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="empty-td">No rentals found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
const colorMap = {
    yellow: { bg: '#f5c400', border: '#f5c400', text: '#0a0a0a' },
    orange: { bg: '#f57c00', border: '#f57c00', text: '#ffffff' },
    green:  { bg: '#2ecc71', border: '#2ecc71', text: '#0a0a0a' },
    gray:   { bg: '#888888', border: '#888888', text: '#ffffff' },
    red:    { bg: '#e03c3c', border: '#e03c3c', text: '#ffffff' },
};

const tabs      = document.querySelectorAll('.filter-tab');
const rows      = document.querySelectorAll('#rentals-table tbody tr[data-status]');
const noResults = document.getElementById('no-results');
const tableWrap = document.getElementById('table-wrap');

function applyActiveStyle(tab) {
    const c = colorMap[tab.dataset.color] || colorMap.yellow;
    tab.style.background  = c.bg;
    tab.style.borderColor = c.border;
    tab.style.color       = c.text;
}

function resetStyle(tab) {
    tab.style.background  = '';
    tab.style.borderColor = '';
    tab.style.color       = '';
}

// Set default on load
applyActiveStyle(document.querySelector('.filter-tab.active'));

tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        // Reset all
        tabs.forEach(t => { t.classList.remove('active'); resetStyle(t); });

        // Activate clicked
        tab.classList.add('active');
        applyActiveStyle(tab);

        // Filter rows
        const filter = tab.dataset.filter;
        let visible = 0;
        rows.forEach(row => {
            const match = filter === 'all' || row.dataset.status === filter;
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        noResults.style.display = visible === 0 ? 'block' : 'none';
        tableWrap.style.display = visible === 0 ? 'none'  : '';
    });
});

function showPaymentDetails(rentalId) {
    const div = document.getElementById('payment-' + rentalId);
    div.style.display = div.style.display === 'none' ? 'block' : 'none';
}

function showReturnForm(rentalId) {
    document.getElementById('return-form-' + rentalId).style.display = 'block';
}
function hideReturnForm(rentalId) {
    document.getElementById('return-form-' + rentalId).style.display = 'none';
}
</script>

<style>
/* ── Filter tabs ─────────────────────────────────────────────── */
.filter-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.filter-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 1.1rem;
    border-radius: var(--radius);
    border: 1px solid var(--gray-3);
    background: var(--gray);
    color: var(--gray-lt);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.18s, border-color 0.18s, color 0.18s;
}

.filter-tab:hover {
    border-color: var(--yellow);
    color: var(--white);
}

.filter-count {
    background: rgba(0,0,0,0.18);
    border-radius: 99px;
    padding: 0.05rem 0.45rem;
    font-size: 0.72rem;
    font-weight: 700;
}

/* ── Table ───────────────────────────────────────────────────── */
.table-wrap { overflow-x: visible; width: 100%; }

.table { width: 100%; min-width: unset; table-layout: auto; }

.table td, .table th {
    padding: 8px 6px;
    font-size: 0.78rem;
    vertical-align: middle;
    white-space: normal;
}

.table th:nth-child(1), .table td:nth-child(1) { width: 30px;  }
.table th:nth-child(3), .table td:nth-child(3) { width: 90px;  }
.table th:nth-child(5), .table td:nth-child(5),
.table th:nth-child(6), .table td:nth-child(6) { width: 100px; }
.table th:nth-child(7), .table td:nth-child(7) { width: 70px;  }
.table th:nth-child(8), .table td:nth-child(8) { width: 110px; }
.table th:nth-child(9), .table td:nth-child(9) { width: 75px;  }
.table th:last-child,   .table td:last-child   { width: 160px; min-width: unset; }

/* ── Action buttons ──────────────────────────────────────────── */
.action-btns {
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: flex-start;
}
.action-btns form,
.action-btns a,
.action-btns button { display: block; width: 100%; text-align: center; }

.btn-sm       { padding: 3px 6px; font-size: 0.72rem; }
.text-success { color: #28a745; }
.text-danger  { color: #dc3545; }
</style>

@endsection