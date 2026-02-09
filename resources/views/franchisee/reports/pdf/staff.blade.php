<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Staff Report</title>
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
    <h1>Staff Report</h1>
    <div class="meta">
        Generated: {{ now()->format('M d, Y h:i A') }}
        <br>
        Filters: Start {{ $filters['start_date'] ?? 'N/A' }} | End {{ $filters['end_date'] ?? 'N/A' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Staff Name</th>
                <th>Contact</th>
                <th>Status</th>
                <th>Orders</th>
                <th class="right">Total Sales</th>
            </tr>
        </thead>
        <tbody>
            @foreach($staff as $member)
                @php $perf = $performance[$member->fstaff_id] ?? null; @endphp
                <tr>
                    <td>{{ $member->fstaff_fname }} {{ $member->fstaff_lname }}</td>
                    <td>{{ $member->fstaff_contactNo }}</td>
                    <td>{{ $member->fstaff_status }}</td>
                    <td>{{ $perf->orders_count ?? 0 }}</td>
                    <td class="right">₱{{ number_format($perf->total_sales ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
