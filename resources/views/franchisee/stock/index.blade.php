@extends('layouts.franchisee')

@section('content')

<div class="inventory-page-wrapper admin-stock-page">
    <div class="inventory-page-container">

        <div class="inventory-header admin-stock-header">
            <div>
                <h1>Manage Stock</h1>
                <p>Monitor availability and adjust quantities</p>
            </div>
            <div class="admin-stock-header-actions">
                <a href="{{ route('franchisee.stock.staff-orders') }}" class="inventory-pdf-btn">
                    Staff Orders
                </a>
                <a href="{{ route('franchisee.stock.history') }}" class="inventory-pdf-btn admin-stock-btn-muted">
                    Stock History
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="admin-stock-alert admin-stock-alert-success js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="admin-stock-alert admin-stock-alert-error js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">{{ session('error') }}</div>
        @endif

        <div class="inventory-stats-grid admin-stock-stats-grid">
            <div class="inventory-stat-card">
                <div class="inventory-stat-content">
                    <div class="inventory-stat-label">Total Items</div>
                    <div class="inventory-stat-value">{{ $totalItems }}</div>
                </div>
            </div>
            <div class="inventory-stat-card">
                <div class="inventory-stat-content">
                    <div class="inventory-stat-label">In Stock</div>
                    <div class="inventory-stat-value">{{ $inStock }}</div>
                </div>
            </div>
            <div class="inventory-stat-card">
                <div class="inventory-stat-content">
                    <div class="inventory-stat-label">Low Stock</div>
                    <div class="inventory-stat-value">{{ $lowStock }}</div>
                </div>
            </div>
            <div class="inventory-stat-card">
                <div class="inventory-stat-content">
                    <div class="inventory-stat-label">Out of Stock</div>
                    <div class="inventory-stat-value">{{ $outOfStock }}</div>
                </div>
            </div>
        </div>

        <div class="inventory-table-section">
            <div class="inventory-table-header">
                <h2 class="inventory-table-title">Stock List</h2>
                <span class="admin-stock-results-text">{{ $stocks->total() }} item(s) matched</span>
            </div>

            <div class="inventory-overflow">
                <table class="inventory-table admin-stock-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Stock Details</th>
                            <th>Category</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <colgroup>
                        <col style="width: 28%">
                        <col style="width: 28%">
                        <col style="width: 16%">
                        <col style="width: 14%">
                        <col style="width: 14%">
                    </colgroup>
                    <tbody>
                        @forelse($stocks as $stock)
                            @php
                                $isOut = $stock->current_quantity <= 0;
                                $isLow = $stock->current_quantity > 0 && $stock->current_quantity <= $stock->minimum_quantity;
                            @endphp
                            <tr class="{{ $isOut ? 'admin-stock-row-out' : ($isLow ? 'admin-stock-row-low' : 'admin-stock-row-ok') }}">
                                <td>
                                    <div class="admin-stock-item-cell">
                                        <div class="admin-stock-thumb-wrap">
                                            @php $images = $stock->item->item_images ?? []; @endphp
                                            @if(count($images) > 0)
                                                <img src="{{ media_url($images[0]) }}" class="admin-stock-thumb" alt="{{ $stock->item->item_name }}">
                                            @else
                                                <span class="admin-stock-thumb-fallback">No image</span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="admin-stock-item-name">{{ $stock->item->item_name }}</div>
                                            <div class="admin-stock-item-desc">{{ $stock->item->item_description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="admin-stock-control-block">
                                        <div class="admin-stock-current-row">
                                            <span class="admin-stock-current-label">Current Stock:</span>
                                            <strong class="admin-stock-current-value">{{ $stock->current_quantity }}</strong>
                                        </div>
                            
                                        <span class="inventory-status-badge {{ $isOut ? 'inventory-status-out-stock' : ($isLow ? 'inventory-status-low-stock' : 'inventory-status-in-stock') }}">
                                            {{ $isOut ? 'Out of Stock' : ($isLow ? 'Low Stock' : 'In Stock') }}
                                        </span>

                                    </div>
                                </td>
                                <td>
                                    {{ !empty($stock->item->item_category) ? ucfirst($stock->item->item_category) : 'Uncategorized' }}
                                </td>
                                <td>
                                    {{ $stock->updated_at ? $stock->updated_at->format('M d, Y') : 'N/A' }}
                                </td>
                                <td>
                                    <div class="admin-stock-actions-col">
                                        <a href="{{ route('franchisee.stock.edit', $stock->stock_id) }}" class="table-action-btn table-action-edit">
                                            Edit Stock
                                        </a>
                                        <button
                                            type="button"
                                            onclick="toggleFifo({{ $stock->stock_id }})"
                                            id="fifo-btn-{{ $stock->stock_id }}"
                                            class="table-action-btn"
                                            style="background:#f0fdf4; color:#14532d; border:1px solid #86efac;"
                                        >
                                            Stock Batches
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @php $snap = $fifoSnapshots[(int) $stock->stock_id] ?? null; @endphp
                            <tr id="fifo-row-{{ $stock->stock_id }}" style="display:none; background:#f9fafb;">
                                <td colspan="5" style="padding: 15px;">
                                    @if($snap && count($snap['lots']) > 0)
                                        <table style="width:100%; border-collapse:collapse; background:white;">
                                            <thead>
                                                <tr style="background:#f3f4f6;">
                                                    <th style="padding:8px; text-align:left; color:#000; font-weight:bold; border:1px solid #d1d5db;">BATCH #</th>
                                                    <th style="padding:8px; text-align:left; color:#000; font-weight:bold; border:1px solid #d1d5db;">BATCH TYPE</th>
                                                    <th style="padding:8px; text-align:left; color:#000; font-weight:bold; border:1px solid #d1d5db;">DATE RECEIVED</th>
                                                    <th style="padding:8px; text-align:right; color:#000; font-weight:bold; border:1px solid #d1d5db;">REMAINING</th>
                                                    <th style="padding:8px; text-align:left; color:#000; font-weight:bold; border:1px solid #d1d5db;">OUT OF STOCK</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($snap['lots'] as $lot)
                                                    @php
                                                        $isSoldOut = $lot['quantity_remaining'] <= 0;
                                                        $receivedDate = $lot['received_date'] ?? $lot['received_at'] ?? null;
                                                    @endphp
                                                    <tr style="{{ $isSoldOut ? 'background:#fcfcfc;' : '' }}">
                                                        <td style="padding:8px; border:1px solid #d1d5db; color:#000;">
                                                            #{{ $loop->iteration }}
                                                        </td>
                                                        <td style="padding:8px; border:1px solid #d1d5db; color:#000;">
                                                            {{ ($lot['source'] ?? 'stock_in') === 'legacy_balance' ? 'Opening Stock' : 'Restocked' }}
                                                        </td>
                                                        <td style="padding:8px; border:1px solid #d1d5db; color:#000;">
                                                            {{ $receivedDate ? \Illuminate\Support\Carbon::parse($receivedDate)->format('M d, Y H:i') : '-' }}
                                                        </td>
                                                        <td style="padding:8px; border:1px solid #d1d5db; text-align:right; color:#000; font-weight:bold;">
                                                            {{ $lot['quantity_remaining'] }}
                                                        </td>
                                                        <td style="padding:8px; border:1px solid #d1d5db;">
                                                            @if(!$isSoldOut)
                                                                <span style="color:#16a34a; font-weight:bold;">In Stock</span>
                                                            @else
                                                                <span style="color:#dc2626; font-weight:bold;">
                                                                    Out: {{ !empty($lot['updated_at']) ? \Illuminate\Support\Carbon::parse($lot['updated_at'])->format('M d, Y') : 'N/A' }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p style="color:#000;">No batch records found.</p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="inventory-table-empty">
                                    No stock records found. Stock will be created when you receive your first delivery.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($stocks->hasPages())
                <div style="margin-top: 16px;">
                    {{ $stocks->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

<script>
function toggleFifo(stockId) {
    var row = document.getElementById('fifo-row-' + stockId);
    var btn = document.getElementById('fifo-btn-' + stockId);
    if (!row || !btn) return;

    var isHidden = row.style.display === 'none';
    row.style.display = isHidden ? 'table-row' : 'none';
    btn.textContent = isHidden ? 'Hide Batches' : 'Stock Batches';
}

</script>
