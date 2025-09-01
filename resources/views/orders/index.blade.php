@extends('layouts.app')

@section('content')
<div class="container">
    <h2>My Orders</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Status</th>
                <th>Items</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>
                        <ul>
                            @foreach ($order->orderDetails as $detail)
                                <li>{{ $detail->item->name }} (x{{ $detail->quantity }})</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
