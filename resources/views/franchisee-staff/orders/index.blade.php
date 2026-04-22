@extends('layouts.franchisee-staff')

@section('content')
<div class="orders-page">
    <div class="orders-container">

        <div class="page-header">
            <h1>Orders Management</h1>
            <p>Track and manage all my orders</p>
        </div>

        @if(session('success'))
            <div class="orders-success-alert js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="orders-error-alert js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">✕ {{ session('error') }}</div>
        @endif

        <div class="filter-section">
            <form method="GET" action="{{ route('franchisee_staff.orders.index') }}" class="filter-form">
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
                    <label for="per_page" class="filter-label">Orders per page</label>
                    <select name="per_page" id="per_page" class="filter-select" onchange="this.form.submit()">
                        <option value="10" {{ $selectedPerPage === '10' ? 'selected' : '' }}>10</option>
                        <option value="all" {{ $selectedPerPage === 'all' ? 'selected' : '' }}>All</option>
                    </select>
                </div>
            </form>
        </div>

        @if($selectedStatus !== '')
            <div class="alert alert-info">
                <strong>Filter:</strong> Showing <strong>{{ $selectedStatus }}</strong> orders.
                <a href="{{ route('franchisee_staff.orders.index', ['per_page' => $selectedPerPage]) }}" style="margin-left: 8px;">Clear</a>
            </div>
        @endif

        @if($orders->count() > 0)
            <h3 class="orders-section-title">All Orders</h3>
            <div class="orders-table-wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>
                                    <span class="orders-id">#{{ $order->order_id }}</span>
                                </td>
                                <td>
                                    <span class="orders-date">{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y h:i A') }}</span>
                                </td>
                                <td>
                                    <span class="orders-amount">PHP {{ number_format($order->total_amount, 2) }}</span>
                                </td>
                                <td>
                                    <span class="orders-status-badge orders-status-{{ Str::lower($order->order_status) }}">
                                        @if($order->order_status == 'Pending')
                                            Pending
                                        @elseif($order->order_status == 'Preparing')
                                            Preparing
                                        @elseif($order->order_status == 'Shipped')
                                            Shipped
                                        @elseif($order->order_status == 'Delivered')
                                            Delivered
                                        @elseif($order->order_status == 'Cancelled')
                                            Cancelled
                                        @else
                                            {{ $order->order_status }}
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('franchisee_staff.orders.show', $order->order_id) }}" class="orders-view-btn">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
                <div class="app-pagination-wrap">
                    {{ $orders->onEachSide(1)->links('vendor.pagination.orders') }}
                </div>
            @endif
        @else
            <div class="orders-empty-message">
                <p>You have no orders yet.</p>
            </div>
        @endif
    </div>
</div>

<script>
    document.querySelectorAll('.js-flash-alert').forEach(function(alertEl) {
        const timeout = parseInt(alertEl.dataset.timeout || '3000', 10);

        setTimeout(function() {
            alertEl.style.transition = 'opacity 0.4s ease';
            alertEl.style.opacity = '0';

            setTimeout(function() {
                alertEl.remove();
            }, 400);
        }, Number.isFinite(timeout) ? timeout : 3000);
    });
</script>
@endsection
