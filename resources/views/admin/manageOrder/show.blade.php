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
    <p><strong>Status:</strong> {{ ucfirst($order->order_status ?? 'pending') }}</p>
    <p><strong>Payment:</strong> {{ ucfirst($order->payment_status ?? 'pending') }}</p>
    <p><strong>Delivery:</strong> {{ ucfirst($order->delivery_status ?? 'pending') }}</p>

    <div class="mt-3">
        <form action="{{ route('admin.manageOrder.confirmPayment', $order->order_id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success">Confirm Payment</button>
        </form>

        <form action="{{ route('admin.manageOrder.updateDelivery', $order->order_id) }}" method="POST" class="d-inline">
            @csrf
            <select name="delivery_status" onchange="this.form.submit()" class="form-select d-inline w-auto">
                <option value="pending" {{ ($order->delivery_status ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="shipped" {{ ($order->delivery_status ?? '') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ ($order->delivery_status ?? '') == 'delivered' ? 'selected' : '' }}>Delivered</option>
            </select>
        </form>

        <form action="{{ route('admin.manageOrder.cancel', $order->order_id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-danger">Cancel Order</button>
        </form>
    </div>

</div>
@endsection
