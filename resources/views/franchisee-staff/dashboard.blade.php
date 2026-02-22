@extends('layouts.franchisee-staff')

@section('title', 'Franchisee Staff Dashboard')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        
        <!-- Welcome Header -->
        <div class="dashboard-header">
            <h1>Welcome, Franchisee Staff!</h1>
            <p>Manage your D-Sizzlers operations efficiently</p>
            
            @if(session('success'))
                <div class="alert alert-success">
                    <strong>✓</strong>
                    {{ session('success') }}
                </div>
            @endif
        </div>

        <!-- Dashboard Cards Grid -->
        <div class="card-grid">
            
            <!-- Update Password Card -->
            <a href="{{ route('franchisee-staff.password') }}" class="card card-orange">
                <div class="card-icon-wrapper">
                    <div class="card-icon">🔑</div>
                </div>
                <h3>Update Password</h3>
                <p>Change your account password</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Manage Items Card -->
            <a href="{{ route('franchisee_staff.item.index') }}" class="card card-red">
                <div class="card-icon-wrapper">
                    <div class="card-icon">🍽️</div>
                </div>
                <h3>Item</h3>
                <p>View and manage all items</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Edit Profile Card -->
            <a href="{{ route('franchisee-staff.account.show') }}" class="card card-yellow">
                <div class="card-icon-wrapper">
                    <div class="card-icon">👥</div>
                </div>
                <h3>Edit Profile</h3>
                <p>Update your account information</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Orders Card -->
            <a href="{{ route('franchisee_staff.orders.index') }}" class="card card-blue">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📦</div>
                </div>
                <h3>Orders</h3>
                <p>Check orders</p>
                <div class="card-arrow">View →</div>
            </a>

                <!-- Item Stock Card -->
            <a href="{{ route('franchisee-staff.stock.index') }}" class="card card-purple">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📊</div>
                </div>
                <h3>Item Stock</h3>
                <p>Manage item inventory</p>
                <div class="card-arrow">View →</div>
            </a>

    </div>
</div>

@endsection

