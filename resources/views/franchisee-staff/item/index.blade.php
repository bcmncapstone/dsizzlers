@extends('layouts.franchisee-staff')

@section('content')
<div class="items-page">
    {{-- Page Header --}}
    <div class="items-page-header">
        <h1>🍽️ Items</h1>
        <p>Browse and purchase available menu items</p>
    </div>

    {{-- Filter Section --}}
    <div class="items-filter-section">
        <h3>🔍 Filter & Sort</h3>
        <form method="GET" action="{{ route(request()->route()->getName()) }}" class="items-filter-form">
            <div class="items-filter-group">
                <label for="item_category" class="items-filter-label">Category</label>
                <select name="item_category" id="item_category" class="items-filter-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" {{ $selectedCategory == $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="items-filter-group">
                <label for="sort_by" class="items-filter-label">Sort By</label>
                <select name="sort_by" id="sort_by" class="items-filter-select" onchange="this.form.submit()">
                    <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Name</option>
                    <option value="price" {{ $sortBy == 'price' ? 'selected' : '' }}>Price</option>
                    <option value="quantity" {{ $sortBy == 'quantity' ? 'selected' : '' }}>Quantity</option>
                </select>
            </div>

            <div class="items-filter-group">
                <label for="sort_order" class="items-filter-label">Order</label>
                <select name="sort_order" id="sort_order" class="items-filter-select" onchange="this.form.submit()">
                    <option value="asc" {{ $sortOrder == 'asc' ? 'selected' : '' }}>Ascending ↑</option>
                    <option value="desc" {{ $sortOrder == 'desc' ? 'selected' : '' }}>Descending ↓</option>
                </select>
            </div>
        </form>
    </div>

    {{-- Items Grid --}}
    <h2 class="items-grid-title">Available Items</h2>
    
    @if ($items->count() > 0)
        <div class="items-grid">
            @foreach ($items as $item)
                <div class="item-card">
                    {{-- Item Image --}}
                    @if (!empty($item->item_images) && count($item->item_images) > 0)
                        <img src="{{ media_url($item->item_images[0]) }}" alt="{{ $item->item_name }}" class="item-card-image">
                    @else
                        <img src="{{ asset('images/default-item.png') }}" alt="Default Image" class="item-card-image">
                    @endif

                    {{-- Item Details --}}
                    <div class="item-card-body">
                        <h3 class="item-card-name">{{ $item->item_name }}</h3>
                        <p class="item-card-description">{{ $item->item_description }}</p>
                        @if (!empty($item->item_category) && $item->item_category !== 'none')
                            <div class="item-card-category">{{ ucfirst($item->item_category) }}</div>
                        @endif
                        
                        <div class="item-card-price">₱{{ number_format($item->price, 2) }}</div>
                        <small class="item-card-stock">
                            @if ($item->stock_quantity > 0)
                                Available: <strong>{{ $item->stock_quantity }}</strong>
                            @else
                                <span style="color: #f44336;">Out of Stock</span>
                            @endif
                        </small>

                        {{-- Action Buttons --}}
                        <div class="item-card-actions">
                            @php
                                $prefix = strpos(Route::currentRouteName(), 'franchisee_staff.') === 0 ? 'franchisee_staff' : 'franchisee';
                            @endphp

                            {{-- Add to Cart Form --}}
                            <form action="{{ route($prefix . '.cart.add', $item->item_id) }}" method="POST">
                                @csrf
                                <input 
                                    type="number" 
                                    name="quantity" 
                                    value="1" 
                                    min="1" 
                                    max="{{ $item->stock_quantity }}" 
                                    id="list-quantity-{{ $item->item_id }}"
                                    class="item-quantity-input"
                                    @if($item->stock_quantity == 0) disabled @endif
                                >
                                <button 
                                    type="submit" 
                                    class="item-btn item-btn-cart"
                                    @if($item->stock_quantity == 0) disabled @endif
                                >
                                    🛒 Add to Cart
                                </button>
                            </form>

                            {{-- Buy Now Form --}}
                            <form action="{{ route($prefix . '.cart.checkout') }}" method="GET" class="buy-now-form">
                                <input type="hidden" name="items[0][item_id]" value="{{ $item->item_id }}">
                                <input type="hidden" name="items[0][quantity]" value="1" class="buy-now-quantity" data-item-id="{{ $item->item_id }}">
                                <button 
                                    type="submit" 
                                    class="item-btn item-btn-buy"
                                    @if($item->stock_quantity == 0) disabled @endif
                                >
                                    💳 Buy Now
                                </button>
                            </form>

                            {{-- View Details Link --}}
                            <a href="{{ route('franchisee_staff.item.show', $item->item_id) }}" class="item-btn item-btn-view">
                                👁️ View Details
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="items-grid-empty">
            <p>📭 No items found.</p>
            <p style="font-size: 14px; opacity: 0.7;">Try adjusting your filters or browse all items.</p>
        </div>
    @endif
</div>
<script>
    // Ensure Buy Now uses the same quantity as the list quantity input
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.buy-now-form').forEach(function (form) {
            form.addEventListener('submit', function () {
                var hiddenQty = form.querySelector('.buy-now-quantity');
                if (!hiddenQty) return;

                var itemId = hiddenQty.getAttribute('data-item-id');
                var qtyInput = document.getElementById('list-quantity-' + itemId);
                if (qtyInput && qtyInput.value) {
                    hiddenQty.value = qtyInput.value;
                }
            });
        });
    });
</script>
@endsection