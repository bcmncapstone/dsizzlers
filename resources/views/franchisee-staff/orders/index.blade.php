@extends('layouts.franchisee-staff')

@section('content')
<div class="orders-page">
    <div class="orders-container">
        
       <!-- Page Header -->
        <div class="page-header">
            <h1>Orders Management</h1>
            <p>Track and manage all my orders</p>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="orders-success-alert">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="orders-error-alert">✕ {{ session('error') }}</div>
        @endif

        {{-- Orders Table or Empty Message --}}
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
                                    <span class="orders-amount">₱{{ number_format($order->total_amount, 2) }}</span>
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
                                        👁️ View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="orders-empty-message">
                <p>You have no orders yet 🛵</p>
            </div>
        @endif
    </div>
</div>
@endsection
