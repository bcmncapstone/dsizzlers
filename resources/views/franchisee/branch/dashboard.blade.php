@extends('layouts.franchisee')

@section('content')
<div class="reports-page">
    <div class="reports-container">
        <div class="reports-header-box">
            <div class="reports-header">
                <h1 class="reports-title">Branch Management</h1>
                <p class="reports-subtitle">
                    {{ $branch->location }} - Manage your branch performance, inventory, and finances
                </p>
            </div>
        </div>

        <div class="reports-grid" style="margin-bottom: 24px;">
            <div class="reports-card">
                <h2 class="reports-card-title">Total Orders</h2>
                <p class="reports-card-description">All branch orders to date</p>
                <div class="reports-card-link">{{ number_format($totalOrders) }}</div>
            </div>

            <div class="reports-card">
                <h2 class="reports-card-title">Total Sales</h2>
                <p class="reports-card-description">Sales based on branch stock-out transactions</p>
                <div class="reports-card-link">₱{{ number_format($totalSales, 2) }}</div>
            </div>

            <div class="reports-card">
                <h2 class="reports-card-title">Sales This Month</h2>
                <p class="reports-card-description">Current month branch stock-out sales</p>
                <div class="reports-card-link">₱{{ number_format($salesThisMonth, 2) }}</div>
            </div>
        </div>

        <div class="reports-header-box" style="margin-bottom: 20px;">
            <div class="reports-header">
                <h2 class="reports-title" style="font-size: 1.35rem;">Branch Management Modules</h2>
                <p class="reports-subtitle">Select a module to view detailed information about your branch operations</p>
            </div>
        </div>

        <div class="reports-grid">
            <a href="{{ route('franchisee.branch.performance') }}" class="reports-card">
                <h2 class="reports-card-title">Performance</h2>
                <p class="reports-card-description">View operational performance metrics</p>
                <div class="reports-card-link">View Report →</div>
            </a>

            <a href="{{ route('franchisee.branch.inventory') }}" class="reports-card">
                <h2 class="reports-card-title">Item Inventory</h2>
                <p class="reports-card-description">Track stock movements by date range</p>
                <div class="reports-card-link">View Report →</div>
            </a>

            <a href="{{ route('franchisee.branch.financial') }}" class="reports-card">
                <h2 class="reports-card-title">Financial</h2>
                <p class="reports-card-description">Track revenue, expenses, and profitability</p>
                <div class="reports-card-link">View Report →</div>
            </a>
        </div>

        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded" style="margin-top: 24px;">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Inventory Management Note</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>
                            Delivered items increase your stock. Sales are counted from franchisee stock decreases and franchisee staff stock-out transactions.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
