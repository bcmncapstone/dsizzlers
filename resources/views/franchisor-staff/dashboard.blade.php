@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <!-- Welcome Header -->
        <div class="dashboard-header">
            <h1>Welcome, Franchisor Staff!</h1>
            <p>Manage your D-Sizzlers operations efficiently</p>
        </div>

        <!-- Dashboard Cards Grid -->
        <div class="card-grid">
            <!-- Update Password Card -->
            <a href="{{ route('franchisor-staff.password.update') }}" class="card card-orange">
                <div class="card-icon-wrapper">
                </div>
                <h3>Update Password</h3>
                <p>Secure your account with a new password</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Edit Profile Card -->
            <a href="{{ route('franchisor-staff.account.show') }}" class="card card-red">
                <div class="card-icon-wrapper">
                </div>
                <h3>View Profile</h3>
                <p>View and update your profile information</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Add Item Card -->
            <a href="{{ route('franchisor-staff.items.index') }}" class="card card-yellow">
                <div class="card-icon-wrapper">
                </div>
                <h3>Add Item</h3>
                <p>Create new menu items and products</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Orders Card -->
            <a href="{{ route('franchisor-staff.manageOrder.index') }}" class="card card-blue">
                <div class="card-icon-wrapper">
                </div>
                <h3>Order</h3>
                <p>Review and manage orders</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Item Stock Card -->
            <a href="{{ route('franchisor-staff.stock.index') }}" class="card card-purple">
                <div class="card-icon-wrapper">
                </div>
                <h3>Stock</h3>
                <p>Manage item inventory</p>
                <div class="card-arrow">View →</div>
            </a>

        </div>
    </div>
</div>

@endsection
