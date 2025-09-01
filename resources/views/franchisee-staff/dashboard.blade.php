@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Franchisee Staff Dashboard</h1>
        <p>Welcome, Franchisee Staff!</p>
        <a href="{{ route('settings.password') }}" class="btn btn-primary">Update Password</a>
        <a href="{{ route(name: 'franchisee_staff.item.index') }}" class="btn btn-secondary">Item</a>
    </div>
@endsection
