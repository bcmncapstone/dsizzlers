@extends('layouts.franchisee-staff')

@section('content')
<div class="item-detail-page">
    <div class="item-detail-wrapper">
        <!-- Left: Image Gallery -->
        <div class="item-gallery-container">
            <!-- Main Image -->
            <div class="item-main-image-wrapper">
                @if (!empty($item->item_images) && count($item->item_images) > 0)
                    <img id="mainImage" src="{{ media_url($item->item_images[0]) }}" alt="{{ $item->item_name }}" class="item-main-image">
                @else
                    <img id="mainImage" src="{{ asset('images/default-item.png') }}" alt="Default Image" class="item-main-image">
                @endif

                <!-- Image Navigation Arrows -->
                @if (!empty($item->item_images) && count($item->item_images) > 1)
                    <div class="item-image-nav">
                        <button id="prevBtn" class="item-image-nav-btn" type="button" title="Previous image">‹</button>
                        <button id="nextBtn" class="item-image-nav-btn" type="button" title="Next image">›</button>
                    </div>
                @endif
            </div>

            <!-- Thumbnail Images -->
            @if (!empty($item->item_images) && count($item->item_images) > 0)
                <div class="item-thumbnails-container">
                    @foreach ($item->item_images as $index => $image)
                        <div class="item-thumbnail-wrapper">
                            <img 
                                src="{{ media_url($image) }}" 
                                class="item-thumbnail @if ($index === 0) active @endif" 
                                alt="Thumbnail {{ $index + 1 }}"
                                data-index="{{ $index }}"
                                data-image="{{ media_url($image) }}"
                            >
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right: Product Details -->
        <div class="item-details-container">
            <!-- Product Title -->
            <h1 class="item-details-title">{{ $item->item_name }}</h1>

            <!-- Category Badge -->
            <div>
                <span class="item-details-category">{{ ucfirst($item->item_category) }}</span>
            </div>

            <!-- Price Section -->
            <div class="item-details-price-section">
                <p class="item-details-price">₱{{ number_format($item->price, 2) }}</p>
            </div>

            <!-- Stock Status -->
            <div class="item-details-stock-section">
                <div class="item-details-stock-item">
                    <span class="item-details-stock-label">Stock Available</span>
                    <span class="item-details-stock-value">{{ $item->stock_quantity }} units</span>
                </div>
            </div>

            <!-- Description -->
            <div>
                <h3 class="item-details-description-title">Description</h3>
                <p class="item-details-description-text">{{ $item->item_description }}</p>
            </div>

            <hr class="item-details-divider">

            @php
                $prefix = 'franchisee_staff';
            @endphp

            @if ($item->stock_quantity > 0)
                <!-- Quantity Selector -->
                <div class="item-quantity-section">
                    <label class="item-quantity-label">Quantity</label>
                    <div class="item-quantity-selector">
                        <button type="button" class="item-quantity-btn" id="decreaseQty">−</button>
                        <input 
                            type="number" 
                            id="quantityInput" 
                            value="1" 
                            min="1" 
                            max="{{ $item->stock_quantity }}" 
                            class="item-quantity-input"
                        >
                        <button type="button" class="item-quantity-btn" id="increaseQty">+</button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="item-actions-container">
                    <!-- Add to Cart -->
                    <form action="{{ route($prefix . '.cart.add', $item->item_id) }}" method="POST" id="addToCartForm">
                        @csrf
                        <input type="hidden" name="quantity" id="cartQuantity" value="1">
                        <button type="submit" class="item-action-btn item-action-btn-cart">
                            🛒 Add to Cart
                        </button>
                    </form>

                    <!-- Buy Now -->
                    <form action="{{ route($prefix . '.cart.checkout') }}" method="GET" id="buyNowForm">
                        <input type="hidden" name="items[0][item_id]" value="{{ $item->item_id }}">
                        <input type="hidden" name="items[0][quantity]" id="buyNowQuantity" value="1">
                        <button type="submit" class="item-action-btn item-action-btn-buy">
                            💳 Buy Now
                        </button>
                    </form>
                </div>
            @else
                <div class="item-out-of-stock">
                    Out of Stock - This item is currently unavailable.
                </div>
            @endif

            <!-- Back Link -->
            <a href="{{ route('franchisee_staff.item.index') }}" class="item-back-link">
                ← Back to Items
            </a>
        </div>
    </div>
</div>

<script>
    // Initialize quantity on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateQuantity();
    });

    // Thumbnail click to change main image
    document.querySelectorAll('.item-thumbnail').forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            // Remove active class from all thumbnails
            document.querySelectorAll('.item-thumbnail').forEach(img => {
                img.classList.remove('active');
            });
            
            // Add active class to clicked thumbnail
            this.classList.add('active');
            
            // Update main image
            document.getElementById('mainImage').src = this.dataset.image;
        });
    });

    // Next/Prev button functionality
    let currentIndex = 0;
    const thumbnails = document.querySelectorAll('.item-thumbnail');
    const totalThumbnails = thumbnails.length;

    document.getElementById('nextBtn')?.addEventListener('click', function() {
        currentIndex = (currentIndex + 1) % totalThumbnails;
        thumbnails[currentIndex].click();
    });

    document.getElementById('prevBtn')?.addEventListener('click', function() {
        currentIndex = (currentIndex - 1 + totalThumbnails) % totalThumbnails;
        thumbnails[currentIndex].click();
    });

    // Quantity controls
    document.getElementById('increaseQty')?.addEventListener('click', function() {
        const input = document.getElementById('quantityInput');
        const max = parseInt(input.max);
        if (parseInt(input.value) < max) {
            input.value = parseInt(input.value) + 1;
            updateQuantity();
        }
    });

    document.getElementById('decreaseQty')?.addEventListener('click', function() {
        const input = document.getElementById('quantityInput');
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
            updateQuantity();
        }
    });

    document.getElementById('quantityInput')?.addEventListener('change', updateQuantity);
    document.getElementById('quantityInput')?.addEventListener('input', updateQuantity);

    function updateQuantity() {
        const qty = document.getElementById('quantityInput').value;
        document.getElementById('cartQuantity').value = qty;
        document.getElementById('buyNowQuantity').value = qty;
    }
</script>

@endsection