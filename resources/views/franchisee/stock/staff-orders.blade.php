@extends('layouts.franchisee')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
            <div class="p-4 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center flex-wrap gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Staff Orders & Stock Impact</h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Track your staff's orders and how they affect your inventory
                        </p>
                    </div>
                    <a href="{{ route('franchisee.stock.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Inventory
                    </a>
                </div>
            </div>
        </div>

        <!-- Date Filter Card -->
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden mb-4">
            <div class="p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter by Date</h3>
                <form method="GET" action="{{ route('franchisee.stock.staff-orders') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" 
                               name="start_date" 
                               id="start_date" 
                               value="{{ request('start_date') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" 
                               name="end_date" 
                               id="end_date" 
                               value="{{ request('end_date') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" 
                                class="flex-1 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Filter
                        </button>
                        <a href="{{ route('franchisee.stock.staff-orders') }}" 
                           class="px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Clear
                        </a>
                    </div>
                </form>

                <!-- Quick Date Filters -->
                <div class="mt-4 flex flex-wrap gap-2">
                    <button onclick="setDateFilter('today')" 
                            class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300">
                        Today
                    </button>
                    <button onclick="setDateFilter('yesterday')" 
                            class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300">
                        Yesterday
                    </button>
                    <button onclick="setDateFilter('last7days')" 
                            class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300">
                        Last 7 Days
                    </button>
                    <button onclick="setDateFilter('last30days')" 
                            class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300">
                        Last 30 Days
                    </button>
                    <button onclick="setDateFilter('thismonth')" 
                            class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300">
                        This Month
                    </button>
                </div>

                @if(session('error'))
                    <div class="mt-4 bg-red-50 border-l-4 border-red-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Information Notice -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Note:</strong> When staff orders are marked as "Delivered", the ordered quantities automatically merge with your franchisee stock inventory.
                    </p>
                </div>
            </div>
        </div>

        <!-- PENDING ORDERS SECTION -->
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden mb-4">
            <div class="p-4 bg-yellow-50 border-b border-yellow-200">
                <h2 class="text-xl font-bold text-gray-900">
                    <svg class="inline h-5 w-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    Pending Orders - Franchisee Staff
                </h2>
                <p class="text-sm text-gray-600 mt-1">Orders that are pending, preparing, or out for delivery</p>
            </div>
            <div class="p-4">
                @if($pendingOrders->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($pendingOrders as $order)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ $order->order_id }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                        {{ $order->franchiseeStaff ? $order->franchiseeStaff->fstaff_fname . ' ' . $order->franchiseeStaff->fstaff_lname : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <ul class="list-disc list-inside">
                                            @foreach($order->orderDetails as $detail)
                                                <li>{{ $detail->item->item_name }} (x{{ $detail->quantity }})</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($order->order_status === 'Pending') bg-yellow-100 text-yellow-800
                                            @elseif($order->order_status === 'Preparing') bg-blue-100 text-blue-800
                                            @elseif($order->order_status === 'Shipped') bg-purple-100 text-purple-800
                                            @endif">
                                            {{ $order->order_status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->created_at->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        ₱{{ number_format($order->total_amount, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $pendingOrders->appends(['delivered_page' => request('delivered_page')])->appends(request()->except('pending_page'))->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No Pending Orders</h3>
                        <p class="mt-1 text-sm text-gray-500">There are no pending orders from your staff at the moment.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- DELIVERED ORDERS SECTION -->
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <div class="p-4 bg-green-50 border-b border-green-200">
                <h2 class="text-xl font-bold text-gray-900">
                    <svg class="inline h-5 w-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Delivered Orders - Franchisee Staff
                </h2>
                <p class="text-sm text-gray-600 mt-1">These orders have been delivered and merged with your stock</p>
            </div>
            <div class="p-4">
                @if($deliveredOrders->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items Added to Stock</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivered Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($deliveredOrders as $order)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ $order->order_id }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                        {{ $order->franchiseeStaff ? $order->franchiseeStaff->fstaff_fname . ' ' . $order->franchiseeStaff->fstaff_lname : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <ul class="list-disc list-inside">
                                            @foreach($order->orderDetails as $detail)
                                                <li>
                                                    <span class="font-medium">{{ $detail->item->item_name }}</span> 
                                                    <span class="text-green-600 font-semibold">(+{{ $detail->quantity }})</span>
                                                    - Added to inventory
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->updated_at->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        ₱{{ number_format($order->total_amount, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $deliveredOrders->appends(['pending_page' => request('pending_page')])->appends(request()->except('delivered_page'))->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No Delivered Orders</h3>
                        <p class="mt-1 text-sm text-gray-500">There are no delivered orders from your staff yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function setDateFilter(period) {
        const today = new Date();
        let startDate, endDate;

        switch(period) {
            case 'today':
                startDate = endDate = formatDate(today);
                break;
            case 'yesterday':
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                startDate = endDate = formatDate(yesterday);
                break;
            case 'last7days':
                const last7 = new Date(today);
                last7.setDate(last7.getDate() - 7);
                startDate = formatDate(last7);
                endDate = formatDate(today);
                break;
            case 'last30days':
                const last30 = new Date(today);
                last30.setDate(last30.getDate() - 30);
                startDate = formatDate(last30);
                endDate = formatDate(today);
                break;
            case 'thismonth':
                startDate = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
                endDate = formatDate(today);
                break;
        }

        document.getElementById('start_date').value = startDate;
        document.getElementById('end_date').value = endDate;
    }

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
</script>
@endsection
