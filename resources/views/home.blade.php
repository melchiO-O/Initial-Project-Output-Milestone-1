@extends('layouts.app')
@section('title', 'Home')

@section('content')

{{-- Hero --}}
<div class="hero">
    <h1>Drive with <span>fast</span>LANE</h1>
    <p>Premium car rentals made simple. Browse our fleet, book instantly, and hit the road with confidence.</p>
    <a href="{{ route('cars.index') }}" class="btn btn-primary">Browse Our Fleet</a>
    @guest
        &nbsp;
        <a href="{{ route('register') }}" class="btn btn-secondary">Get Started Free</a>
    @endguest
</div>

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Cars</div>
        <div class="stat-value">{{ $totalCars }}</div>
        <div class="stat-sub">In our fleet</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Available Now</div>
        <div class="stat-value">{{ $availableCars }}</div>
        <div class="stat-sub">Ready to rent</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Car Brands</div>
        <div class="stat-value">{{ $brands }}</div>
        <div class="stat-sub">To choose from</div>
    </div>
</div>

{{-- Featured Cars --}}
<div class="section-header">
    <h2>Featured Cars</h2>
    <a href="{{ route('cars.index') }}" class="btn btn-secondary btn-sm">View All →</a>
</div>

@if($featuredCars->count())
    <div class="cars-grid">
        @foreach($featuredCars as $car)
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
                    <div class="plate">{{ $car->plate_number }} &middot; {{ $car->year }}</div>
                    <span class="car-card-badge badge-available">Available</span>
                    <div class="price">
                        &#8369;{{ number_format($car->price_per_day) }}
                        <span>/ day</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="empty-state">
        <div class="icon">🚘</div>
        <p>No cars available yet. Check back soon!</p>
    </div>
@endif

@endsection