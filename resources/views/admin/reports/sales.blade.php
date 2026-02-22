@extends('layouts.app')

@section('content')

<div class="sales-page-wrapper">
    <div class="sales-page-container">
        <!-- Header -->
        <div class="sales-header">
            <div>
                <h1>Sales Report</h1>
                <p>Admin item sales to franchisees</p>
            </div>
            <a href="{{ route('admin.reports.index') }}">← Back to Reports</a>
        </div>

        <!-- Filter Section -->
        <div class="sales-filter-section">
            <form method="GET" action="{{ route('admin.reports.sales') }}" class="sales-filter-form">
                <div class="sales-filter-group">
                    <label for="start_date" class="sales-filter-label">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="sales-filter-input" />
                </div>
                <div class="sales-filter-group">
                    <label for="end_date" class="sales-filter-label">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="sales-filter-input" />
                </div>
                <button type="submit" class="sales-filter-btn">Apply Filter</button>
            </form>
        </div>

        <!-- Alerts -->
        @if(session('error'))
            <div class="sales-alert alert-danger">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if($noData && (request('start_date') || request('end_date')))
            <div class="sales-alert alert-warning">
                <p>No sales data found for the selected filters.</p>
                @if($availableRange && $availableRange->min_date && $availableRange->max_date)
                    <p class="sales-alert-range">Available range: {{ \Carbon\Carbon::parse($availableRange->min_date)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($availableRange->max_date)->format('M d, Y') }}</p>
                @endif
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="sales-stats-grid">
            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Total Items Sold</div>
                    <div class="sales-stat-value">{{ intval($totalQuantity ?? 0) }}</div>
                </div>
                <div class="sales-stat-icon">📦</div>
            </div>
            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Total Sales</div>
                    <div class="sales-stat-value">₱{{ number_format($totalSales, 2) }}</div>
                </div>
                <div class="sales-stat-icon">💰</div>
            </div>
        </div>

        <!-- Charts Section -->
        @if(!$noData)
        <div class="sales-charts-grid">
            <!-- Top Selling Items Chart -->
            <div class="sales-chart-section">
                <h3 class="sales-chart-title">Top Selling Items</h3>
                <div class="sales-chart-container">
                    <canvas id="topItemsChart"></canvas>
                </div>
            </div>

            <!-- Sales by Category Chart -->
            @if(count($salesByCategory) > 0)
            <div class="sales-chart-section">
                <h3 class="sales-chart-title">Sales by Category</h3>
                <div class="sales-chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
            @endif
        </div>

        <!-- Daily Sales Trend Chart -->
        @if(count($dailySales) > 0)
        <div class="sales-daily-chart">
            <h3 class="sales-chart-title">Daily Sales Trend</h3>
            <div class="sales-chart-container">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>
        @endif
        @endif

        <!-- Sales Results Table -->
        <div class="sales-table-section">
            <div class="sales-table-header">
                <h3>Sales Results</h3>
                <a href="{{ route('admin.reports.sales.pdf', request()->query()) }}" class="sales-table-pdf-btn">📄 Generate PDF</a>
            </div>
            <div class="sales-table-overflow">
                <table class="sales-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                            <th>Order Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orderDetails as $detail)
                            <tr>
                                <td class="table-item-name">#{{ $detail->order_id }}</td>
                                <td>{{ $detail->item_name }}</td>
                                <td>{{ $detail->quantity }}</td>
                                <td>₱{{ number_format($detail->price, 2) }}</td>
                                <td class="table-subtotal">₱{{ number_format($detail->subtotal, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($detail->order_date)->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="sales-table-empty">No sales records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orderDetails->hasPages())
                <div class="sales-pagination-wrapper">
                    {{ $orderDetails->appends(request()->query())->links() }}
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
                    backgroundColor: '#3b82f6',
                    borderColor: '#1e40af',
                    borderWidth: 1,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
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
                maintainAspectRatio: true,
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
                maintainAspectRatio: true,
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
