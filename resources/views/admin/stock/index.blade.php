@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Action Buttons (Top) -->
        <div class="mb-4 flex flex-wrap gap-3">
            <a href="{{ route('admin.stock.franchisee-inventory') }}" 
               class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
                📦 Manage Franchisee Inventory
            </a>
            <a href="{{ route('admin.items.archived') }}" 
               class="inline-flex items-center px-6 py-3 bg-gray-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                🗂️ Archived Items
            </a>
            <a href="{{ route('admin.items.create') }}" 
               class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                ➕ Stock Inventory
            </a>
        </div>
        
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-3xl font-bold text-gray-900">Manage Stock</h1>
                <p class="mt-2 text-sm text-gray-600">
                    View and manage item stock inventory
                </p>
            </div>
        </div>

        <!-- Stock Table (Items Catalog) -->
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Stock Items</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($items as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @forelse ($item->item_images as $img)
                                            <img src="{{ asset('storage/' . $img) }}" width="60" class="rounded" alt="{{ $item->item_name }}">
                                        @empty
                                            <span class="text-xs text-gray-400">No image</span>
                                        @endforelse
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->item_name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500">{{ $item->item_description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($item->price, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900">{{ $item->stock_quantity }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $item->item_category }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.items.edit', $item->item_id) }}" 
                                               class="text-blue-600 hover:text-blue-900">
                                                Update 
                                            </a>
                                            <form action="{{ route('admin.items.archive', $item->item_id) }}"
                                                  method="POST"
                                                  style="display:inline;"
                                                  onsubmit="return confirm('Are you sure you want to archive this item?');">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-900">Archive</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No items found in stock.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
