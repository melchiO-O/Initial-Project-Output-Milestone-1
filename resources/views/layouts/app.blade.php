<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>fastLANE – @yield('title', 'Car Rental')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

<nav class="navbar">
    <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="nav-brand">fast<span>LANE</span></a>

    <ul class="nav-links">
        @auth
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>
            </li>

            <li>
                <a href="{{ route('cars.index') }}" class="{{ request()->routeIs('cars.index') ? 'active' : '' }}">
                    Browse Cars
                </a>
            </li>

            {{-- My Rentals — regular users only --}}
            @if(!auth()->user()->isAdmin())
                <li>
                    <a href="{{ route('rentals.index') }}" class="{{ request()->routeIs('rentals.*') ? 'active' : '' }}">
                        My Rentals
                        @php
                            $activeCount = auth()->user()->rentals()->whereIn('status', ['pending','active'])->count();
                        @endphp
                        @if($activeCount > 0)
                            <span class="badge">{{ $activeCount }}</span>
                        @endif
                    </a>
                </li>
            @endif

            {{-- Admin links --}}
            @if(auth()->user()->isAdmin())
                <li>
                    <a href="{{ route('cars.create') }}" class="{{ request()->routeIs('cars.create') ? 'active' : '' }}">
                        Add Car
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.rentals') }}" class="{{ request()->routeIs('admin.rentals') ? 'active' : '' }}">
                        Manage Rentals
                    </a>
                </li>
            @endif

            {{-- Profile --}}
            @if(auth()->user()->isUser())
            <li>
                <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    Profile
                </a>
            </li>
            @endif

            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-btn-logout">Logout</button>
                </form>
            </li>
        @else
            <li>
                <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Home</a>
            </li>
            <li>
                <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">Login</a>
            </li>
            <li>
                <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'active' : '' }}">Register</a>
            </li>
        @endauth
    </ul>
</nav>

<main class="main-content">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    @if(session('status') === 'profile-updated')
        <div class="alert alert-success">Profile updated successfully!</div>
    @endif

    @yield('content')
</main>

<footer class="footer">
    <p>&copy; {{ date('Y') }} fastLANE Car Rental. All rights reserved.</p>
</footer>

<style>
.badge {
    background-color: #f5c400;
    color: #0a0a0a;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 0.7rem;
    margin-left: 5px;
    display: inline-block;
    min-width: 18px;
    text-align: center;
    font-weight: 700;
}
</style>

@stack('scripts')
</body>
</html>