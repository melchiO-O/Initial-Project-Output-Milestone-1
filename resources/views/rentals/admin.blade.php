{{-- resources/views/rentals/admin.blade.php --}}
@extends('layouts.app')
@section('title', 'Admin - All Rentals')

@section('content')
<div class="page-header">
    <h1>Manage All Rentals</h1>
    <p>Overview of all car rentals in the system</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Rentals</div>
        <div class="stat-value">{{ $rentals->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active Rentals</div>
        <div class="stat-value">{{ $activeRentals->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Overdue Rentals</div>
        <div class="stat-value">{{ $overdueRentals->count() }}</div>
    </div>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
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
            @forelse($rentals as $rental)
                <tr>
                    <td>#{{ $rental->id }}</td>
                    <td>
                        <strong>{{ $rental->user->name }}</strong><br>
                        <small>{{ $rental->user->email }}</small>
                    </td>
                    <td>
                        {{ $rental->car->brand }} {{ $rental->car->model }}<br>
                        <small>{{ $rental->car->plate_number }}</small>
                    </td>
                    <td>{{ $rental->pickup_datetime->format('M d, Y h:i A') }}</td>
                    <td>{{ $rental->return_datetime->format('M d, Y h:i A') }}</td>
                    <td>{{ $rental->duration_hours }} hours</td>
                    <td>₱{{ number_format($rental->total_price, 2) }}</td>
                    <td>
                        <span class="status-badge status-{{ $rental->status }}">
                            {{ ucfirst($rental->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('rentals.show', $rental) }}" class="btn btn-secondary btn-sm">View</a>
                        @if($rental->status === 'active')
                            <form action="{{ route('rentals.return', $rental) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Mark as returned?')">Return</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="empty-td">No rentals found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection