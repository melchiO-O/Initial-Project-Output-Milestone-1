@extends('layouts.app')
@section('title', '403 – Access Denied')

@section('content')
<div class="error-page">
    <div class="error-code">403</div>
    <h2>Access Denied</h2>
    <p>You don't have permission to view this page.<br>This area is for administrators only.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Go Home</a>
    &nbsp;
    @auth
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Dashboard</a>
    @endauth
</div>
@endsection