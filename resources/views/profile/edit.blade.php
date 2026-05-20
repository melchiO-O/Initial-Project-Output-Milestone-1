@extends('layouts.app')
@section('title', 'My Profile')

@section('content')

<style>
    /* Style the calendar picker icon to white */
    .date-input-wrapper {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    
    input[type="date"] {
        width: 100%;
        padding-right: 35px; /* Make space for the custom icon */
    }
    
    /* Hide the native calendar icon */
    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0;
        position: absolute;
        right: 0;
        top: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
        z-index: 2;
    }
    
    /* Custom calendar icon */
    .date-input-wrapper .calendar-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        font-size: 1.2rem;
        color: white;
        z-index: 1;
    }
    
    /* Optional: Add hover effect */
    input[type="date"]::-webkit-calendar-picker-indicator:hover {
        cursor: pointer;
    }
    
    /* For Firefox */
    input[type="date"] {
        position: relative;
    }
    
    input[type="date"]::-moz-focus-inner {
        border: 0;
    }
</style>

<div class="page-header">
    <h1>My Profile</h1>
    <p>Manage your account details and driver's license.</p>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')

        {{-- Basic Info --}}
        <div class="image-section-title" style="margin-bottom:1rem;">Account Information</div>

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name"
                   value="{{ old('name', $user->name) }}" required>
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email"
                   value="{{ old('email', $user->email) }}" required>
            @error('email')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        {{-- License Section --}}
        <div class="image-section">
            <div class="image-section-title">🪪 Driver's License</div>
            <p style="color:var(--gray-lt);font-size:0.82rem;margin-bottom:1rem;">
                A valid driver's license is required to rent a car.
                One license = one active rental at a time.
            </p>

            <div class="form-row">
                <div class="form-group">
                    <label>License Number</label>
                    <input type="text" name="license_number"
                           value="{{ old('license_number', $user->license_number) }}"
                           placeholder="e.g. N01-23-456789">
                    @error('license_number')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>License Expiry Date</label>
                    <div class="date-input-wrapper">
                        <input type="date" name="license_expiry"
                               value="{{ old('license_expiry', $user->license_expiry?->format('Y-m-d')) }}"
                               min="{{ date('Y-m-d') }}"
                               max="{{ date('Y-m-d', strtotime('+10 years')) }}">
                        <span class="calendar-icon">📅</span>
                    </div>
                    @error('license_expiry')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>

            @if($user->hasLicense())
                <div class="license-info-box">
                    <div class="license-label">✅ License on File</div>
                    <div>
                        <strong>{{ $user->license_number }}</strong>
                        — Expires: {{ $user->license_expiry->format('F d, Y') }}
                    </div>
                </div>
            @else
                <div class="alert alert-error" style="margin-top:0.5rem;">
                    ⚠️ No driver's license on file. You cannot rent a car until you add one.
                </div>
            @endif
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

@endsection