@extends('layouts.app')

@section('content')

<div class="inventory-page-wrapper">
    <div class="inventory-page-container">
        <!-- Header -->
        <div class="inventory-header">
            <div>
                <h1>Inventory Report</h1>
                <p>Check current inventory levels and stock status.</p>
            </div>
            <a href="{{ route('admin.reports.index') }}" class="inventory-back-link">← Back to Reports</a>
        </div>

        <!-- Summary Stats -->
        <div class="inventory-stats-grid">
            <div class="inventory-stat-card">
                <div class="inventory-stat-content">
                    <div class="inventory-stat-label">Total Items</div>
                    <div class="inventory-stat-value">{{ $items->count() }}</div>
                </div>
            </div>

            <div class="inventory-stat-card">
                <div class="inventory-stat-content">
                    <div class="inventory-stat-label">Total Quantity</div>
                    <div class="inventory-stat-value">{{ number_format($totalQuantity) }}</div>
                </div>
            </div>

            <div class="inventory-stat-card">
                <div class="inventory-stat-content">
                    <div class="inventory-stat-label">Inventory Value</div>
                    <div class="inventory-stat-value">₱{{ number_format($totalValue, 2) }}</div>
                </div>
            </div>

            <div class="inventory-stat-card">
                <div class="inventory-stat-content">
                    <div class="inventory-stat-label">Low/Out Stock</div>
                    <div class="inventory-stat-value" style="color: var(--danger-color);">{{ $lowStock->count() + $outOfStock->count() }}</div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="inventory-charts-grid">
            <!-- Stock Status Pie Chart -->
            <div class="inventory-chart-section">
                <h2 class="inventory-chart-title">Stock Status Distribution</h2>
                <div class="inventory-chart-container">
                    <canvas id="stockStatusChart"></canvas>
                </div>
                <div class="inventory-legend">
                    <div class="inventory-legend-item">
                        <span class="inventory-legend-label" style="color: #10b981;">In Stock (>10)</span>
                        <span class="inventory-legend-value">{{ $inStock->count() }} items</span>
                    </div>
                    <div class="inventory-legend-item">
                        <span class="inventory-legend-label" style="color: #f59e0b;">Low Stock (1-10)</span>
                        <span class="inventory-legend-value">{{ $lowStock->count() }} items</span>
                    </div>
                    <div class="inventory-legend-item">
                        <span class="inventory-legend-label" style="color: #ef4444;">Out of Stock (0)</span>
                        <span class="inventory-legend-value">{{ $outOfStock->count() }} items</span>
                    </div>
                </div>
            </div>

            <!-- Top Items by Stock -->
            <div class="inventory-chart-section">
                <h2 class="inventory-chart-title">Top 5 Items by Stock</h2>
                <div class="inventory-chart-container">
                    <canvas id="topItemsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        @if($lowStock->count() > 0 || $outOfStock->count() > 0)
        <div class="inventory-alert">
            <h3>⚠️ Stock Alert</h3>
            <p>You have <strong>{{ $lowStock->count() }}</strong> item(s) with low stock and <strong>{{ $outOfStock->count() }}</strong> item(s) out of stock.</p>
        </div>
        @endif

        <!-- Stock Batch Visibility -->
        <div class="inventory-table-section">
            <div class="inventory-table-header">
                <h2 class="inventory-table-title">Stock Batch Breakdown</h2>
            </div>

            <form method="GET" action="{{ route('admin.reports.inventory') }}" style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 14px;">
                <label for="fifo_item_id" style="font-weight: 600; color: #111827;">Select Item</label>
                <select id="fifo_item_id" name="fifo_item_id" onchange="this.form.submit()" style="min-width: 280px; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                    @forelse($fifoFilterItems as $filterItem)
                        <option value="{{ $filterItem->item_id }}" {{ (int) $selectedFifoItemId === (int) $filterItem->item_id ? 'selected' : '' }}>
                            {{ $filterItem->item_name }} (Current: {{ $filterItem->stock_quantity }})
                        </option>
                    @empty
                        <option value="">No items available</option>
                    @endforelse
                </select>
                <noscript>
                    <button type="submit" class="inventory-pdf-btn">Apply</button>
                </noscript>
            </form>

            @if($fifoSnapshot)
                @php $hasFifoMismatch = $fifoSnapshot['stock_quantity'] !== $fifoSnapshot['fifo_available']; @endphp

                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 16px; margin-bottom: 14px; font-size: 14px;">
                    <div><strong>Item:</strong> {{ $fifoSnapshot['item_name'] }}</div>
                    <div><strong>Current Stock:</strong> {{ $fifoSnapshot['stock_quantity'] }}</div>
                    <div><strong>Tracked Available:</strong> {{ $fifoSnapshot['fifo_available'] }}</div>
                    @if($hasFifoMismatch)
                        <div style="display: inline-flex; align-items: center; gap: 6px; background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; border-radius: 6px; padding: 5px 10px; font-size: 12px; font-weight: 600;">
                            ⚠ Mismatch — current stock ({{ $fifoSnapshot['stock_quantity'] }}) does not match tracked total ({{ $fifoSnapshot['fifo_available'] }}). Some batches may not be recorded yet.
                        </div>
                    @endif
                </div>

                <div class="inventory-overflow">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Batch Type</th>
                                <th>Batch #</th>
                                <th>Date Received</th>
                                <th>Remaining Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fifoSnapshot['lots'] as $lot)
                                <tr>
                                    <td>{{ ($lot['source'] ?? 'stock_in') === 'legacy_balance' ? 'Opening Stock' : 'Restocked' }}</td>
                                    <td>{{ $lot['stock_in_id'] ?? '-' }}</td>
                                    <td>
                                        @if($lot['received_date'])
                                            {{ \Illuminate\Support\Carbon::parse($lot['received_date'])->format('M d, Y H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td style="font-weight: 600;">{{ $lot['quantity_remaining'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="inventory-table-empty">No batch records found for this item.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- All Items Table -->
        <div class="inventory-table-section">
            <div class="inventory-table-header">
                <h2 class="inventory-table-title">All Items Inventory</h2>
                <a href="{{ route('admin.reports.inventory.pdf') }}" class="inventory-pdf-btn">📄 Generate PDF</a>
            </div>
            <div class="inventory-overflow">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Unit Price</th>
                            <th>Stock Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->item_category ?? '-' }}</td>
                                <td style="font-weight: 600;">{{ $item->stock_quantity }}</td>
                                <td>₱{{ number_format($item->price, 2) }}</td>
                                <td>₱{{ number_format($item->stock_quantity * $item->price, 2) }}</td>
                                <td>
                                    @if($item->stock_quantity > 10)
                                        <span class="inventory-status-badge inventory-status-in-stock">In Stock</span>
                                    @elseif($item->stock_quantity > 0)
                                        <span class="inventory-status-badge inventory-status-low-stock">Low Stock</span>
                                    @else
                                        <span class="inventory-status-badge inventory-status-out-stock">Out of Stock</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="inventory-table-empty">No items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Stock Status Pie Chart
    const stockStatusCtx = document.getElementById('stockStatusChart').getContext('2d');
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
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Top Items Bar Chart
    const topItemsCtx = document.getElementById('topItemsChart').getContext('2d');
    new Chart(topItemsCtx, {
        type: 'bar',
        data: {
            labels: [
                @foreach($topItems->take(5) as $item)
                    '{{ substr($item->item_name, 0, 15) }}{{ strlen($item->item_name) > 15 ? "..." : "" }}',
                @endforeach
            ],
            datasets: [{
                label: 'Stock Quantity',
                data: [
                    @foreach($topItems->take(5) as $item)
                        {{ $item->stock_quantity }},
                    @endforeach
                ],
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
</script>

@endsection
