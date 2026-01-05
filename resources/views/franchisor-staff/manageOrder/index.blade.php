@extends('layouts.franchisor-staff')

@section('content')
<div class="container">
    <h1>Manage Orders (Franchisor Staff)</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Contact</th>
                <th>Address</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Delivery</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>{{ $order->order_id }}</td>
                    <td>{{ $order->name }}</td>
                    <td>{{ $order->contact }}</td>
                    <td>{{ $order->address }}</td>
                    <td>{{ ucfirst($order->order_status) }}</td>
                    <td>{{ ucfirst($order->payment_status ?? 'pending') }}</td>
                    <td>{{ ucfirst($order->delivery_status ?? 'pending') }}</td>
                    <td>
                        <a href="{{ route('franchisor-staff.manageOrder.show', $order->order_id) }}" class="btn btn-primary btn-sm">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No orders found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
