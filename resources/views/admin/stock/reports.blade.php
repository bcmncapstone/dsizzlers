@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
            <div class="p-4 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Stock Reports</h1>
                        <p class="mt-2 text-sm text-gray-600">
                            View inventory reports with date filtering
                        </p>
                    </div>
                    <a href="{{ route('admin.stock.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Stock
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden mb-4">
            <div class="p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Reports</h3>
                
                <!-- Quick Date Presets -->
                <div class="mb-4 flex flex-wrap gap-2">
                    <span class="text-sm font-medium text-gray-700 mr-2">Quick Filters:</span>
                    <button type="button" onclick="setDateFilter('today')" 
                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-md transition">
                        Today
                    </button>
                    <button type="button" onclick="setDateFilter('yesterday')" 
                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-md transition">
                        Yesterday
                    </button>
                    <button type="button" onclick="setDateFilter('last7days')" 
                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-md transition">
                        Last 7 Days
                    </button>
                    <button type="button" onclick="setDateFilter('last30days')" 
                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-md transition">
                        Last 30 Days
                    </button>
                    <button type="button" onclick="setDateFilter('thismonth')" 
                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-md transition">
                        This Month
                    </button>
                    <button type="button" onclick="clearDateFilter()" 
                            class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 text-sm rounded-md transition">
                        Clear Dates
                    </button>
                </div>
                
                <form method="GET" action="{{ route('admin.stock.reports') }}" id="reportFilterForm" class="mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="franchisee_id" class="block text-sm font-medium text-gray-700 mb-1">Franchisee</label>
                            <select name="franchisee_id" 
                                    id="franchisee_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Franchisees</option>
                                @foreach($franchisees as $franchisee)
                                    <option value="{{ $franchisee->franchisee_id }}" 
                                            {{ request('franchisee_id') == $franchisee->franchisee_id ? 'selected' : '' }}>
                                        {{ $franchisee->franchisee_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
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
                        <div class="flex items-end">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Apply Filter
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Error Messages -->
                @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-red-400 p-3 mb-4">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                @endif

                <!-- No Data Message -->
                @if($noData && (request('franchisee_id') || request('start_date') || request('end_date')))
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4">
                        <p class="text-sm text-yellow-700">
                            No inventory records found for the selected filters.
                        </p>
                    </div>
                @endif
                
                <!-- Transaction History Table (INSIDE SAME CARD) -->
                <h2 class="text-lg font-semibold text-gray-900 mb-2 mt-3">Stock Transaction History</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Franchisee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance After</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($transactions as $transaction)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $transaction->created_at->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $transaction->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $transaction->franchisee->franchisee_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $transaction->franchisee->franchisee_address }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $transaction->item->item_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $transaction->item->item_category }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($transaction->transaction_type === 'in')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Stock In
                                            </span>
                                        @elseif($transaction->transaction_type === 'out')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Stock Out
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Adjustment
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold {{ $transaction->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->quantity >= 0 ? '+' : '' }}{{ $transaction->quantity }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-semibold">{{ $transaction->balance_after }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500">{{ $transaction->notes ?? '-' }}</div>
                                        @if($transaction->reference_type)
                                            <div class="text-xs text-gray-400 mt-1">
                                                Ref: {{ ucfirst($transaction->reference_type) }}
                                                @if($transaction->reference_id)
                                                    #{{ $transaction->reference_id }}
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                        @if($noData)
                                            No transaction records found. Please adjust your filters.
                                        @else
                                            No stock transactions have been recorded yet.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($transactions->hasPages())
                    <div class="mt-6">
                        {{ $transactions->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function setDateFilter(preset) {
    const today = new Date();
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    let startDate, endDate;
    
    switch(preset) {
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
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            startDate = formatDate(firstDay);
            endDate = formatDate(today);
            break;
    }
    
    startDateInput.value = startDate;
    endDateInput.value = endDate;
    
    // Auto-submit the form
    document.getElementById('reportFilterForm').submit();
}

function clearDateFilter() {
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
    document.getElementById('reportFilterForm').submit();
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}
</script>
@endsection
