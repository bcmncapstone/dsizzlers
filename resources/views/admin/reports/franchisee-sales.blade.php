@extends('layouts.app')

@section('content')

<div class="franchisee-page-wrapper">
    <div class="franchisee-page-container">
        <!-- Header -->
        <div class="franchisee-header">
            <div>
                <h1>Franchisee Sales Report</h1>
                <p>Compare sales performance per franchisee.</p>
            </div>
            <a href="{{ route('admin.reports.index') }}">← Back to Reports</a>
        </div>

        <!-- Filter Section -->
        <div class="franchisee-filter-section">
            <form method="GET" action="{{ route('admin.reports.franchisee-sales') }}" class="franchisee-filter-form">
                <div class="franchisee-filter-group">
                    <label class="franchisee-filter-label">Franchisee</label>
                    <select name="franchisee_id" class="franchisee-filter-select">
                        <option value="">All Franchisees</option>
                        @foreach($franchisees as $franchisee)
                            <option value="{{ $franchisee->franchisee_id }}" {{ request('franchisee_id') == $franchisee->franchisee_id ? 'selected' : '' }}>
                                {{ $franchisee->franchisee_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="franchisee-filter-group">
                    <label class="franchisee-filter-label">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="franchisee-filter-input" />
                </div>
                <div class="franchisee-filter-group">
                    <label class="franchisee-filter-label">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="franchisee-filter-input" />
                </div>
                <button type="submit" class="franchisee-filter-btn">Apply Filter</button>
            </form>
        </div>

        <!-- Alerts -->
        @if(session('error'))
            <div class="franchisee-alert alert-danger">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if($noData && (request('franchisee_id') || request('start_date') || request('end_date')))
            <div class="franchisee-alert alert-warning">
                <p>No franchisee sales data found for the selected filters.</p>
                @if($availableRange && $availableRange->min_date && $availableRange->max_date)
                    <p class="franchisee-alert-range">Available range: {{ \Carbon\Carbon::parse($availableRange->min_date)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($availableRange->max_date)->format('M d, Y') }}</p>
                @endif
            </div>
        @endif

        @if(!$noData)
            <!-- Stat Cards -->
            <div class="franchisee-stats-grid">
                <div class="franchisee-stat-card">
                    <div class="franchisee-stat-content">
                        <div class="franchisee-stat-label">Total Franchisees</div>
                        <div class="franchisee-stat-value">{{ $rows->count() }}</div>
                    </div>
                    <div class="franchisee-stat-icon">🏪</div>
                </div>

                <div class="franchisee-stat-card">
                    <div class="franchisee-stat-content">
                        <div class="franchisee-stat-label">Total Orders</div>
                        <div class="franchisee-stat-value">{{ $totalOrders }}</div>
                    </div>
                    <div class="franchisee-stat-icon">📦</div>
                </div>

                <div class="franchisee-stat-card">
                    <div class="franchisee-stat-content">
                        <div class="franchisee-stat-label">Total Revenue</div>
                        <div class="franchisee-stat-value">₱{{ number_format($totalSales, 2) }}</div>
                    </div>
                    <div class="franchisee-stat-icon">💰</div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="franchisee-charts-grid">
                <!-- Top Franchisees by Revenue -->
                <div class="franchisee-chart-section">
                    <h3 class="franchisee-chart-title">🏆 Top Franchisees by Revenue</h3>
                    <div class="franchisee-chart-container">
                        <canvas id="topFranchiseesChart"></canvas>
                    </div>
                </div>

                <!-- Franchisee Market Share -->
                <div class="franchisee-chart-section">
                    <h3 class="franchisee-chart-title">📈 Franchisee Market Share</h3>
                    <div class="franchisee-chart-container">
                        <canvas id="marketShareChart"></canvas>
                    </div>
                </div>
            </div>
        @endif

        <!-- Franchisee Sales Table -->
        <div class="franchisee-table-section">
            <div class="franchisee-table-header">
                <h3>Franchisee Sales</h3>
                <a href="{{ route('admin.reports.franchisee-sales.pdf', request()->query()) }}" class="franchisee-table-pdf-btn">📄 Generate PDF</a>
            </div>
            <div class="franchisee-table-overflow">
                <table class="franchisee-table">
                    <thead>
                        <tr>
                            <th>Franchisee</th>
                            <th>Orders</th>
                            <th>Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $franchiseeMap[$row->franchisee_id]->franchisee_name ?? 'N/A' }}</td>
                                <td>{{ $row->orders_count }}</td>
                                <td class="franchisee-table-revenue">₱{{ number_format($row->total_sales, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="franchisee-table-empty">No results.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if(!$noData)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Top Franchisees by Revenue Chart
    const topFranchiseesCtx = document.getElementById('topFranchiseesChart').getContext('2d');
    const allChartRows = @json($chartRows);
    const topFranchisees = allChartRows.slice(0, 5);
    
    new Chart(topFranchiseesCtx, {
        type: 'bar',
        data: {
            labels: topFranchisees.map(f => f.name),
            datasets: [{
                label: 'Revenue',
                data: topFranchisees.map(f => f.sales),
                backgroundColor: '#FF5722',
                borderColor: '#E64A19',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Franchisee Market Share Chart
    const marketShareCtx = document.getElementById('marketShareChart').getContext('2d');
    const marketShareData = allChartRows;
    const colors = ['#FF5722', '#FF7043', '#FF8A65', '#FFAB91', '#FFCCBC', '#FF2D00', '#E74C3C', '#D35400'];
    
    new Chart(marketShareCtx, {
        type: 'doughnut',
        data: {
            labels: marketShareData.map(f => f.name),
            datasets: [{
                data: marketShareData.map(f => f.sales),
                backgroundColor: colors.slice(0, marketShareData.length),
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

</script>
@endif

@endsection
