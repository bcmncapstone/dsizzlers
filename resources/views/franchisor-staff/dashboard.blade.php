@extends('layouts.franchisor-staff')

@section('content')
    <div class="container">
        <h1>Franchisor Staff Dashboard</h1>
        <p>Welcome, Franchisor Staff!</p>
        <a href="{{ route('settings.password') }}" class="btn btn-primary">Update Password</a>
    </div>
@endsection
