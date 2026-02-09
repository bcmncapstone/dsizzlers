<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Franchisee Sales Report</title>
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
    <h1>Franchisee Sales Report</h1>
    <div class="meta">
        Generated: {{ now()->format('M d, Y h:i A') }}
        <br>
        Filters: Franchisee {{ $filters['franchisee_id'] ?? 'All' }} | Start {{ $filters['start_date'] ?? 'N/A' }} | End {{ $filters['end_date'] ?? 'N/A' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Franchisee</th>
                <th>Orders</th>
                <th class="right">Total Sales</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $franchisees[$row->franchisee_id]->franchisee_name ?? 'N/A' }}</td>
                    <td>{{ $row->orders_count }}</td>
                    <td class="right">₱{{ number_format($row->total_sales, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
