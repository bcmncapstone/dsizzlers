@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h1 class="text-2xl font-bold text-gray-900">Reports</h1>
            <p class="text-sm text-gray-600 mt-1">Choose a report type to generate.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                <a href="{{ route('admin.reports.sales') }}" class="block border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:shadow">
                    <h2 class="text-lg font-semibold text-gray-900">Sales Report</h2>
                    <p class="text-sm text-gray-600">Filter sales by date range and franchisee.</p>
                </a>
                <a href="{{ route('admin.reports.inventory') }}" class="block border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:shadow">
                    <h2 class="text-lg font-semibold text-gray-900">Inventory Report</h2>
                    <p class="text-sm text-gray-600">Track stock movements by date range.</p>
                </a>
                <a href="{{ route('admin.reports.franchisee-sales') }}" class="block border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:shadow">
                    <h2 class="text-lg font-semibold text-gray-900">Franchisee Sales Report</h2>
                    <p class="text-sm text-gray-600">Compare sales performance per franchisee.</p>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
