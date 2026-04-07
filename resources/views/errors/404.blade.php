@extends('layouts.app')
@section('title', '404 – Not Found')

@section('content')
<div class="error-page">
    <div class="error-code">404</div>
    <h2>Page Not Found</h2>
    <p>The page you're looking for doesn't exist or has been moved.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Go Home</a>
</div>
@endsection