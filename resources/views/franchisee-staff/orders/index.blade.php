@extends('layouts.franchisee-staff')

@section('content')
<div class="container">
    <h2 class="mb-4">My Orders (Franchisee Staff)</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($orders->count() > 0)
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->order_id }}</td>
                        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y h:i A') }}</td>
                        <td>₱{{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            <span class="badge 
                                @if($order->order_status == 'Pending') bg-warning 
                                @elseif($order->order_status == 'Approved') bg-success 
                                @elseif($order->order_status == 'Rejected') bg-danger 
                                @else bg-secondary @endif">
                                {{ $order->order_status }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('franchisee_staff.orders.show', $order->order_id) }}" class="btn btn-info btn-sm">
                                View
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>You have no orders yet.</p>
    @endif
</div>
@endsection