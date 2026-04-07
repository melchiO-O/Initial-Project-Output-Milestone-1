@extends('layouts.app')
@section('title', 'Register')

@section('content')

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-brand">fast<span>LANE</span></div>
        <div class="auth-subtitle">Create your account</div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name"
                       value="{{ old('name') }}" placeholder="Juan Dela Cruz"
                       required autofocus autocomplete="name">
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       value="{{ old('email') }}" placeholder="you@example.com"
                       required autocomplete="email">
                @error('email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Min. 8 characters"
                       required autocomplete="new-password">
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       placeholder="Repeat your password"
                       required autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary btn-full">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account?
            <a href="{{ route('login') }}">Sign in</a>
        </div>

    </div>
</div>

@endsection