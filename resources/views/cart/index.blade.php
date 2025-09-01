@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Your Cart</h2>

    {{-- Success & Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php
        $prefix = strpos(Route::currentRouteName(), 'franchisee_staff.') === 0 ? 'franchisee_staff' : 'franchisee';
    @endphp

    @if($cart && count($cart) > 0)
        <form action="{{ route($prefix . '.cart.checkout') }}" method="POST">
            @csrf
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @foreach($cart as $id => $details)
                        @php 
                            $subtotal = $details['price'] * $details['quantity']; 
                            $total += $subtotal; 
                        @endphp
                        <tr>
                            <td>{{ $details['name'] }}</td>
                            <td>₱{{ number_format($details['price'], 2) }}</td>
                            <td>
                                {{-- Update Quantity --}}
                                <div class="d-flex">
                                    <form action="{{ route($prefix . '.cart.add', $id) }}" method="POST" class="d-flex me-2">
                                        @csrf
                                        <input type="number" name="quantity" value="{{ $details['quantity'] }}" 
                                               min="1" max="{{ $details['stock_quantity'] ?? 100 }}" 
                                               class="form-control me-2">
                                        <button type="submit" class="btn btn-secondary btn-sm">Update</button>
                                    </form>
                                </div>
                                <small class="text-muted">
                                    Available: {{ $details['stock_quantity'] ?? 'N/A' }}
                                </small>
                            </td>
                            <td>₱{{ number_format($subtotal, 2) }}</td>
                            <td>
                                {{-- Remove Button --}}
                                <form action="{{ route($prefix . '.cart.remove', $id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3"><strong>Total</strong></td>
                        <td><strong>₱{{ number_format($total, 2) }}</strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <button type="submit" class="btn btn-success">Proceed to Checkout</button>
        </form>
    @else
        <p>Your cart is empty.</p>
    @endif
</div>
@endsection
