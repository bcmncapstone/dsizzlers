@extends('layouts.franchisee')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-3xl font-bold text-gray-900">Branch Management</h1>
                <p class="mt-2 text-sm text-gray-600">
                    {{ $branch->location }} - Manage your branch performance, inventory, and finances
                </p>
            </div>
        </div>

        <!-- Quick Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Orders -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Orders</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ number_format($totalOrders) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                                <dd class="text-2xl font-semibold text-gray-900">₱{{ number_format($totalRevenue, 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">This Month</dt>
                                <dd class="text-2xl font-semibold text-gray-900">₱{{ number_format($monthlyRevenue, 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Modules -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Branch Management Modules</h2>
                <p class="text-sm text-gray-600 mb-6">
                    Select a module below to view detailed information about your branch operations
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Performance Module -->
                    <a href="{{ route('franchisee.branch.performance') }}" 
                       class="block p-6 bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg hover:shadow-lg transition duration-200 transform hover:scale-105">
                        <div class="flex items-center justify-center mb-4">
                            <div class="bg-blue-500 rounded-full p-4">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Performance</h3>
                        <p class="text-sm text-gray-600 text-center">
                            View sales and operational performance metrics
                        </p>
                    </a>

                    <!-- Inventory Module -->
                    <a href="{{ route('franchisee.branch.inventory') }}" 
                       class="block p-6 bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-lg hover:shadow-lg transition duration-200 transform hover:scale-105">
                        <div class="flex items-center justify-center mb-4">
                            <div class="bg-orange-500 rounded-full p-4">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Item Inventory</h3>
                        <p class="text-sm text-gray-600 text-center">
                            Monitor and adjust your stock levels
                        </p>
                    </a>

                    <!-- Financial Module -->
                    <a href="{{ route('franchisee.branch.financial') }}" 
                       class="block p-6 bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg hover:shadow-lg transition duration-200 transform hover:scale-105">
                        <div class="flex items-center justify-center mb-4">
                            <div class="bg-green-500 rounded-full p-4">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Financial</h3>
                        <p class="text-sm text-gray-600 text-center">
                            Track revenue, expenses, and profitability
                        </p>
                    </a>
                </div>
            </div>
        </div>

        <!-- Important Note -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Inventory Management Note</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>
                            All inventory is automatically calculated from orders delivered by admin. 
                            Franchisees cannot manually add items. Stock levels reflect items received 
                            from admin minus items sold to customers.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
