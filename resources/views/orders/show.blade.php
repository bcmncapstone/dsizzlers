@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Order #{{ $order->id }}</h2>
    <p>Status: {{ ucfirst($order->status) }}</p>
    <p>Total: ₱{{ number_format($order->total_amount, 2) }}</p>

    <h4>Items</h4>
    <ul>
        @foreach ($order->orderDetails as $detail)
            <li>
                {{ $detail->item->name }} - 
                {{ $detail->quantity }} × ₱{{ number_format($detail->price, 2) }}
            </li>
        @endforeach
    </ul>
</div>
@endsection
