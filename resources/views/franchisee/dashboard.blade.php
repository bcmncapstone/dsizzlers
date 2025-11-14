@extends('layouts.franchisee') 

@section('content')
    <div class="container">
        <h1>Franchisee Dashboard</h1>
        <p>Welcome, Franchisee!</p>
        <a href="{{ route('franchisee.password.update') }}" class="btn btn-primary">Update Password</a>

        <!-- Item -->
        <a href="{{ route(name: 'franchisee.item.index') }}" class="btn btn-secondary">Item</a>
        <a href="{{ route(name: 'franchisee.cart.index') }}" class="btn btn-secondary">Cart</a>
        <a href="{{ route(name: 'account.create') }}" class="btn btn-secondary">Add Staff</a>
        <a href="{{ route('franchisee.account.index') }}" class="btn btn-secondary">User Account</a>

    </div>
@endsection
