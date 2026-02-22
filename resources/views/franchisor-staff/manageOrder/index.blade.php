@extends('layouts.franchisor-staff')

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
                                        $status = $order->order_status ?? 'pending';
                                        $statusClass = $status === 'completed' ? 'badge-success' : ($status === 'cancelled' ? 'badge-danger' : 'badge-warning');
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ ucfirst($status) }}</span>
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
                                        <a href="{{ route('franchisor-staff.manageOrder.show', $order->order_id) }}" class="table-action-btn table-action-edit">
                                            👁️ View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="table-empty">
                                    No orders found.
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
