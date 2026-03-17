@extends('layouts.franchisee') 

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        
        <!-- Welcome Header -->
        <div class="dashboard-header">
            <h1>Welcome, Franchisee!</h1>
            <p>Manage your branch operations efficiently</p>
            
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
            <a href="{{ route('franchisee.password.update') }}" class="card card-orange">
                <div class="card-icon-wrapper">
                    <div class="card-icon">🔑</div>
                </div>
                <h3>Update Profile</h3>
                <p>Secure your account with a new password and username</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Manage Branch Card -->
            <a href="{{ route('franchisee.branch.dashboard') }}" class="card card-red">
                <div class="card-icon-wrapper">
                    <div class="card-icon">🏢</div>
                </div>
                <h3>Manage Branch</h3>
                <p>View and manage your branch information</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Items Card -->
            <a href="{{ route('franchisee.item.index') }}" class="card card-yellow">
                <div class="card-icon-wrapper">
                    <div class="card-icon">🍽️</div>
                </div>
                <h3>Item</h3>
                <p>Manage menu items and products</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Cart Card -->
            <a href="{{ route('franchisee.cart.index') }}" class="card card-blue">
                <div class="card-icon-wrapper">
                    <div class="card-icon">🛒</div>
                </div>
                <h3>Cart</h3>
                <p>Check cart</p>
                <div class="card-arrow">View →</div>
            </a>

               <!-- Orders Card -->
            <a href="{{ route('franchisee.orders.index') }}" class="card card-green">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📦</div>
                </div>
                <h3>Order</h3>
                <p>Check orders</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Add Staff Card -->
            <a href="{{ route('account.create') }}" class="card card-red">
                <div class="card-icon-wrapper">
                    <div class="card-icon">➕</div>
                </div>
                <h3>Add Staff</h3>
                <p>Create new staff accounts and manage permissions</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- User Accounts Card -->
            <a href="{{ route('franchisee.account.index') }}" class="card card-purple">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📝</div>
                </div>
                <h3>View Contract</h3>
                <p>Manage Contract</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Reports Card -->
            <a href="{{ route('franchisee.reports.index') }}" class="card card-blue">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📈</div>
                </div>
                <h3>Report</h3>
                <p>View detailed analytics and business reports</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Messages Card -->
            <a href="{{ route('communication.index') }}" class="card card-green">
                <div class="card-icon-wrapper">
                    <div class="card-icon">💬</div>
                </div>
                <h3>Message</h3>
                <p>Communicate with franchisor and team members</p>
                <div class="card-arrow">View →</div>
            </a>
        </div>

    </div>
</div>

@endsection
