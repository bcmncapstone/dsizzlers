@extends('layouts.franchisee')

@section('content')
<div class="reports-page">
    <div class="reports-container">
        {{-- Header with Orange Left Border --}}
        <div class="reports-header-box">
            <div class="reports-header">
                <h1 class="reports-title">Reports</h1>
                <p class="reports-subtitle">Generate and analyze business reports</p>
            </div>
        </div>

        {{-- Report Cards Grid --}}
        <div class="reports-grid">
            {{-- Sales Report Card --}}
            <a href="{{ route('franchisee.reports.sales') }}" class="reports-card">
                <h2 class="reports-card-title">Sales Report</h2>
                <p class="reports-card-description">Filter sales by date range and franchisee</p>
                <div class="reports-card-link">View Report →</div>
            </a>

            {{-- Inventory Report Card --}}
            <a href="{{ route('franchisee.reports.inventory') }}" class="reports-card">
                <h2 class="reports-card-title">Inventory Report</h2>
                <p class="reports-card-description">Track stock movements by date range</p>
                <div class="reports-card-link">View Report →</div>
            </a>

            {{-- Franchisee Sales Card --}}
            <a href="{{ route('franchisee.reports.staff') }}" class="reports-card">
                <h2 class="reports-card-title">Staff Report Sales</h2>
                <p class="reports-card-description">Compare sales performance per franchisee</p>
                <div class="reports-card-link">View Report →</div>
            </a>
        </div>
    </div>
</div>
@endsection
