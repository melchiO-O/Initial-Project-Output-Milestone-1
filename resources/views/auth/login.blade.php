@extends('layouts.app')
@section('title', 'Login')

@section('content')

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-brand">fast<span>LANE</span></div>
        <div class="auth-subtitle">Sign in to your account</div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       value="{{ old('email') }}" placeholder="you@example.com"
                       required autofocus autocomplete="email">
                @error('email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••" required autocomplete="current-password">
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-remember">
                <label>
                    <input type="checkbox" name="remember">
                    Remember me
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Sign In</button>
        </form>

        <div class="auth-footer">
            Don't have an account?
            <a href="{{ route('register') }}">Register here</a>
        </div>

    </div>
</div>

@endsection