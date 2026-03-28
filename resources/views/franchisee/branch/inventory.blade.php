@extends('layouts.franchisee')

@section('content')
<div class="py-12">
    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded bg-green-100 text-green-800 border border-green-300">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded bg-red-100 text-red-800 border border-red-300">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 px-4 py-3 rounded bg-red-100 text-red-800 border border-red-300">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex items-start justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Inventory Management</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Branch inventory and stock levels
                </p>
            </div>
            <a href="{{ route('franchisee.branch.dashboard') }}" 
               class="text-sm font-medium text-orange-600 hover:text-orange-700 transition">
                ← Back to Dashboard
            </a>
        </div>

        <!-- Inventory Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Items -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Items</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ $totalItems }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- In Stock -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">In Stock</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ $inStock }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Low Stock -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Low Stock</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ $lowStock }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Out of Stock -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Out of Stock</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ $outOfStock }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Important Information -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Inventory Management</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>
                            Stock levels are managed through the Item Inventory system. Items below the minimum quantity threshold are highlighted for replenishment.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Current Inventory Levels</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Minimum Required</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adjust</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($inventory as $item)
                                <tr class="hover:bg-gray-50 {{ $item['status'] === 'out_of_stock' ? 'bg-red-50' : ($item['status'] === 'low_stock' ? 'bg-yellow-50' : '') }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item['item_name'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $item['item_category'] ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold {{ $item['status'] === 'out_of_stock' ? 'text-red-600' : ($item['status'] === 'low_stock' ? 'text-yellow-600' : 'text-green-600') }}">
                                            {{ number_format($item['current_stock']) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($item['minimum_quantity']) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($item['status'] === 'out_of_stock')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                                Out of Stock
                                            </span>
                                        @elseif($item['status'] === 'low_stock')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                                Low Stock
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                In Stock
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form action="{{ route('franchisee.branch.inventory.adjust', $item['item_id']) }}" method="POST" class="flex flex-col md:flex-row items-center gap-2">
                                            @csrf
                                            <input
                                                type="number"
                                                name="adjust_by"
                                                min="1"
                                                step="1"
                                                class="border rounded px-2 py-1 w-16 text-xs md:text-sm focus:ring-orange-500 focus:border-orange-500"
                                                placeholder="Qty"
                                                required
                                            >
                                            <div class="flex gap-1">
                                                <button type="submit" name="direction" value="add" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs md:text-sm font-bold">+</button>
                                                <button type="submit" name="direction" value="deduct" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs md:text-sm font-bold">-</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No inventory data available. Stock will be updated through the Item Inventory system.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Items Needing Replenishment -->
        @if(count(array_filter($inventory, fn($item) => $item['needs_replenishment'])) > 0)
        <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-6 h-6 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    Items Needing Replenishment
                </h2>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                    <p class="text-sm text-red-700">
                        The following items are running low or out of stock. You can place an order for replenishment.
                    </p>
                </div>
                <ul class="space-y-2">
                    @foreach(array_filter($inventory, fn($item) => $item['needs_replenishment']) as $item)
                        <li class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-900">{{ $item['item_name'] }}</span>
                            <span class="text-sm text-gray-600">Current Stock: 
                                <strong class="{{ $item['status'] === 'out_of_stock' ? 'text-red-600' : 'text-yellow-600' }}">
                                    {{ number_format($item['current_stock']) }}
                                </strong>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>
   <!-- Flash Message -->
    @if(session('success') || session('error'))
        <div id="flash-message" class="mt-4 px-4 py-2 rounded bg-green-100 text-green-800">
            {{ session('success') ?? session('error') }}
        </div>
    @endif
</div>

@if(session('flash_timeout'))
    <script>
        setTimeout(function() {
            let alert = document.querySelector('.mb-4');
            if(alert) alert.style.display = 'none';
        }, {{ session('flash_timeout') }});
    </script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const flash = document.getElementById('flash-message');
    if (flash) {
        setTimeout(() => flash.remove(), 3000);
    }
});
</script>
@endsection
