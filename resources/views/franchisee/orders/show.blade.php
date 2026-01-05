@extends('layouts.franchisee')

@section('content')
<div class="container">
    <h2 class="mb-4">Order Details (Franchisee)</h2>

    <p><strong>Order ID:</strong> {{ $order->order_id }}</p>
    <p><strong>Name:</strong> {{ $order->name }}</p>
    <p><strong>Contact:</strong> {{ $order->contact }}</p>
    <p><strong>Address:</strong> {{ $order->address }}</p>
    <p><strong>Status:</strong> {{ ucfirst($order->delivery_status ?? 'pending') }}</p>
    <p><strong>Total Amount:</strong> ₱{{ number_format($order->total_amount, 2) }}</p>

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
</div>
@endsection
