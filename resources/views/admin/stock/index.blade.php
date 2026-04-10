@extends('layouts.app')

@section('content')

<div class="inventory-page-wrapper admin-stock-page">
    <div class="inventory-page-container">

        <div class="inventory-header admin-stock-header">
            <div>
                <h1>Manage Stock</h1>
                <p>Monitor availability and adjust quantities</p>
            </div>
            <div class="admin-stock-header-actions">
                <a href="{{ route('admin.stock.franchisee-inventory') }}" class="inventory-pdf-btn">
                    Franchisee Inventory
                </a>
                <a href="{{ route('admin.items.archived') }}" class="inventory-pdf-btn admin-stock-btn-muted">
                    Archived Stock Item
                </a>
                <a href="{{ route('admin.items.create') }}" class="inventory-pdf-btn">
                    Add New Stock Item
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="admin-stock-alert admin-stock-alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="admin-stock-alert admin-stock-alert-error">{{ session('error') }}</div>
        @endif

        <div class="admin-stock-filter-card">
            <form method="GET" action="{{ route('admin.stock.index') }}" class="admin-stock-filter-form">
                <div class="admin-stock-filter-group">
                    <label for="search" class="admin-stock-filter-label">Search Stock Item</label>
                    <input type="text" id="search" name="search" class="admin-stock-filter-input" placeholder="Item name..." value="{{ $search }}">
                </div>

                <div class="admin-stock-filter-group">
                    <label for="category" class="admin-stock-filter-label">Category</label>
                    <select id="category" name="category" class="admin-stock-filter-input" onchange="this.form.submit()">
                        <option value="">All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ $selectedCategory === $category ? 'selected' : '' }}>
                                {{ ucfirst($category) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="admin-stock-filter-group">
                    <label for="stock_status" class="admin-stock-filter-label">Stock Status</label>
                    <select id="stock_status" name="stock_status" class="admin-stock-filter-input" onchange="this.form.submit()">
                        <option value="all" {{ $stockStatus === 'all' ? 'selected' : '' }}>All stock levels</option>
                        <option value="in_stock" {{ $stockStatus === 'in_stock' ? 'selected' : '' }}>In stock</option>
                        <option value="low_stock" {{ $stockStatus === 'low_stock' ? 'selected' : '' }}>Low stock (1-10)</option>
                    </select>
                </div>
            </form>
        </div>

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
                    <div class="inventory-stat-value">{{ $inStockCount }}</div>
                </div>
            </div>
            <div class="inventory-stat-card">
                <div class="inventory-stat-content">
                    <div class="inventory-stat-label">Low Stock</div>
                    <div class="inventory-stat-value">{{ $lowStockCount }}</div>
                </div>
            </div>
        </div>

        <div class="inventory-table-section">
            <div class="inventory-table-header">
                <h2 class="inventory-table-title">Stock List</h2>
                <span class="admin-stock-results-text">{{ $items->count() }} item(s) matched</span>
            </div>

            <div class="inventory-overflow">
                <table class="inventory-table admin-stock-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Stock Adjustment</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <colgroup>
                        <col style="width: 28%">
                        <col style="width: 26%">
                        <col style="width: 10%">
                        <col style="width: 12%">
                        <col style="width: 24%">
                    </colgroup>
                    <tbody>
                        @forelse($items as $item)
                            @php
                                $isOut = $item->stock_quantity <= 0;
                                $isLow = $item->stock_quantity > 0 && $item->stock_quantity <= 10;
                            @endphp
                            <tr class="{{ $isOut ? 'admin-stock-row-out' : ($isLow ? 'admin-stock-row-low' : 'admin-stock-row-ok') }}">
                                <td>
                                    <div class="admin-stock-item-cell">
                                        <div class="admin-stock-thumb-wrap">
                                            @forelse ($item->item_images as $img)
                                                <img src="{{ media_url($img) }}" class="admin-stock-thumb" alt="{{ $item->item_name }}">
                                                @break
                                            @empty
                                                <span class="admin-stock-thumb-fallback">No image</span>
                                            @endforelse
                                        </div>
                                        <div>
                                            <div class="admin-stock-item-name">{{ $item->item_name }}</div>
                                            <div class="admin-stock-item-desc">{{ $item->item_description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="admin-stock-control-block">
                                        <div class="admin-stock-current-row">
                                            <span class="admin-stock-current-label">Current Stock:</span>
                                            <strong class="admin-stock-current-value">{{ $item->stock_quantity }}</strong>
                                        </div>
                                        <span class="inventory-status-badge {{ $isOut ? 'inventory-status-out-stock' : ($isLow ? 'inventory-status-low-stock' : 'inventory-status-in-stock') }}">
                                            {{ $isOut ? 'Out of Stock' : ($isLow ? 'Low Stock' : 'In Stock') }}
                                        </span>

                                        <form action="{{ route('admin.stock.adjust', $item->item_id) }}" method="POST" class="admin-stock-adjust-form">
                                            @csrf
                                            <div class="admin-stock-adjust-controls">
                                                <input type="number" name="adjust_by" min="1" class="admin-stock-adjust-input" placeholder="Qty" required>
                                                <button type="submit" name="direction" value="add" class="admin-stock-adjust-btn admin-stock-adjust-plus">+</button>
                                                <button type="submit" name="direction" value="deduct" class="admin-stock-adjust-btn admin-stock-adjust-minus">-</button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                                <td><strong>₱{{ number_format($item->price, 2) }}</strong></td>
                                <td>{{ !empty($item->item_category) ? ucfirst($item->item_category) : 'Uncategorized' }}</td>
                                <td>
                                    <div class="admin-stock-actions-col">
                                        <a href="{{ route('admin.items.edit', $item->item_id) }}" class="table-action-btn table-action-edit">Edit</a>
                                        <button type="button" onclick="toggleFifo({{ $item->item_id }})" id="fifo-btn-{{ $item->item_id }}" class="table-action-btn" style="background:#f0fdf4; color:#14532d; border:1px solid #86efac;">
                                            Stock Batches
                                        </button>
                                        @if((int) $item->stock_quantity <= 0)
                                            <form action="{{ route('admin.items.archive', $item->item_id) }}" method="POST" onsubmit="return confirm('Archive item?');">
                                                @csrf
                                                <button type="submit" class="table-action-btn table-action-archive">Archive</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- THE STOCK BATCHES SECTION - FIXED DESIGN --}}
                         {{-- THE STOCK BATCHES SECTION --}}
@php $snap = $fifoSnapshots[(int) $item->item_id] ?? null; @endphp
<tr id="fifo-row-{{ $item->item_id }}" style="display:none; background:#f9fafb;">
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
                        @php $isSoldOut = $lot['quantity_remaining'] <= 0; @endphp
                        <tr style="{{ $isSoldOut ? 'background:#fcfcfc;' : '' }}">
                            {{-- This line now ensures each item starts its own count at #1 --}}
                            <td style="padding:8px; border:1px solid #d1d5db; color:#000;">
                                #{{ $loop->iteration }}
                            </td>
                            
                            <td style="padding:8px; border:1px solid #d1d5db; color:#000;">
                                {{ ($lot['source'] ?? 'stock_in') === 'legacy_balance' ? 'Opening Stock' : 'Restocked' }}
                            </td>
                            <td style="padding:8px; border:1px solid #d1d5db; color:#000;">
                                {{ $lot['received_date'] ? \Illuminate\Support\Carbon::parse($lot['received_date'])->format('M d, Y H:i') : '-' }}
                            </td>
                            <td style="padding:8px; border:1px solid #d1d5db; text-align:right; color:#000; font-weight:bold;">
                                {{ $lot['quantity_remaining'] }}
                            </td>
                            <td style="padding:8px; border:1px solid #d1d5db;">
                                @if(!$isSoldOut)
                                    <span style="color:#16a34a; font-weight:bold;">In Stock</span>
                                @else
                                    <span style="color:#dc2626; font-weight:bold;">
                                        Out: {{ $lot['updated_at'] ? \Illuminate\Support\Carbon::parse($lot['updated_at'])->format('M d, Y') : 'N/A' }}
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
                            <tr><td colspan="5" style="text-align:center; padding:20px;">No stock items found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFifo(itemId) {
    const row = document.getElementById(`fifo-row-${itemId}`);
    const btn = document.getElementById(`fifo-btn-${itemId}`);
    if (row.style.display === 'none') {
        row.style.display = 'table-row';
        btn.innerText = 'Hide Batches';
    } else {
        row.style.display = 'none';
        btn.innerText = 'Stock Batches';
    }
}

document.querySelectorAll('.admin-stock-adjust-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        var qty = form.querySelector('input[name="adjust_by"]').value;
        var btn = e.submitter;
        var direction = btn ? btn.value : 'adjust';
        var action = direction === 'add' ? 'add' : 'deduct';

        if (!confirm('Are you sure you want to ' + action + ' ' + qty + ' item(s)?')) {
            e.preventDefault();
        }
    });
});
</script>
@endsection