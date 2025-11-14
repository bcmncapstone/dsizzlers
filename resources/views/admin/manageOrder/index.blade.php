@extends('layouts.app')

@section('content')

<div class="container">
    <h1>Franchisor (Admin) - Orders List</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Delivery</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->order_id }}</td>
                <td>{{ $order->name ?? 'N/A' }}</td>
                <td>{{ ucfirst($order->order_status ?? 'pending') }}</td>
                <td>{{ ucfirst($order->payment_status ?? 'pending') }}</td>
                <td>{{ ucfirst($order->delivery_status ?? 'pending') }}</td>
                <td>
                    <a href="{{ route('admin.manageOrder.show', $order->order_id) }}" class="btn btn-sm btn-primary">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
