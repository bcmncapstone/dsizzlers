@extends('layouts.franchisee')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Sales Report</h1>
                    <p class="text-sm text-gray-600">Filter sales by date range.</p>
                </div>
                <a href="{{ route('franchisee.reports.index') }}" class="text-sm text-blue-600 hover:underline">Back to Reports</a>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-4">
            <form method="GET" action="{{ route('franchisee.reports.sales') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                <p class="text-sm text-yellow-700">No sales data found for the selected filters.</p>
                @if($availableRange && $availableRange->min_date && $availableRange->max_date)
                    <p class="text-xs text-yellow-600 mt-1">Available range: {{ \Carbon\Carbon::parse($availableRange->min_date)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($availableRange->max_date)->format('M d, Y') }}</p>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <p class="text-sm text-gray-500">Total Orders</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $totalOrders }}</p>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <p class="text-sm text-gray-500">Total Sales</p>
                <p class="text-2xl font-semibold text-gray-900">₱{{ number_format($totalSales, 2) }}</p>
            </div>
        </div>

        <!-- Charts Section -->
        @if(!$noData)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Top Selling Items Chart -->
            @if(count($topItems) > 0)
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Selling Items</h3>
                <div style="position: relative; height: 300px;">
                    <canvas id="topItemsChart"></canvas>
                </div>
            </div>
            @endif

            <!-- Sales by Category Chart -->
            @if(count($salesByCategory) > 0)
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales by Category</h3>
                <div style="position: relative; height: 300px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
            @endif
        </div>

        <!-- Daily Sales Trend Chart -->
        @if(count($dailySales) > 0)
        <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Daily Sales Trend</h3>
            <div style="position: relative; height: 300px;">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>
        @endif
        @endif

        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <div class="p-4 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">Sales Results</h2>
                <a href="{{ route('franchisee.reports.sales.pdf', request()->query()) }}" class="inline-flex items-center px-3 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase">Generate PDF</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item(s)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ordered By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($orders as $order)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $order->order_id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $order->item_names ?: '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $order->ordered_by }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">₱{{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No results.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
                <div class="p-4">
                    {{ $orders->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Top Selling Items Chart
    @if(!$noData && count($topItems) > 0)
    const topItemsCtx = document.getElementById('topItemsChart');
    if (topItemsCtx) {
        const topItemsData = @json($topItems);
        new Chart(topItemsCtx, {
            type: 'bar',
            data: {
                labels: topItemsData.map(item => item.name.substring(0, 20) + (item.name.length > 20 ? '...' : '')),
                datasets: [{
                    label: 'Quantity Sold',
                    data: topItemsData.map(item => item.quantity),
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
    @endif

    // Sales by Category Chart
    @if(!$noData && count($salesByCategory) > 0)
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        const categoryData = @json($salesByCategory);
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryData.map(cat => cat.category),
                datasets: [{
                    data: categoryData.map(cat => cat.sales),
                    backgroundColor: [
                        '#FF5722', '#FF7043', '#FF8A65', '#FFAB91', '#FFCCBC',
                        '#FF2D00', '#E74C3C', '#D35400', '#C23B1D', '#A93D20'
                    ],
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
    @endif

    // Daily Sales Trend Chart
    @if(!$noData && count($dailySales) > 0)
    const dailySalesCtx = document.getElementById('dailySalesChart');
    if (dailySalesCtx) {
        const dailyData = @json($dailySales);
        new Chart(dailySalesCtx, {
            type: 'line',
            data: {
                labels: dailyData.map(day => {
                    const date = new Date(day.date);
                    return (date.getMonth() + 1) + '/' + date.getDate();
                }),
                datasets: [{
                    label: 'Daily Sales (₱)',
                    data: dailyData.map(day => day.sales),
                    borderColor: '#FF5722',
                    backgroundColor: 'rgba(255, 87, 34, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#FF5722',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                    }
                }
            }
        });
    }
    @endif
</script>
@endsection
