@extends('layouts.franchisee-staff')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow-sm p-8 rounded-lg">
            <h1>Order Details</h1>
            <p>Review your order information and items</p>
        </div>

        <!-- Order Information -->
        <div class="bg-white shadow-sm p-8 rounded-lg mt-6">
            <h2>Order Information</h2>
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <p class="label">Order ID</p>
                    <p class="value">{{ $order->order_id }}</p>
                </div>
                <div>
                    <p class="label">Status</p>
                    <p class="value">
                        <span class="status-badge 
                            @if($order->order_status === 'Pending') status-pending
                            @elseif($order->order_status === 'Confirmed') status-confirmed
                            @elseif($order->order_status === 'Shipped') status-shipped
                            @elseif($order->order_status === 'Delivered') status-delivered
                            @elseif($order->order_status === 'Cancelled') status-cancelled
                            @endif">
                            {{ $order->order_status }}
                        </span>
                    </p>
                </div>
            </div>

            <hr class="my-6">

            <h3 class="mt-6">Customer Information</h3>
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <p class="label">Name</p>
                    <p class="value">{{ $order->name }}</p>
                </div>
                <div>
                    <p class="label">Contact</p>
                    <p class="value">{{ $order->contact }}</p>
                </div>
            </div>

            <div class="mt-6">
                <p class="label">Delivery Address</p>
                <p class="value">{{ $order->address }}</p>
            </div>
        </div>

        <!-- Payment Proof -->
        <div class="bg-white shadow-sm p-8 rounded-lg mt-6">
            <h2>Payment Proof</h2>

            @if($order->payment_receipt)
                <div class="mt-4">
                    <img
                        src="{{ media_url($order->payment_receipt) }}"
                        alt="Payment Receipt"
                        class="max-w-full rounded-lg border border-gray-200 shadow-sm"
                    >
                </div>
            @else
                <p class="mt-4 text-sm text-gray-500">No payment receipt uploaded.</p>
            @endif
        </div>

        <!-- Order Notes -->
        @if($order->order_notes)
        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg mt-6">
            <p class="label" style="color: #1e40af;">Order Notes</p>
            <p class="value" style="color: #1e40af;">{{ $order->order_notes }}</p>
        </div>
        @endif

        <!-- Order Items -->
        <div class="bg-white shadow-sm p-8 rounded-lg mt-6">
            <h2>Ordered Items</h2>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->orderDetails as $detail)
                        <tr>
                            <td>{{ $detail->item->item_name ?? 'Item Deleted' }}</td>
                            <td>₱{{ number_format($detail->price, 2) }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td class="subtotal">₱{{ number_format($detail->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="order-total mt-6">
                <p>Total Amount: <span>₱{{ number_format($order->total_amount, 2) }}</span></p>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-6">
            <a href="{{ route('franchisee_staff.orders.index') }}" class="back-link">← Back to Orders</a>
        </div>
    </div>
</div>
@endsection
