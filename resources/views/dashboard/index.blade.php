@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

<div class="page-header">
    <h1>Dashboard</h1>
    <p>
        Welcome back, <strong>{{ auth()->user()->name }}</strong> &mdash;
        <span class="role-badge {{ auth()->user()->isAdmin() ? 'role-admin' : 'role-user' }}">
            {{ ucfirst(auth()->user()->role) }}
        </span>
    </p>
</div>

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label" style="color: var(--yellow);">Total Cars</div>
        <div class="stat-value" style="color: var(--yellow);">{{ $totalCars }}</div>
        <div class="stat-sub">In the fleet</div>
    </div>
    <div class="stat-card">
        <div class="stat-label" style="color: var(--green);">Available</div>
        <div class="stat-value" style="color: var(--green);">{{ $availableCars }}</div>
        <div class="stat-sub">Ready to rent</div>
    </div>
    <div class="stat-card">
        <div class="stat-label" style="color: var(--red);">Rented</div>
        <div class="stat-value" style="color: var(--red);">{{ $rentedCars }}</div>
        <div class="stat-sub">Currently out</div>
    </div>
    @if(auth()->user()->isAdmin())
        <div class="stat-card">
            <div class="stat-label" style="color: var(--blue);">Total Users</div>
            <div class="stat-value" style="color: var(--blue);">{{ $totalUsers }}</div>
            <div class="stat-sub">Registered</div>
        </div>
    @endif
</div>

{{-- Admin: full table with actions --}}
@if(auth()->user()->isAdmin())

    <div class="section-header">
        <h2>Manage Cars</h2>
        <a href="{{ route('cars.create') }}" class="btn btn-primary btn-sm">+ Add Car</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Brand & Model</th>
                    <th>Plate Number</th>
                    <th>Year</th>
                    <th>Price / Day</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cars as $car)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if($car->image_path)
                                <img src="{{ asset('storage/' . $car->image_path) }}"
                                     alt="{{ $car->brand }}"
                                     class="table-thumb">
                            @elseif($car->image_url)
                                <img src="{{ $car->image_url }}"
                                     alt="{{ $car->brand }}"
                                     class="table-thumb">
                            @else
                                <div class="table-thumb-placeholder">🚗</div>
                            @endif
                        </td>
                        <td><strong>{{ $car->brand }} {{ $car->model }}</strong></td>
                        <td class="mono">{{ $car->plate_number }}</td>
                        <td>{{ $car->year }}</td>
                        <td>&#8369;{{ number_format($car->price_per_day) }}</td>
                        <td>
                            <span class="car-card-badge {{ $car->status === 'available' ? 'badge-available' : 'badge-rented' }}">
                                {{ ucfirst($car->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('cars.edit', $car) }}" class="btn btn-secondary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('cars.destroy', $car) }}"
                                      onsubmit="return confirm('Delete {{ $car->brand }} {{ $car->model }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-td">No cars in the fleet yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

{{-- Regular user: card view --}}
@else

    <div class="section-header">
        <h2>Available Cars</h2>
    </div>

    @if($cars->count())
        <div class="cars-grid">
            @foreach($cars as $car)
                <div class="car-card">

                    {{-- Car Image --}}
                    @if($car->image_path)
                        <img src="{{ asset('storage/' . $car->image_path) }}"
                             alt="{{ $car->brand }} {{ $car->model }}"
                             class="car-card-img">
                    @elseif($car->image_url)
                        <img src="{{ $car->image_url }}"
                             alt="{{ $car->brand }} {{ $car->model }}"
                             class="car-card-img">
                    @else
                        <div class="car-img-placeholder">🚗</div>
                    @endif

                    <div class="car-card-body">
                        <h3>{{ $car->brand }} {{ $car->model }}</h3>
                        <div class="plate">{{ $car->plate_number }} &middot; {{ $car->year }}  &middot; {{ $car->seat_capacity }} seats</div>
                        <span class="car-card-badge badge-available">Available</span>
                        <div class="price">
                            &#8369;{{ number_format($car->price_per_day) }}
                            <span>/ day</span>
                        </div>
                        @if($car->description)
                            <p class="car-desc">{{ $car->description }}</p>
                        @endif

                        {{-- In the user car card section, add this button --}}
                        <div class="rental-actions" style="margin-top: 1rem;">
                            <a href="{{ route('rentals.create', $car) }}" class="btn btn-primary btn-sm" style="width: 100%;">
                                Rent Now! 🚗
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="icon">🚘</div>
            <p>No cars available right now. Please check back later.</p>
        </div>
    @endif

@endif

@endsection