@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('admin.stock.reports') }}" class="btn btn-primary">
                View Reports
            </a>
            <a href="{{ route('admin.stock.index') }}" class="btn btn-gray">
                ← Back to Manage Stock
            </a>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1>Franchisee Inventory Overview</h1>
            <p>Monitor stock levels across all franchisee locations</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('admin.stock.franchisee-inventory') }}" class="filter-form">
                <div class="filter-group">
                    <label for="franchisee_id" class="filter-label">Filter by Franchisee</label>
                    <select name="franchisee_id" id="franchisee_id" class="filter-select">
                        <option value="">All Franchisees</option>
                        @foreach($franchisees as $franchisee)
                            <option value="{{ $franchisee->franchisee_id }}" {{ request('franchisee_id') == $franchisee->franchisee_id ? 'selected' : '' }}>
                                {{ $franchisee->franchisee_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="grid-column: 2;"></div>
                <button type="submit" class="btn btn-info">Apply Filter</button>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-content">
                    <dl>
                        <dt class="stat-label">Total Franchisees</dt>
                        <dd class="stat-value">{{ $totalFranchisees }}</dd>
                    </dl>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-content">
                    <dl>
                        <dt class="stat-label">Low Stock Items</dt>
                        <dd class="stat-value">{{ $totalLowStockItems }}</dd>
                    </dl>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-content">
                    <dl>
                        <dt class="stat-label">Out of Stock Items</dt>
                        <dd class="stat-value">{{ $totalOutOfStockItems }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Franchisee Stock Summary Table -->
        <div class="table-section">
            <div class="table-section-header">
                <h2>Franchisee Stock Summary</h2>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Franchisee</th>
                            <th>Location</th>
                            <th>Total Items</th>
                            <th>In Stock</th>
                            <th>Low Stock</th>
                            <th>Out of Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($franchiseeSummaries as $summary)
                            <tr>
                                <td>
                                    <div class="detail-item-name">{{ $summary['franchisee']->franchisee_name }}</div>
                                </td>
                                <td>
                                    <div class="detail-category">{{ $summary['franchisee']->franchisee_address }}</div>
                                </td>
                                <td>
                                    <div class="detail-quantity">{{ $summary['total_items'] }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-success">{{ $summary['in_stock'] }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-warning">{{ $summary['low_stock'] }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-danger">{{ $summary['out_of_stock'] }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.stock.show', $summary['franchisee']->franchisee_id) }}" class="table-action-btn table-action-edit">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="table-empty">
                                    No franchisee stock data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detailed Stock List (if filtered) -->
        @if(request('franchisee_id'))
            <div class="table-section" style="margin-top: var(--spacing-lg);">
                <div class="table-section-header">
                    <h3>Detailed Stock Inventory</h3>
                </div>

                <div class="table-responsive">
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Current Qty</th>
                                <th>Min Qty</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stocks as $stock)
                                <tr class="@if($stock->status === 'out_of_stock') row-out-of-stock @elseif($stock->status === 'low_stock') row-low-stock @else row-in-stock @endif">
                                    <td>
                                        <div class="detail-item-name">{{ $stock->item->item_name }}</div>
                                    </td>
                                    <td>
                                        <div class="detail-category">{{ $stock->item->item_category }}</div>
                                    </td>
                                    <td>
                                        <div class="detail-quantity">{{ $stock->current_quantity }}</div>
                                    </td>
                                    <td>
                                        <div class="detail-category">{{ $stock->minimum_quantity }}</div>
                                    </td>
                                    <td class="detail-status">
                                        @if($stock->status === 'out_of_stock')
                                            <span class="badge badge-danger">Out of Stock</span>
                                        @elseif($stock->status === 'low_stock')
                                            <span class="badge badge-warning">Low Stock</span>
                                        @else
                                            <span class="badge badge-success">In Stock</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="detail-updated">{{ $stock->updated_at->format('M d, Y') }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="table-empty">
                                        No stock records found for this franchisee.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</div>

@endsection
