@extends('layouts.franchisor-staff')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
            <div class="p-4 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Update Item Quantity</h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Adjust quantity for {{ $item->item_name }}
                        </p>
                    </div>
                    <a href="{{ route('franchisor-staff.stock.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back
                    </a>
                </div>
            </div>
        </div>

        <!-- Item Information -->
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden mb-4">
            <div class="p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Item Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Item Name</label>
                        <p class="text-base text-gray-900">{{ $item->item_name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Category</label>
                        <p class="text-base text-gray-900">{{ $item->item_category }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Price</label>
                        <p class="text-base text-gray-900">₱{{ number_format($item->price, 2) }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Current Quantity</label>
                        <p class="text-base font-semibold text-gray-900">{{ $item->stock_quantity }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Status</label>
                        <p>
                            @if($item->stock_quantity == 0)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Out of Stock</span>
                            @elseif($item->stock_quantity <= 10)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Low Stock</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">In Stock</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Update Form -->
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <div class="p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Enter New Quantity</h3>
                
                <form method="POST" action="{{ route('franchisor-staff.stock.update', $item->item_id) }}" id="stockForm">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="new_quantity" class="block text-sm font-medium text-gray-700 mb-2">
                            New Quantity <span class="text-red-500">*</span>
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
                                   value="{{ old('new_quantity', $item->stock_quantity) }}" 
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
                            Use + and − buttons or enter quantity manually
                        </p>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notes (Optional)
                        </label>
                        <textarea name="notes" 
                                  id="notes" 
                                  rows="3"
                                  placeholder="e.g., Restocking, Adjustment, Spoilage"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                    </div>

                    @if($item->stock_quantity == 0)
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                            <p class="text-sm text-red-700">
                                <strong>Warning:</strong> This item is out of stock.
                            </p>
                        </div>
                    @endif

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('franchisor-staff.stock.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                onclick="return confirmUpdate()"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Proceed & Update
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
        const itemName = "{{ $item->item_name }}";
        const currentQuantity = "{{ $item->stock_quantity }}";
        
        return confirm(`Are you sure you want to update ${itemName} from ${currentQuantity} to ${newQuantity}?`);
    }
</script>
@endsection
