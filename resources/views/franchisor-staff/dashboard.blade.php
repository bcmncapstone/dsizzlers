@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Franchisor Staff Dashboard</h1>
        <p>Welcome, Franchisor Staff!</p>
        <a href="{{ route('franchisor-staff.password.update') }}" class="btn btn-primary">Update Password</a>
         <a href="{{ route(name: 'franchisor-staff.account.show') }}" class="btn btn-secondary">Edit Profile</a>
    </div>
@endsection
