@extends('layouts.franchisee-staff')

@section('content')
    <div class="container">
        <h1>Franchisee Staff Dashboard</h1>
        <p>Welcome, Franchisee Staff!</p>
        <a href="{{ route('franchisee-staff.password') }}" class="btn btn-primary">Update Password</a>
        <a href="{{ route(name: 'franchisee_staff.item.index') }}" class="btn btn-secondary">Item</a>
        <a href="{{ route(name: 'franchisee-staff.account.show') }}" class="btn btn-secondary">Edit Profile</a>
    </div>
@endsection
