{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        
        <!-- Welcome Header -->
        <div class="dashboard-header">
            <h1>Welcome, Admin!</h1>
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
            
            <!-- Create Account Card -->
            <a href="{{ route('accounts.create') }}" class="card card-orange">
                <div class="card-icon-wrapper">
                    <div class="card-icon">👤</div>
                </div>
                <h3>Create Account</h3>
                <p>Add new user accounts and manage permissions</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Update Password Card -->
            <a href="{{ route('admin.password.update') }}" class="card card-orange">
                <div class="card-icon-wrapper">
                    <div class="card-icon">🔑</div>
                </div>
                <h3>Update Password</h3>
                <p>Secure your account with a new password</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Manage Branches Card -->
            <a href="{{ route('admin.branches.index') }}" class="card card-red">
                <div class="card-icon-wrapper">
                    <div class="card-icon">🏢</div>
                </div>
                <h3>Branches</h3>
                <p>View and manage all branch locations</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Add Item Card -->
            <a href="{{ route('admin.items.create') }}" class="card card-yellow">
                <div class="card-icon-wrapper">
                    <div class="card-icon">🍽️</div>
                </div>
                <h3>Add Item</h3>
                <p>Create new menu items and products</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Manage Orders Card -->
            <a href="{{ route('admin.manageOrder.index') }}" class="card card-blue">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📦</div>
                </div>
                <h3>Orders</h3>
                <p>Track and manage all customer orders</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Stock Management Card -->
            <a href="{{ route('admin.stock.index') }}" class="card card-purple">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📊</div>
                </div>
                <h3>Stock Management</h3>
                <p>Monitor inventory levels and stock transactions</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Reports Card -->
            <a href="{{ route('admin.reports.index') }}" class="card card-purple">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📈</div>
                </div>
                <h3>Reports</h3>
                <p>View detailed analytics and business reports</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Messages Card -->
            <a href="{{ route('communication.index') }}" class="card card-green">
                <div class="card-icon-wrapper">
                    <div class="card-icon">💬</div>
                </div>
                <h3>Messages</h3>
                <p>Communicate with team and users in real-time</p>
                <div class="card-arrow">View →</div>
            </a>
        </div>
    </div>
</div>

@endsection
