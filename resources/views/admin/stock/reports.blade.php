@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="bg-white shadow-sm p-8 rounded-lg mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1>Stock Reports</h1>
                    <p class="header-subtitle">View inventory reports with date filtering</p>
                </div>
                <a href="{{ route('admin.stock.index') }}" class="back-link-button">
                    ← Back to Stock
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white shadow-sm p-8 rounded-lg mb-6">
            <h2 style="margin-bottom: 24px;">Filter Reports</h2>

            <!-- Quick Date Presets -->
            <div class="quick-filters-section">
                <span class="filter-label">Quick Filters:</span>
                <button type="button" onclick="setDateFilter('today')" class="preset-button">Today</button>
                <button type="button" onclick="setDateFilter('yesterday')" class="preset-button">Yesterday</button>
                <button type="button" onclick="setDateFilter('last7days')" class="preset-button">Last 7 Days</button>
                <button type="button" onclick="setDateFilter('last30days')" class="preset-button">Last 30 Days</button>
                <button type="button" onclick="setDateFilter('thismonth')" class="preset-button">This Month</button>
                <button type="button" onclick="clearDateFilter()" class="preset-button clear-button">Clear Dates</button>
            </div>

            <!-- Form -->
            <form method="GET" action="{{ route('admin.stock.reports') }}" id="reportFilterForm" class="filter-form">
                <div class="filter-grid">
                    <div>
                        <label for="franchisee_id" class="form-label">Franchisee</label>
                        <select name="franchisee_id" id="franchisee_id" class="form-select">
                            <option value="">All Franchisees</option>
                            @foreach($franchisees as $franchisee)
                                <option value="{{ $franchisee->franchisee_id }}" 
                                        {{ request('franchisee_id') == $franchisee->franchisee_id ? 'selected' : '' }}>
                                    {{ $franchisee->franchisee_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="form-input">
                    </div>
                    <div>
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="form-input">
                    </div>
                    <div class="filter-button-wrapper">
                        <button type="submit" class="filter-submit-button">Apply Filter</button>
                    </div>
                </div>
            </form>

            <!-- Error Messages -->
            @if(session('error'))
                <div class="alert-error">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <!-- No Data Message -->
            @if($noData && (request('franchisee_id') || request('start_date') || request('end_date')))
                <div class="alert-warning">
                    <p>No inventory records found for the selected filters.</p>
                </div>
            @endif
        </div>

        <!-- Transaction History Table -->
        <div class="bg-white shadow-sm p-8 rounded-lg">
            <h2 style="margin-bottom: 24px;">Stock Transaction History</h2>
            
            <div class="table-wrapper">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Franchisee</th>
                            <th>Item</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Balance After</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td class="date-cell">
                                    <div class="date-main">{{ $transaction->created_at->format('M d, Y') }}</div>
                                    <div class="date-time">{{ $transaction->created_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div class="franchisee-name">{{ $transaction->franchisee->franchisee_name }}</div>
                                    <div class="franchisee-address">{{ $transaction->franchisee->franchisee_address }}</div>
                                </td>
                                <td>
                                    <div class="item-name">{{ $transaction->item->item_name }}</div>
                                    <div class="item-category">{{ $transaction->item->item_category }}</div>
                                </td>
                                <td>
                                    @if($transaction->transaction_type === 'in')
                                        <span class="badge-stockin">Stock In</span>
                                    @elseif($transaction->transaction_type === 'out')
                                        <span class="badge-stockout">Stock Out</span>
                                    @else
                                        <span class="badge-adjustment">Adjustment</span>
                                    @endif
                                </td>
                                <td class="quantity-cell">
                                    <span class="quantity-value {{ $transaction->quantity >= 0 ? 'quantity-positive' : 'quantity-negative' }}">
                                        {{ $transaction->quantity >= 0 ? '+' : '' }}{{ $transaction->quantity }}
                                    </span>
                                </td>
                                <td class="balance-cell">{{ $transaction->balance_after }}</td>
                                <td>
                                    <div class="notes-main">{{ $transaction->notes ?? '-' }}</div>
                                    @if($transaction->reference_type)
                                        <div class="notes-ref">
                                            Ref: {{ ucfirst($transaction->reference_type) }}
                                            @if($transaction->reference_id)
                                                #{{ $transaction->reference_id }}
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-row">
                                    @if($noData)
                                        No transaction records found. Please adjust your filters.
                                    @else
                                        No stock transactions have been recorded yet.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($transactions->hasPages())
                <div class="pagination-wrapper">
                    {{ $transactions->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

<script>
function setDateFilter(preset) {
    const today = new Date();
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    let startDate, endDate;
    
    switch(preset) {
        case 'today':
            startDate = endDate = formatDate(today);
            break;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            startDate = endDate = formatDate(yesterday);
            break;
        case 'last7days':
            const last7 = new Date(today);
            last7.setDate(last7.getDate() - 7);
            startDate = formatDate(last7);
            endDate = formatDate(today);
            break;
        case 'last30days':
            const last30 = new Date(today);
            last30.setDate(last30.getDate() - 30);
            startDate = formatDate(last30);
            endDate = formatDate(today);
            break;
        case 'thismonth':
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            startDate = formatDate(firstDay);
            endDate = formatDate(today);
            break;
    }
    
    startDateInput.value = startDate;
    endDateInput.value = endDate;
    document.getElementById('reportFilterForm').submit();
}

function clearDateFilter() {
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
    document.getElementById('reportFilterForm').submit();
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}
</script>
@endsection
