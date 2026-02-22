@extends('layouts.app')

@section('content')

<div class="py-6">
    <div class="max-w-5xl mx-auto">
        
        <!-- Header -->
        <div class="bg-white shadow-sm p-8 rounded-lg">
            <div class="flex justify-between items-start">
                <div>
                    <h1>Order #{{ $order->order_id }}</h1>
                    <p class="order-customer">{{ $order->name }}</p>
                    <p class="order-contact">Phone: {{ $order->contact }}</p>
                    <p class="order-address">Address: {{ $order->address }}</p>
                </div>
                <div class="header-badges">
                    @php
                        $orderStatus = $order->order_status ?? 'pending';
                        $statusClass = in_array(strtolower($orderStatus), ['delivered', 'completed']) ? 'status-delivered' : (strtolower($orderStatus) === 'shipped' || strtolower($orderStatus) === 'preparing' ? 'status-shipped' : 'status-pending');
                        $paymentStatus = $order->payment_status ?? 'pending';
                        $paymentClass = strtolower($paymentStatus) === 'paid' ? 'status-paid' : (strtolower($paymentStatus) === 'pending' ? 'status-pending-payment' : 'status-failed');
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ ucfirst($orderStatus) }}</span>
                    <span class="status-badge {{ $paymentClass }}">{{ ucfirst($paymentStatus) }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Proof Section -->
        <div class="bg-white shadow-sm p-8 rounded-lg mt-6">
            <h2>Payment Proof</h2>
            @if($order->payment_receipt)
                <div class="receipt-container">
                    <img src="{{ asset('storage/' . $order->payment_receipt) }}" alt="Payment Receipt" class="receipt-image">
                </div>
            @else
                <div class="no-receipt-message">
                    No payment receipt uploaded
                </div>
            @endif
        </div>

        <!-- Actions Section -->
        <div class="bg-white shadow-sm p-8 rounded-lg mt-6">
            <h2>Actions</h2>
            <div class="actions-grid">
                
                <!-- Confirm Payment -->
                <form action="{{ route('admin.manageOrder.confirmPayment', $order->order_id) }}" method="POST" class="action-form">
                    @csrf
                    <button type="submit" class="action-button confirm-payment">
                        ✓ Confirm Payment
                    </button>
                </form>

                <!-- Update Status -->
                <form action="{{ route('admin.manageOrder.updateOrderStatus', $order->order_id) }}" method="POST" class="action-form">
                    @csrf
                    <select name="order_status" onchange="this.form.submit()" class="status-select">
                        <option value="">Update Order Status</option>
                        <option value="Pending" {{ ($order->order_status ?? 'Pending') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Preparing" {{ ($order->order_status ?? '') == 'Preparing' ? 'selected' : '' }}>Preparing</option>
                        <option value="Shipped" {{ ($order->order_status ?? '') == 'Shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="Delivered" {{ ($order->order_status ?? '') == 'Delivered' ? 'selected' : '' }}>Delivered</option>
                    </select>
                </form>

                <!-- Cancel Order -->
                <form action="{{ route('admin.manageOrder.cancel', $order->order_id) }}" method="POST" class="action-form">
                    @csrf
                    <button type="submit" class="action-button cancel-order" onclick="return confirm('Are you sure you want to cancel this order?');">
                        ✕ Cancel Order
                    </button>
                </form>
            </div>
        </div>

        <!-- Notes Section -->
        <div class="bg-white shadow-sm p-8 rounded-lg mt-6">
            <h2>Order Notes</h2>
            <form action="{{ route('admin.manageOrder.updateNotes', $order->order_id) }}" method="POST" class="notes-form">
                @csrf
                <textarea name="order_notes" class="notes-textarea" placeholder="Tracking number, delivery instructions...">{{ old('order_notes', $order->order_notes) }}</textarea>
                <button type="submit" class="save-button">Save Notes</button>
            </form>
        </div>

    </div>
</div>

@endsection
