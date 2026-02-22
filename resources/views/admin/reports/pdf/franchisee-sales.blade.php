<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Franchisee Sales Report</title>
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
        .summary-item { flex: 1; min-width: 140px; border: 1px solid #ddd; padding: 8px; }
        .summary-label { font-size: 10px; color: #666; margin-bottom: 4px; font-weight: bold; }
        .summary-value { font-size: 14px; font-weight: bold; color: #111; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; font-size: 10px; }
        th { background: #f3f4f6; font-weight: bold; }
        
        .center { text-align: center; }
        .right { text-align: right; }
        
        .footer { margin-top: 16px; padding-top: 8px; border-top: 1px solid #ddd; font-size: 9px; color: #555; }
        .page-break { page-break-after: always; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Franchisee Sales Report</h1>
        <p>D-Sizzlers Admin Portal</p>
        <div class="meta-info">
            <strong>Generated:</strong> {{ now()->format('M d, Y h:i A') }}<br>
            <strong>Filters:</strong> Franchisee {{ $filters['franchisee_id'] ?? 'All' }} | Start {{ $filters['start_date'] ?? 'N/A' }} | End {{ $filters['end_date'] ?? 'N/A' }}
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="summary">
        <div class="summary-row">
            <div class="summary-item">
                <div class="summary-label">Total Franchisees</div>
                <div class="summary-value">{{ $rows->count() }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Orders</div>
                <div class="summary-value">{{ $totalOrders }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Revenue</div>
                <div class="summary-value">₱{{ number_format($totalSales, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Top Franchisees Summary -->
    <h2>Franchisees by Revenue</h2>
    <table>
        <thead>
            <tr>
                <th style="text-align: left;">Franchisee</th>
                <th style="text-align: center;">Orders</th>
                <th style="text-align: right;">Total Sales</th>
            </tr>
        </thead>
        <tbody>
            @php
                $topChartRows = array_slice($chartRows, 0, 5);
            @endphp
            @foreach($topChartRows as $franchisee)
                <tr>
                    <td style="text-align: left;">{{ $franchisee['name'] }}</td>
                    <td style="text-align: center;">{{ $franchisee['orders'] }}</td>
                    <td style="text-align: right;">₱{{ number_format($franchisee['sales'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Franchisee Market Share Summary -->
    <h2>Franchisee Sales</h2>
    <table>
        <thead>
            <tr>
                <th style="text-align: left;">Franchisee</th>
                <th style="text-align: center;">Orders</th>
                <th style="text-align: right;">Total Sales</th>
            </tr>
        </thead>
        <tbody>
            @foreach($chartRows as $franchisee)
                <tr>
                    <td style="text-align: left;">{{ $franchisee['name'] }}</td>
                    <td style="text-align: center;">{{ $franchisee['orders'] }}</td>
                    <td style="text-align: right;">₱{{ number_format($franchisee['sales'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- All Franchisees Details -->
    <h2>All Franchisees Report</h2>
    <table>
        <thead>
            <tr>
                <th style="text-align: left;">Franchisee</th>
                <th style="text-align: center;">Orders</th>
                <th style="text-align: right;">Total Sales</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td style="text-align: left;">{{ $franchisees[$row->franchisee_id]->franchisee_name ?? 'N/A' }}</td>
                    <td style="text-align: center;">{{ $row->orders_count }}</td>
                    <td style="text-align: right;">₱{{ number_format($row->total_sales, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Report generated:</strong> {{ now()->format('F d, Y') }} at {{ now()->format('h:i A') }} | D-Sizzlers Admin System | Confidential</p>
    </div>
</body>
</html>
