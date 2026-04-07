@extends('layouts.app')
@section('title', 'View Cars')

@section('content')

<div class="page-header">
    <h1>Our Fleet</h1>
    <p>Browse all cars available for rent.</p>
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
                    <div class="plate">{{ $car->plate_number }} &middot; {{ $car->year }} &middot; {{ $car->seat_capacity }} seats</div>

                    <span class="car-card-badge {{ $car->status === 'available' ? 'badge-available' : 'badge-rented' }}">
                        {{ ucfirst($car->status) }}
                    </span>

                    <div class="price">
                        &#8369;{{ number_format($car->price_per_day) }}
                        <span>/ day</span>
                    </div>

                    @if($car->description)
                        <p class="car-desc">{{ $car->description }}</p>
                    @endif

                    @auth
                        @if(auth()->user()->isAdmin())
                            <div class="car-card-actions">
                                <a href="{{ route('cars.edit', $car) }}" class="btn btn-secondary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('cars.destroy', $car) }}"
                                      onsubmit="return confirm('Delete {{ $car->brand }} {{ $car->model }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="empty-state">
        <div class="icon">🚘</div>
        <p>No cars in the fleet yet.</p>
    </div>
@endif

@endsection