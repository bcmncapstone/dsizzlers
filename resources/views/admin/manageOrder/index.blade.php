@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        <!-- Page Header -->
        <div class="page-header">
            <h1>Orders Management</h1>
            <p>Track and manage all customer orders</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success">
                <strong>✓</strong> {{ session('success') }}
            </div>
        @endif

        <div class="filter-section">
            <form method="GET" action="{{ route('admin.manageOrder.index') }}" class="filter-form">
                <div class="filter-group">
                    <label for="order_status" class="filter-label">Filter by Order Status</label>
                    <select name="order_status" id="order_status" class="filter-select">
                        <option value="">All Statuses</option>
                        @foreach($availableStatuses as $statusOption)
                            <option value="{{ $statusOption }}" {{ $selectedStatus === $statusOption ? 'selected' : '' }}>
                                {{ $statusOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="grid-column: 2;"></div>
                <button type="submit" class="btn btn-info">Apply Filter</button>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="table-section">
            <div class="table-section-header">
                <h2>All Orders</h2>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Order Status</th>
                            <th>Payment Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <div class="table-item-name">#{{ $order->order_id }}</div>
                                </td>
                                <td>
                                    <div class="table-item-name">{{ $order->name ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="table-category">{{ $order->contact ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="table-item-desc">{{ $order->address ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    @php
                                        $status = trim((string) ($order->order_status ?? 'Pending'));
                                        $statusLower = strtolower($status);
                                        $statusLower = $statusLower !== '' ? $statusLower : 'pending';
                                        $statusClass = in_array($statusLower, ['delivered', 'completed'], true)
                                            ? 'badge-success'
                                            : ($statusLower === 'cancelled' ? 'badge-danger' : 'badge-warning');
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ ucfirst($statusLower) }}</span>
                                </td>
                                <td>
                                    @php
                                        $paymentStatus = $order->payment_status ?? 'pending';
                                        $paymentClass = $paymentStatus === 'paid' ? 'badge-success' : ($paymentStatus === 'failed' ? 'badge-danger' : 'badge-warning');
                                    @endphp
                                    <span class="badge {{ $paymentClass }}">{{ ucfirst($paymentStatus) }}</span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.manageOrder.show', $order->order_id) }}" class="table-action-btn table-action-edit">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="table-empty">
                                    No orders found{{ $selectedStatus !== '' ? ' for the selected status.' : '.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection
