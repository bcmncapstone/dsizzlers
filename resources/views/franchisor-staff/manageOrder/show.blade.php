@extends('layouts.franchisor-staff')

@section('content')

<div class="container">
    <h1>Franchisor Staff - Order Details</h1>

<p><strong>Order ID:</strong> {{ $order->order_id }}</p>
<p><strong>Customer:</strong> {{ $order->name }}</p>
<p><strong>Status:</strong> {{ ucfirst($order->order_status) }}</p>
<p><strong>Payment:</strong> {{ ucfirst($order->payment_status ?? 'pending') }}</p>
<p><strong>Delivery:</strong> {{ ucfirst($order->delivery_status ?? 'pending') }}</p>

<div class="mt-3">
<form action="{{ route('franchisor-staff.manageOrder.confirmPayment', $order->order_id) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-success">Confirm Payment</button>
    </form>

    <form action="{{ route('franchisor-staff.manageOrder.updateDelivery', $order->order_id) }}" method="POST" class="d-inline">
        @csrf
        <select name="delivery_status" onchange="this.form.submit()" class="form-select d-inline w-auto">
            <option value="pending" {{ ($order->delivery_status ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="shipped" {{ ($order->delivery_status ?? '') == 'shipped' ? 'selected' : '' }}>Shipped</option>
            <option value="delivered" {{ ($order->delivery_status ?? '') == 'delivered' ? 'selected' : '' }}>Delivered</option>
        </select>
    </form>

    <form action="{{ route('franchisor-staff.manageOrder.cancel', $order->order_id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-danger">Cancel Order</button>
    </form>
</div>


</div>
@endsection
