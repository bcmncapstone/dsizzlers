<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { font-size: 11px; margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Sales Report</h1>
    <div class="meta">
        Generated: {{ now()->format('M d, Y h:i A') }}
        <br>
        Filters: Start {{ $filters['start_date'] ?? 'N/A' }} | End {{ $filters['end_date'] ?? 'N/A' }}
    </div>

    <p><strong>Total Sales Entries:</strong> {{ $totalOrders }} &nbsp; | &nbsp; <strong>Total Sales:</strong> ₱{{ number_format($totalSales, 2) }}</p>

    <table>
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Item</th>
                <th>Decreased By</th>
                <th>Qty Sold</th>
                <th>Date</th>
                <th class="right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesEntries as $entry)
                <tr>
                    <td>{{ $entry->transaction_id }}</td>
                    <td>{{ $entry->item_name }}</td>
                    <td>{{ $entry->decreased_by }}</td>
                    <td>{{ $entry->quantity_sold }}</td>
                    <td>{{ \Carbon\Carbon::parse($entry->created_at)->format('M d, Y') }}</td>
                    <td class="right">₱{{ number_format($entry->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
