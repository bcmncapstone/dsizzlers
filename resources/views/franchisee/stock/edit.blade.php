@extends('layouts.franchisee')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Adjust Stock</h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Update the remaining quantity for {{ $stock->item->item_name }}
                        </p>
                    </div>
                    <a href="{{ route('franchisee.stock.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Inventory
                    </a>
                </div>
            </div>
        </div>

        <!-- Item Information Card -->
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden mb-6">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Item Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Item Name</label>
                        <p class="text-base text-gray-900">{{ $stock->item->item_name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Category</label>
                        <p class="text-base text-gray-900">{{ $stock->item->item_category }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Current Quantity</label>
                        <p class="text-base font-semibold text-gray-900">{{ $stock->current_quantity }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Minimum Quantity</label>
                        <p class="text-base text-gray-900">{{ $stock->minimum_quantity }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Status</label>
                        <p>
                            @if($stock->status === 'out_of_stock')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Out of Stock
                                </span>
                            @elseif($stock->status === 'low_stock')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Low Stock
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    In Stock
                                </span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Last Updated</label>
                        <p class="text-base text-gray-900">{{ $stock->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Error Messages -->
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <ul class="list-disc list-inside text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Adjustment Form -->
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Adjust Stock Quantity</h3>
                
                <form method="POST" action="{{ route('franchisee.stock.update', $stock->stock_id) }}">
                    @csrf
                    
                    <div class="mb-6">
                        <label for="new_quantity" class="block text-sm font-medium text-gray-700 mb-2">
                            New Remaining Quantity <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center space-x-4">
                            <button type="button" 
                                    onclick="decrementQuantity()" 
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-md font-bold text-lg">
                                −
                            </button>
                            <input type="number" 
                                   name="new_quantity" 
                                   id="new_quantity" 
                                   value="{{ old('new_quantity', $stock->current_quantity) }}" 
                                   min="0"
                                   required
                                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-center text-lg font-semibold">
                            <button type="button" 
                                    onclick="incrementQuantity()" 
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-md font-bold text-lg">
                                +
                            </button>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            Use the + and − buttons or manually enter the remaining quantity.
                        </p>
                    </div>

                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notes (Optional)
                        </label>
                        <textarea name="notes" 
                                  id="notes" 
                                  rows="3"
                                  placeholder="e.g., Sales, Spoilage, Inventory count adjustment"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                        <p class="mt-2 text-sm text-gray-500">
                            Optional: Provide a reason for this adjustment (e.g., sales, spoilage, count correction)
                        </p>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    This will update your stock balance. Make sure the quantity is accurate.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('franchisee.stock.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Cancel
                        </a>
                        <button type="submit" 
                                onclick="return confirmUpdate()"
                                style="display: inline-flex !important; background-color: #2563eb !important;"
                                class="items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Confirm & Update Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function incrementQuantity() {
        const input = document.getElementById('new_quantity');
        input.value = parseInt(input.value) + 1;
    }

    function decrementQuantity() {
        const input = document.getElementById('new_quantity');
        const currentValue = parseInt(input.value);
        if (currentValue > 0) {
            input.value = currentValue - 1;
        }
    }

    function confirmUpdate() {
        const newQuantity = document.getElementById('new_quantity').value;
        const itemName = "{{ $stock->item->item_name }}";
        return confirm(`Are you sure you want to update the stock quantity for ${itemName} to ${newQuantity}?\n\nThis will update your inventory balance.`);
    }
</script>
@endsection
