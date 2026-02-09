<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { font-size: 11px; margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Inventory Report</h1>
    <div class="meta">
        Generated: {{ now()->format('M d, Y h:i A') }}
        <br>
        Filters: Start {{ $filters['start_date'] ?? 'N/A' }} | End {{ $filters['end_date'] ?? 'N/A' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Item</th>
                <th>Type</th>
                <th>Qty</th>
                <th>Balance After</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                    <td>{{ $transaction->item->item_name }}</td>
                    <td>{{ ucfirst($transaction->transaction_type) }}</td>
                    <td>{{ $transaction->quantity }}</td>
                    <td>{{ $transaction->balance_after }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
