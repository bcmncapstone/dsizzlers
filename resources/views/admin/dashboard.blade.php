{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        
        <!-- Welcome Header -->
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <p>Summary of reports, operational data, trend line chart, and franchisee overview.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom: 16px;">
                <strong>✓</strong>
                {{ session('success') }}
            </div>
        @endif

        <!-- Summary of Reports -->
        <div class="sales-stats-grid">
            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Total Sales</div>
                    <div class="sales-stat-value">₱{{ number_format($totalSales ?? 0, 2) }}</div>
                </div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Sales This Month</div>
                    <div class="sales-stat-value">₱{{ number_format($salesThisMonth ?? 0, 2) }}</div>
                </div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Total Orders</div>
                    <div class="sales-stat-value">{{ intval($totalOrders ?? 0) }}</div>
                </div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Total Items Sold</div>
                    <div class="sales-stat-value">{{ intval($totalItemsSold ?? 0) }}</div>
                </div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Active Franchisees</div>
                    <div class="sales-stat-value">{{ intval($activeFranchisees ?? 0) }} / {{ intval($totalFranchisees ?? 0) }}</div>
                </div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Low + Out of Stock</div>
                    <div class="sales-stat-value">{{ intval($lowStockCount ?? 0) + intval($outOfStockCount ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Line Chart + Status Data -->
        <div class="sales-charts-grid">
            <div class="sales-chart-section">
                <h3 class="sales-chart-title">Sales Trend (Last 14 Days)</h3>
                <div class="sales-chart-container">
                    <canvas id="dashboardSalesTrendChart"></canvas>
                </div>
            </div>

            <div class="sales-chart-section">
                <h3 class="sales-chart-title">Order Status</h3>
                <div class="sales-table-overflow">
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>Order Status</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Pending</td>
                                <td>{{ intval($pendingOrders ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td>Preparing</td>
                                <td>{{ intval($preparingOrders ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td>Shipped</td>
                                <td>{{ intval($shippedOrders ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td>Delivered</td>
                                <td>{{ intval($deliveredOrders ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td>Cancelled</td>
                                <td>{{ intval($cancelledOrders ?? 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="sales-table-section" style="margin-bottom: 24px;">
            <div class="sales-table-header">
                <h3>Recent Orders Data</h3>
                <a href="{{ route('admin.manageOrder.index') }}" class="sales-table-pdf-btn">View All Orders</a>
            </div>

            <div class="sales-table-overflow">
                <table class="sales-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Order Status</th>
                            <th>Payment Status</th>
                            <th>Total</th>
                            <th>Order Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $dashboardRecentOrders = $recentOrders ?? collect(); @endphp
                        @forelse($dashboardRecentOrders as $order)
                            <tr>
                                <td>#{{ $order->order_id }}</td>
                                <td>{{ $order->name ?: 'N/A' }}</td>
                                <td>{{ ucfirst(strtolower($order->order_status ?? 'Pending')) }}</td>
                                <td>{{ ucfirst(strtolower($order->payment_status ?? 'Pending')) }}</td>
                                <td>₱{{ number_format($order->total_amount ?? 0, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="sales-table-empty">No recent orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="sales-table-section" style="margin-bottom: 24px;">
            <div class="sales-table-header">
                <h3>Quick Actions</h3>
            </div>
        </div>

        <!-- Dashboard Cards Grid -->
        <div class="card-grid">
            
            <!-- Create Account Card -->
            <a href="{{ route('accounts.create') }}" class="card card-orange">
                <div class="card-icon-wrapper">
                </div>
                <h3>Account</h3>
                <p>Add new user accounts and manage permissions</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Manage Contract Card -->
            <a href="{{ route('admin.branches.index') }}" class="card card-red">
                <div class="card-icon-wrapper">
                </div>
                <h3>Contract</h3>
                <p>View and manage contracts</p>
                <div class="card-arrow">View →</div>
            </a>

              <!-- Update Password Card -->
            <a href="{{ route('admin.password.update') }}" class="card card-orange">
                <div class="card-icon-wrapper">
                </div>
                <h3>Update Password</h3>
                <p>Secure your account with a new password</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Add Item Card -->
            <a href="{{ route('admin.items.index') }}" class="card card-yellow">
                <div class="card-icon-wrapper">
                </div>
                <h3>Item</h3>
                <p>View and manage items</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Manage Orders Card -->
            <a href="{{ route('admin.manageOrder.index') }}" class="card card-blue">
                <div class="card-icon-wrapper">
                </div>
                <h3>Order</h3>
                <p>Track and manage all customer orders</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Stock Management Card -->
            <a href="{{ route('admin.stock.index') }}" class="card card-purple">
                <div class="card-icon-wrapper">
                </div>
                <h3>Stock Management</h3>
                <p>Monitor inventory levels and stock transactions</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Reports Card -->
            <a href="{{ route('admin.reports.index') }}" class="card card-purple">
                <div class="card-icon-wrapper">
                </div>
                <h3>Report</h3>
                <p>View detailed analytics and business reports</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Messages Card -->
            <a href="{{ route('communication.index') }}" class="card card-green">
                <div class="card-icon-wrapper">
                </div>
                <h3>Message</h3>
                <p>Communicate with team and users in real-time</p>
                <div class="card-arrow">View →</div>
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const dashboardChartCanvas = document.getElementById('dashboardSalesTrendChart');

    if (dashboardChartCanvas) {
        const labels = @json($salesTrendLabels ?? []);
        const salesData = @json($salesTrendValues ?? []);
        const orderData = @json($salesTrendOrderCounts ?? []);

        new Chart(dashboardChartCanvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Sales (₱)',
                        data: salesData,
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.14)',
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'ySales',
                        borderWidth: 3,
                    },
                    {
                        label: 'Delivered Orders',
                        data: orderData,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.08)',
                        fill: false,
                        tension: 0.3,
                        yAxisID: 'yOrders',
                        borderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                },
                scales: {
                    ySales: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                    },
                    yOrders: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            precision: 0,
                            stepSize: 1,
                        }
                    }
                }
            }
        });
    }
</script>

@endsection
