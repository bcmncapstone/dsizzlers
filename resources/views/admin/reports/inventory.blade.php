@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Inventory Report</h1>
                    <p class="text-sm text-gray-600">Filter stock movements by franchisee and date range.</p>
                </div>
                <a href="{{ route('admin.reports.index') }}" class="text-sm text-blue-600 hover:underline">Back to Reports</a>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-4">
            <form method="GET" action="{{ route('admin.reports.inventory') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Franchisee</label>
                    <select name="franchisee_id" class="w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Franchisees</option>
                        @foreach($franchisees as $franchisee)
                            <option value="{{ $franchisee->franchisee_id }}" {{ request('franchisee_id') == $franchisee->franchisee_id ? 'selected' : '' }}>
                                {{ $franchisee->franchisee_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-md text-xs font-semibold uppercase">Apply Filter</button>
                </div>
            </form>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-3 mb-4">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        @if($noData && (request('franchisee_id') || request('start_date') || request('end_date')))
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4">
                <p class="text-sm text-yellow-700">No inventory data found for the selected filters.</p>
                @if($availableRange && $availableRange->min_date && $availableRange->max_date)
                    <p class="text-xs text-yellow-600 mt-1">Available range: {{ \Carbon\Carbon::parse($availableRange->min_date)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($availableRange->max_date)->format('M d, Y') }}</p>
                @endif
            </div>
        @endif

        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <div class="p-4 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">Inventory Movements</h2>
                <a href="{{ route('admin.reports.inventory.pdf', request()->query()) }}" class="inline-flex items-center px-3 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase">Generate PDF</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Franchisee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance After</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $transaction)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $transaction->franchisee->franchisee_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $transaction->item->item_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ ucfirst($transaction->transaction_type) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction->quantity }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction->balance_after }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No results.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="p-4">
                    {{ $transactions->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
