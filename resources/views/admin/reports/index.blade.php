@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <div class="page-header">
            <h1>Reports</h1>
            <p>Generate and analyze business reports</p>
        </div>

        <div class="reports-grid">
            <!-- Sales Report -->
            <a href="{{ route('admin.reports.sales') }}" class="report-card">
                <div class="report-icon">📊</div>
                <h2>Sales Report</h2>
                <p>Filter sales by date range and franchisee</p>
                <span class="report-link">View Report →</span>
            </a>

            <!-- Inventory Report -->
            <a href="{{ route('admin.reports.inventory') }}" class="report-card">
                <div class="report-icon">📦</div>
                <h2>Inventory Report</h2>
                <p>Track stock movements by date range</p>
                <span class="report-link">View Report →</span>
            </a>

            <!-- Franchisee Sales Report -->
            <a href="{{ route('admin.reports.franchisee-sales') }}" class="report-card">
                <div class="report-icon">🏪</div>
                <h2>Franchisee Sales</h2>
                <p>Compare sales performance per franchisee</p>
                <span class="report-link">View Report →</span>
            </a>
        </div>
    </div>
</div>

@endsection
