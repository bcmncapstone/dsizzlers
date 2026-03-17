@extends('layouts.app')

@section('content')

<div class="py-6">
    <div class="max-w-5xl mx-auto">
        
        <!-- Header -->
        <div class="bg-white shadow-sm p-8 rounded-lg">
            @php
                $placedByType = ! empty($order->fstaff_id)
                    ? 'Franchisee Staff'
                    : (! empty($order->franchisee_id) ? 'Franchisee' : 'Unknown');

                $placedByName = '';
                if (! empty($order->fstaff_id)) {
                    $placedByName = trim(($order->franchiseeStaff->fstaff_fname ?? '') . ' ' . ($order->franchiseeStaff->fstaff_lname ?? ''));
                } elseif (! empty($order->franchisee_id)) {
                    $placedByName = (string) ($order->franchisee->franchisee_name ?? '');
                }
            @endphp
            <div class="flex justify-between items-start">
                <div>
                    <h1>Order #{{ $order->order_id }}</h1>
                    <p class="order-customer">{{ $order->name }}</p>
                    <p class="order-contact">Phone: {{ $order->contact }}</p>
                    <p class="order-address">Address: {{ $order->address }}</p>
                    <p class="order-placed-by">Placed by: {{ $placedByName }} ({{ $placedByType }})</p> 
                </div>
                <div class="header-badges">
                    @php
                        $orderStatus = $order->order_status ?? 'pending';
                        $statusClass = in_array(strtolower($orderStatus), ['delivered', 'completed']) ? 'status-delivered' : (strtolower($orderStatus) === 'shipped' || strtolower($orderStatus) === 'preparing' ? 'status-shipped' : 'status-pending');
                        $paymentStatus = $order->payment_status ?? 'pending';
                        $paymentClass = strtolower($paymentStatus) === 'paid' ? 'status-paid' : (strtolower($paymentStatus) === 'pending' ? 'status-pending-payment' : 'status-failed');
                        $canCancelOrder = $canCancelOrder ?? (
                            ! in_array(strtolower($paymentStatus), ['confirmed', 'paid'], true)
                            && ! in_array(strtolower($orderStatus), ['preparing', 'shipped', 'delivered', 'completed', 'cancelled'], true)
                        );
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ ucfirst($orderStatus) }}</span>
                    <span class="status-badge {{ $paymentClass }}">{{ ucfirst($paymentStatus) }}</span>
                </div>
            </div>
        </div>

        @if(session('success') || session('error') || session('info'))
            <div class="mt-6 space-y-3">
                @if(session('success'))
                    <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-green-700 js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-700 js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">{{ session('error') }}</div>
                @endif
                @if(session('info'))
                    <div class="rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-blue-700 js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">{{ session('info') }}</div>
                @endif
            </div>
        @endif

        <!-- Payment Proof Section -->
        <div class="bg-white shadow-sm p-8 rounded-lg mt-6">
            <h2>Payment Proof</h2>
            @if($order->payment_receipt)
                <div class="receipt-container">
                    <img src="{{ media_url($order->payment_receipt) }}" alt="Payment Receipt" class="receipt-image">
                </div>
            @else
                <div class="no-receipt-message">
                    No payment receipt uploaded
                </div>
            @endif
        </div>

        <!-- Ordered Items Section -->
        <div class="bg-white shadow-sm p-8 rounded-lg mt-6">
            <h2>Ordered Items</h2>

            @if($order->orderDetails->isNotEmpty())
                <div class="overflow-x-auto mt-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->orderDetails as $detail)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->item->item_name ?? 'Item Deleted' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">₱{{ number_format($detail->price, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ $detail->quantity }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-orange-600 text-right">₱{{ number_format($detail->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 text-right">
                    <p class="text-base font-semibold text-gray-900">Total Amount:
                        <span class="text-orange-600">₱{{ number_format($order->total_amount, 2) }}</span>
                    </p>
                </div>
            @else
                <p class="mt-4 text-sm text-gray-500">No item details found for this order.</p>
            @endif
        </div>

        <!-- Actions Section -->
        <div class="bg-white shadow-sm p-8 rounded-lg mt-6">
            <h2>Actions</h2>
            <div class="actions-grid">
                
                <!-- Confirm Payment -->
                <form action="{{ route('admin.manageOrder.confirmPayment', $order->order_id) }}" method="POST" class="action-form">
                    @csrf
                    <button type="submit" class="action-button confirm-payment">
                        ✓ Confirm Payment
                    </button>
                </form>

                <!-- Update Status -->
                <form action="{{ route('admin.manageOrder.updateOrderStatus', $order->order_id) }}" method="POST" class="action-form">
                    @csrf
                    <select name="order_status" onchange="this.form.submit()" class="status-select">
                        <option value="">Update Order Status</option>
                        <option value="Pending" {{ ($order->order_status ?? 'Pending') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Preparing" {{ ($order->order_status ?? '') == 'Preparing' ? 'selected' : '' }}>Preparing</option>
                        <option value="Shipped" {{ ($order->order_status ?? '') == 'Shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="Delivered" {{ ($order->order_status ?? '') == 'Delivered' ? 'selected' : '' }}>Delivered</option>
                    </select>
                </form>

                <!-- Cancel Order -->
                @if($canCancelOrder)
                    <form action="{{ route('admin.manageOrder.cancel', $order->order_id) }}" method="POST" class="action-form">
                        @csrf
                        <button type="submit" class="action-button cancel-order" onclick="return confirm('Are you sure you want to cancel this order?');">
                            ✕ Cancel Order
                        </button>
                    </form>
                @else
                    <div class="action-form">
                        <button type="button" class="action-button cancel-order opacity-50 cursor-not-allowed" disabled>
                            ✕ Cancel Order
                        </button>
                        <p class="mt-2 text-xs text-gray-500">
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Notes Section -->
        <div class="bg-white shadow-sm p-8 rounded-lg mt-6">
            <h2>Order Notes</h2>
            <form action="{{ route('admin.manageOrder.updateNotes', $order->order_id) }}" method="POST" class="notes-form">
                @csrf
                <textarea name="order_notes" class="notes-textarea" placeholder="Tracking number, delivery instructions...">{{ old('order_notes', $order->order_notes) }}</textarea>
                <button type="submit" class="save-button">Save Notes</button>
            </form>
        </div>

    </div>
</div>

<script>
    document.querySelectorAll('.js-flash-alert').forEach(function(alertEl) {
        const timeout = parseInt(alertEl.dataset.timeout || '3000', 10);

        setTimeout(function() {
            alertEl.style.transition = 'opacity 0.4s ease';
            alertEl.style.opacity = '0';

            setTimeout(function() {
                alertEl.remove();
            }, 400);
        }, Number.isFinite(timeout) ? timeout : 3000);
    });
</script>

@endsection
