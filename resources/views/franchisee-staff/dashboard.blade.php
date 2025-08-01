@extends('layouts.franchisee-staff')

@section('content')
    <div class="container">
        <h1>Franchisee Staff Dashboard</h1>
        <p>Welcome, Franchisee Staff!</p>
        <a href="{{ route('settings.password') }}" class="btn btn-primary">Update Password</a>
    </div>
@endsection
