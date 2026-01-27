@extends('layouts.franchisee-staff')

@section('content')
<div class="container">
    <h2 class="mb-4">Order Details (Franchisee Staff)</h2>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Order ID:</strong> {{ $order->order_id }}</p>
            <p><strong>Name:</strong> {{ $order->name }}</p>
            <p><strong>Contact:</strong> {{ $order->contact }}</p>
            <p><strong>Address:</strong> {{ $order->address }}</p>
            <p><strong>Order Status:</strong> 
                <span class="badge bg-{{ $order->order_status == 'Delivered' ? 'success' : ($order->order_status == 'Cancelled' ? 'danger' : 'warning') }}">
                    {{ ucfirst($order->order_status) }}
                </span>
            </p>
            <p><strong>Payment Status:</strong> 
                <span class="badge bg-{{ $order->payment_status == 'confirmed' ? 'success' : 'warning' }}">
                    {{ ucfirst($order->payment_status ?? 'pending') }}
                </span>
            </p>
            <p><strong>Total Amount:</strong> ₱{{ number_format($order->total_amount, 2) }}</p>

            @if($order->order_notes)
                <div class="alert alert-info mt-3">
                    <strong>Order Notes / Tracking Info:</strong><br>
                    {{ $order->order_notes }}
                </div>
            @endif

            @if($order->payment_receipt)
                <div class="mt-3">
                    <strong>Payment Receipt:</strong><br>
                    <img src="{{ asset('storage/' . $order->payment_receipt) }}" alt="Payment Receipt" style="max-width: 300px;" class="img-thumbnail">
                </div>
            @endif
        </div>
    </div>

    <hr>
    <h4>Ordered Items</h4>
    <table class="table table-bordered">
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
                    <td>₱{{ number_format($detail->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('franchisee_staff.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
</div>
@endsection
