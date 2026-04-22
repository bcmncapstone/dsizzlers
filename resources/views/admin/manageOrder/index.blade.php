@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        <!-- Page Header -->
        <div class="page-header">
            <h1>Order Management</h1>
            <p>Track and manage all customer order</p>
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
                    <select name="order_status" id="order_status" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        @foreach($availableStatuses as $statusOption)
                            <option value="{{ $statusOption }}" {{ $selectedStatus === $statusOption ? 'selected' : '' }}>
                                {{ $statusOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="payment_status" class="filter-label">Filter by Payment Status</label>
                    <select name="payment_status" id="payment_status" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Payment Statuses</option>
                        @foreach($availablePaymentStatuses as $paymentStatusOption)
                            <option value="{{ $paymentStatusOption }}" {{ $selectedPaymentStatus === $paymentStatusOption ? 'selected' : '' }}>
                                {{ $paymentStatusOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="per_page" class="filter-label">Orders per page</label>
                    <select name="per_page" id="per_page" class="filter-select" onchange="this.form.submit()">
                        <option value="10" {{ $selectedPerPage === '10' ? 'selected' : '' }}>10</option>
                        <option value="all" {{ $selectedPerPage === 'all' ? 'selected' : '' }}>All</option>
                    </select>
                </div>
            </form>
        </div>

        @if(!empty($selectedStatus) || !empty($selectedPaymentStatus))
            <div class="alert alert-info">
                <strong>Filter:</strong>
                Showing
                <strong>{{ $selectedStatus !== '' ? $selectedStatus : 'All order statuses' }}</strong>
                and
                <strong>{{ $selectedPaymentStatus !== '' ? $selectedPaymentStatus : 'All payment statuses' }}</strong>.
                <a href="{{ route('admin.manageOrder.index') }}" style="margin-left: 8px;">Clear</a>
            </div>
        @endif

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
                            <th>Ordered At</th> 
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
                <div class="table-date">
                    {{ $order->created_at ? $order->created_at->format('M d, Y h:i A') : 'N/A' }}
                </div>
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
                                <td colspan="8" class="table-empty">
                                    No orders found{{ $selectedStatus !== '' ? ' for the selected status.' : '.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="app-pagination-wrap">
                    {{ $orders->onEachSide(1)->links('vendor.pagination.orders') }}
                </div>
            @endif
        </div>

    </div>
</div>

@endsection
