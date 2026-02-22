@extends('layouts.franchisee')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Inventory Report</h1>
                    <p class="text-sm text-gray-600">Filter stock movements by date range.</p>
                </div>
                <a href="{{ route('franchisee.reports.index') }}" class="text-sm text-blue-600 hover:underline">Back to Reports</a>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-4">
            <form method="GET" action="{{ route('franchisee.reports.inventory') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-md text-xs font-semibold uppercase">Apply Filter</button>
                </div>
            </form>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-3 mb-4">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        @if($noData && (request('start_date') || request('end_date')))
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4">
                <p class="text-sm text-yellow-700">No inventory data found for the selected filters.</p>
                @if($availableRange && $availableRange->min_date && $availableRange->max_date)
                    <p class="text-xs text-yellow-600 mt-1">Available range: {{ \Carbon\Carbon::parse($availableRange->min_date)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($availableRange->max_date)->format('M d, Y') }}</p>
                @endif
            </div>
        @endif

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <p class="text-sm text-gray-500">Total Items</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $franchiseeItems->count() }}</p>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <p class="text-sm text-gray-500">Total Quantity</p>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalQuantity) }}</p>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <p class="text-sm text-gray-500">Inventory Value</p>
                <p class="text-2xl font-semibold text-gray-900">₱{{ number_format($totalValue, 2) }}</p>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <p class="text-sm text-gray-500">Low/Out Stock</p>
                <p class="text-2xl font-semibold text-red-600">{{ $lowStock->count() + $outOfStock->count() }}</p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Stock Status Pie Chart -->
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Stock Status Distribution</h3>
                <div style="position: relative; height: 300px;">
                    <canvas id="stockStatusChart"></canvas>
                </div>
            </div>

            <!-- Top Items Bar Chart -->
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Items by Stock</h3>
                <div style="position: relative; height: 300px;">
                    <canvas id="topItemsChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <div class="p-4 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">Inventory Movements</h2>
                <a href="{{ route('franchisee.reports.inventory.pdf', request()->query()) }}" class="inline-flex items-center px-3 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase">Generate PDF</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
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
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $transaction->item->item_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ ucfirst($transaction->transaction_type) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction->quantity }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction->balance_after }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No results.</td>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Stock Status Pie Chart
    const stockStatusCtx = document.getElementById('stockStatusChart');
    if (stockStatusCtx) {
        new Chart(stockStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['In Stock (>10)', 'Low Stock (1-10)', 'Out of Stock (0)'],
                datasets: [{
                    data: [{{ $inStock->count() }}, {{ $lowStock->count() }}, {{ $outOfStock->count() }}],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderColor: '#fff',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    // Top Items Bar Chart
    const topItemsCtx = document.getElementById('topItemsChart');
    if (topItemsCtx) {
        const topItemsData = @json($topItems);
        if (topItemsData && topItemsData.length > 0) {
            new Chart(topItemsCtx, {
                type: 'bar',
                data: {
                    labels: topItemsData.map(item => {
                        const name = item.item_name || 'Unknown';
                        return name.substring(0, 15) + (name.length > 15 ? '...' : '');
                    }),
                    datasets: [{
                        label: 'Stock Quantity',
                        data: topItemsData.map(item => parseInt(item.stock_quantity) || 0),
                        backgroundColor: '#FF5722',
                        borderColor: '#FF2D00',
                        borderWidth: 1,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                            }
                        }
                    }
                }
            });
        }
    }
</script>
@endsection
