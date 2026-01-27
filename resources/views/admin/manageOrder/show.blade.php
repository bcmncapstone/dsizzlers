@extends('layouts.app')

@section('content')

<div class="container">
    <h1>Franchisor (Admin) - Order Details</h1>

    <p><strong>Order ID:</strong> {{ $order->order_id }}</p>
    <p><strong>Customer:</strong> {{ $order->name }}</p>
    <p><strong>Payment Receipt:</strong></p>
@if($order->payment_receipt)
    <img src="{{ asset('storage/' . $order->payment_receipt) }}" alt="Payment Receipt" style="max-width: 300px; max-height: 300px;">
@else
    <p>No payment receipt uploaded.</p>
@endif
    <p><strong>Status:</strong> {{ ucfirst($order->order_status ?? 'Pending') }}</p>
    <p><strong>Payment:</strong> {{ ucfirst($order->payment_status ?? 'pending') }}</p>

    <div class="mt-3">
        <form action="{{ route('admin.manageOrder.confirmPayment', $order->order_id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success">Confirm Payment</button>
        </form>

        <form action="{{ route('admin.manageOrder.updateOrderStatus', $order->order_id) }}" method="POST" class="d-inline">
            @csrf
            <label for="order_status" class="me-2">Order Status:</label>
            <select name="order_status" onchange="this.form.submit()" class="form-select d-inline w-auto">
                <option value="Pending" {{ ($order->order_status ?? 'Pending') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Preparing" {{ ($order->order_status ?? '') == 'Preparing' ? 'selected' : '' }}>Preparing</option>
                <option value="Shipped" {{ ($order->order_status ?? '') == 'Shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="Delivered" {{ ($order->order_status ?? '') == 'Delivered' ? 'selected' : '' }}>Delivered</option>
            </select>
        </form>

        <form action="{{ route('admin.manageOrder.cancel', $order->order_id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-danger">Cancel Order</button>
        </form>
    </div>

    <div class="mt-4">
        <form action="{{ route('admin.manageOrder.updateNotes', $order->order_id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="order_notes" class="form-label"><strong>Order Notes (Tracking Number, etc.):</strong></label>
                <textarea name="order_notes" id="order_notes" class="form-control" rows="3" placeholder="Enter tracking number or other notes...">{{ old('order_notes', $order->order_notes) }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Notes</button>
        </form>
    </div>

</div>
@endsection
