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
                <span class="admin-stock-results-text">{{ $stocks->count() }} item(s) matched</span>
            </div>

            <div class="inventory-overflow">
                <table class="inventory-table admin-stock-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Stock Adjustment</th>
                            <th>Category</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <colgroup>
                        <col style="width: 30%">
                        <col style="width: 36%">
                        <col style="width: 16%">
                        <col style="width: 18%">
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

                                        <button
                                            type="button"
                                            onclick="toggleFifo({{ $stock->stock_id }})"
                                            id="fifo-btn-{{ $stock->stock_id }}"
                                            class="inventory-pdf-btn admin-stock-btn-muted"
                                            style="margin-top:8px; width:max-content;"
                                        >
                                            Stock Batches
                                        </button>

                                        <form action="{{ route('franchisee.stock.update', $stock->stock_id) }}" method="POST" class="admin-stock-adjust-form">
                                            @csrf
                                            <label class="admin-stock-adjust-label" for="adjust-{{ $stock->stock_id }}">Need to adjust?</label>
                                            <input
                                                type="text"
                                                name="notes"
                                                class="admin-stock-adjust-input"
                                                placeholder="Notes"
                                                maxlength="255"
                                                style="margin-bottom:8px;"
                                            >
                                            <div class="admin-stock-adjust-controls">
                                                <input
                                                    id="adjust-{{ $stock->stock_id }}"
                                                    type="number"
                                                    name="adjust_by"
                                                    min="1"
                                                    step="1"
                                                    class="admin-stock-adjust-input"
                                                    placeholder="Qty"
                                                    required
                                                >
                                                <button type="submit" name="direction" value="add" class="admin-stock-adjust-btn admin-stock-adjust-plus">
                                                    +
                                                </button>
                                                <button type="submit" name="direction" value="deduct" class="admin-stock-adjust-btn admin-stock-adjust-minus">
                                                    -
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    {{ !empty($stock->item->item_category) ? ucfirst($stock->item->item_category) : 'Uncategorized' }}
                                </td>
                                <td>
                                    {{ $stock->updated_at ? $stock->updated_at->format('M d, Y') : 'N/A' }}
                                </td>
                            </tr>
                            @php $snap = $fifoSnapshots[(int) $stock->stock_id] ?? null; @endphp
                            <tr id="fifo-row-{{ $stock->stock_id }}" style="display:none; background:#f9fafb;">
                                <td colspan="4" style="padding:0;">
                                    <div style="padding:12px 16px;">
                                        <div style="display:flex; flex-wrap:wrap; gap:16px; font-size:13px; margin-bottom:10px;">
                                            <span><strong>Current Stock:</strong> {{ $snap['stock_quantity'] ?? '-' }}</span>
                                            <span><strong>Tracked Available:</strong> {{ $snap['fifo_available'] ?? '-' }}</span>
                                            @if($snap && $snap['stock_quantity'] !== $snap['fifo_available'])
                                                <span style="background:#fef3c7; color:#92400e; border:1px solid #fcd34d; border-radius:5px; padding:2px 8px; font-size:12px; font-weight:600;">
                                                    &#9888; Mismatch - some stock may not have a batch record yet
                                                </span>
                                            @endif
                                        </div>
                                        @if($snap && count($snap['lots']) > 0)
                                            <table style="width:100%; border-collapse:collapse; font-size:12px;">
                                                <thead>
                                                    <tr style="background:#e5e7eb;">
                                                        <th style="padding:5px 8px; text-align:left; border:1px solid #d1d5db;">Batch Type</th>
                                                        <th style="padding:5px 8px; text-align:left; border:1px solid #d1d5db;">Date Received</th>
                                                        <th style="padding:5px 8px; text-align:right; border:1px solid #d1d5db;">Remaining Qty</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($snap['lots'] as $lot)
                                                        <tr>
                                                            <td style="padding:5px 8px; border:1px solid #d1d5db;">
                                                                @if(($lot['source'] ?? 'stock_in') === 'legacy_balance')
                                                                    Opening Stock
                                                                @elseif(($lot['source'] ?? 'stock_in') === 'manual_add')
                                                                    Manual Add
                                                                @else
                                                                    Delivered
                                                                @endif
                                                            </td>
                                                            <td style="padding:5px 8px; border:1px solid #d1d5db;">
                                                                @if(!empty($lot['received_at']))
                                                                    {{ \Illuminate\Support\Carbon::parse($lot['received_at'])->format('M d, Y H:i') }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td style="padding:5px 8px; border:1px solid #d1d5db; text-align:right; font-weight:700;">
                                                                {{ $lot['quantity_remaining'] }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <p style="font-size:12px; color:#6b7280;">No batch records found for this stock item yet.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="inventory-table-empty">
                                    No stock records found. Stock will be created when you receive your first delivery.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
