<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory Report</title>
    <style>
        * { margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; line-height: 1.4; }
        h1 { font-size: 20px; margin-bottom: 4px; font-weight: bold; }
        h2 { font-size: 13px; margin-top: 14px; margin-bottom: 8px; font-weight: bold; }
        p { margin-bottom: 4px; }
        
        .header { margin-bottom: 16px; border-bottom: 2px solid #FF8C42; padding-bottom: 8px; }
        .header p { font-size: 10px; color: #666; }
        .meta-info { font-size: 10px; color: #555; margin-top: 4px; }
        
        .summary { margin-bottom: 16px; }
        .summary-row { display: flex; gap: 16px; margin-bottom: 8px; flex-wrap: wrap; }
        .summary-item { flex: 1; min-width: 160px; border: 1px solid #ddd; padding: 8px; }
        .summary-label { font-size: 10px; color: #666; margin-bottom: 4px; font-weight: bold; }
        .summary-value { font-size: 14px; font-weight: bold; color: #111; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; font-size: 10px; }
        th { background: #f3f4f6; font-weight: bold; }
        
        .center { text-align: center; }
        .right { text-align: right; }
        
        .status { padding: 2px 4px; font-size: 9px; font-weight: bold; border-radius: 2px; }
        .status-in { background: #d1fae5; color: #065f46; }
        .status-low { background: #fef3c7; color: #92400e; }
        .status-out { background: #fee2e2; color: #991b1b; }
        
        .footer { margin-top: 16px; padding-top: 8px; border-top: 1px solid #ddd; font-size: 9px; color: #555; }
        .footer p { margin-bottom: 3px; }
        
        .page-break { page-break-after: always; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventory Report</h1>
        <p>D-Sizzlers Admin Portal</p>
        <div class="meta-info">
            <strong>Generated:</strong> {{ now()->format('M d, Y h:i A') }}
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="summary">
        <div class="summary-row">
            <div class="summary-item">
                <div class="summary-label">Total Items</div>
                <div class="summary-value">{{ $items->count() }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Quantity</div>
                <div class="summary-value">{{ number_format($totalQuantity) }} units</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Value</div>
                <div class="summary-value">₱{{ number_format($totalValue, 2) }}</div>
            </div>
        </div>
        <div class="summary-row">
            <div class="summary-item">
                <div class="summary-label">In Stock (>10)</div>
                <div class="summary-value" style="color: #059669;">{{ $inStock->count() }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Low Stock (1-10)</div>
                <div class="summary-value" style="color: #d97706;">{{ $lowStock->count() }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Out of Stock</div>
                <div class="summary-value" style="color: #dc2626;">{{ $outOfStock->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <h2>All Items Inventory</h2>
    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Category</th>
                <th class="center">Stock Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Stock Value</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->item_category ?? '-' }}</td>
                    <td class="center">{{ $item->stock_quantity }}</td>
                    <td class="right">₱{{ number_format($item->price, 2) }}</td>
                    <td class="right">₱{{ number_format($item->stock_quantity * $item->price, 2) }}</td>
                    <td>
                        @if($item->stock_quantity > 10)
                            <span class="status status-in">In Stock</span>
                        @elseif($item->stock_quantity > 0)
                            <span class="status status-low">Low Stock</span>
                        @else
                            <span class="status status-out">Out Stock</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Report generated:</strong> {{ now()->format('F d, Y') }} at {{ now()->format('h:i A') }} | D-Sizzlers Admin System | Confidential</p>
        <p>This report shows the current inventory levels of all items in the system.</p>
    </div>
</body>
</html>
