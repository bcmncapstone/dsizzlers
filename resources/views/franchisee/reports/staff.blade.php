@extends('layouts.franchisee')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Staff Report</h1>
                    <p class="text-sm text-gray-600">View staff roster and performance.</p>
                </div>
                <a href="{{ route('franchisee.reports.index') }}" class="text-sm text-blue-600 hover:underline">Back to Reports</a>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-4">
            <form method="GET" action="{{ route('franchisee.reports.staff') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-md text-xs font-semibold uppercase">Apply Filter</button>
                </div>
            </form>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-3 mb-4">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        @if($noData)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4">
                <p class="text-sm text-yellow-700">No staff data found for this branch.</p>
            </div>
        @endif

        @if(!$noData && $noPerformanceData)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4">
                <p class="text-sm text-yellow-700">No staff performance data found for the selected date range.</p>
                @if($availableRange && $availableRange->min_date && $availableRange->max_date)
                    <p class="text-xs text-yellow-600 mt-1">Available range: {{ \Carbon\Carbon::parse($availableRange->min_date)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($availableRange->max_date)->format('M d, Y') }}</p>
                @endif
            </div>
        @endif

        <!-- Charts Section -->
        @if(!$noData && !$noPerformanceData && count($topStaffBySales) > 0)
        <div class="flex justify-center mb-4">
            <!-- Top Staff by Sales Chart -->
            <div class="bg-white shadow-sm sm:rounded-lg p-4 w-full max-w-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">Top Staff by Sales</h3>
                <div style="position: relative; height: 300px;">
                    <canvas id="staffSalesChart"></canvas>
                </div>
            </div>
        </div>
        @endif

        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <div class="p-4 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">Staff Roster</h2>
                <a href="{{ route('franchisee.reports.staff.pdf', request()->query()) }}" class="inline-flex items-center px-3 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase">Generate PDF</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Sales</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($staff as $member)
                            @php
                                $perf = $performance[$member->fstaff_id] ?? null;
                            @endphp
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $member->fstaff_fname }} {{ $member->fstaff_lname }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $member->fstaff_contactNo }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $member->fstaff_status }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $perf->orders_count ?? 0 }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">₱{{ number_format($perf->total_sales ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No results.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Top Staff by Sales Chart
    @if(!$noData && !$noPerformanceData && count($topStaffBySales) > 0)
    const staffSalesCtx = document.getElementById('staffSalesChart');
    if (staffSalesCtx) {
        const staffSalesData = @json($topStaffBySales);
        new Chart(staffSalesCtx, {
            type: 'bar',
            data: {
                labels: staffSalesData.map(staff => {
                    const name = staff.name;
                    return name.substring(0, 15) + (name.length > 15 ? '...' : '');
                }),
                datasets: [{
                    label: 'Sales (₱)',
                    data: staffSalesData.map(staff => staff.sales),
                    backgroundColor: '#FF5722',
                    borderColor: '#FF2D00',
                    borderWidth: 1,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                    }
                }
            }
        });
    }
    @endif
</script>
@endsection
