@extends('layouts.franchisee-staff')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <!-- Left: Image Gallery (Shopee Style) -->
        <div class="col-lg-5">
            <div class="row">
                <!-- Main Image -->
                <div class="col-12 mb-3">
                    <div id="mainImageContainer" style="background-color: #f5f5f5; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; height: 450px;">
                        @if (!empty($item->item_images) && count($item->item_images) > 0)
                            <img id="mainImage" src="{{ asset('storage/' . $item->item_images[0]) }}" alt="{{ $item->item_name }}" style="max-width: 100%; max-height: 100%; object-fit: contain; padding: 10px;">
                        @else
                            <img id="mainImage" src="{{ asset('images/default-item.png') }}" alt="Default Image" style="max-width: 100%; max-height: 100%; object-fit: contain; padding: 10px;">
                        @endif
                    </div>

                    <!-- Image Navigation Arrows -->
                    @if (!empty($item->item_images) && count($item->item_images) > 1)
                        <div style="position: relative; top: -225px; display: flex; justify-content: space-between; padding: 0 10px;">
                            <button id="prevBtn" class="btn btn-light" style="border-radius: 50%; width: 40px; height: 40px; padding: 0;">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button id="nextBtn" class="btn btn-light" style="border-radius: 50%; width: 40px; height: 40px; padding: 0;">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Thumbnail Images (Vertical) -->
                @if (!empty($item->item_images) && count($item->item_images) > 0)
                    <div class="col-12">
                        <div class="d-flex gap-2" style="overflow-x: auto; padding-bottom: 10px;">
                            @foreach ($item->item_images as $index => $image)
                                <div class="thumbnail-wrapper" style="flex-shrink: 0;">
                                    <img src="{{ asset('storage/' . $image) }}" 
                                         class="thumbnail-img @if ($index === 0) active @endif" 
                                         alt="Thumbnail {{ $index + 1 }}"
                                         data-index="{{ $index }}"
                                         data-image="{{ asset('storage/' . $image) }}"
                                         style="width: 90px; height: 90px; object-fit: cover; cursor: pointer; border: 3px solid #ddd; border-radius: 4px; transition: all 0.3s ease;">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Product Details -->
        <div class="col-lg-7 ps-4">
            <!-- Product Title -->
            <h1 class="mb-2" style="font-size: 28px; font-weight: 600;">{{ $item->item_name }}</h1>

            <!-- Category Badge -->
            <div class="mb-3">
                <span class="badge bg-light text-dark" style="font-size: 14px; padding: 6px 12px;">{{ ucfirst($item->item_category) }}</span>
            </div>

            <!-- Price Section -->
            <div class="mb-4" style="border-bottom: 1px solid #ddd; padding-bottom: 16px;">
                <div class="d-flex align-items-baseline gap-2">
                    <span style="font-size: 32px; color: #ff6b6b; font-weight: 700;">₱{{ number_format($item->price, 2) }}</span>
                </div>
            </div>

            <!-- Stock Status -->
            <div class="mb-4">
                <div class="row">
                    <div class="col-6">
                        <p class="mb-2" style="color: #666;">Stock Available</p>
                        <p class="mb-0" style="font-size: 20px; font-weight: 600; color: #ff6b6b;">{{ $item->stock_quantity }} units</p>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <h5 style="color: #333; margin-bottom: 10px;">Description</h5>
                <p style="color: #666; line-height: 1.6;">{{ $item->item_description }}</p>
            </div>

            <!-- Divider -->
            <hr style="margin: 20px 0;">

            @php
                $prefix = 'franchisee_staff';
            @endphp

            @if ($item->stock_quantity > 0)
                <!-- Quantity Selector -->
                <div class="mb-4">
                    <p class="mb-2" style="color: #666;">Quantity</p>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary" id="decreaseQty" style="width: 40px; height: 40px; padding: 0;">−</button>
                        <input type="number" id="quantityInput" value="1" min="1" max="{{ $item->stock_quantity }}" class="form-control text-center" style="width: 80px;">
                        <button type="button" class="btn btn-outline-secondary" id="increaseQty" style="width: 40px; height: 40px; padding: 0;">+</button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row gap-2">
                    <!-- Add to Cart -->
                    <div class="col-12">
                        <form action="{{ route($prefix . '.cart.add', $item->item_id) }}" method="POST" id="addToCartForm">
                            @csrf
                            <input type="hidden" name="quantity" id="cartQuantity" value="1">
                            <button type="submit" class="btn w-100" style="background-color: #ffb800; color: white; font-weight: 600; padding: 14px; font-size: 16px; border: none;">
                                <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                            </button>
                        </form>
                    </div>

                    <!-- Buy Now -->
                    <div class="col-12">
                        <form action="{{ route($prefix . '.cart.checkout') }}" method="GET">
    <input type="hidden" name="items[0][item_id]" value="{{ $item->item_id }}">
    <input type="hidden" name="items[0][quantity]" id="buyNowQuantity" value="1">
    <button type="submit" class="btn w-100" style="background-color: #28a745; color: white; font-weight: 600; padding: 14px; font-size: 16px; border: none;">
                                <i class="fas fa-bolt me-2"></i> Buy Now
                            </button>
</form>
                    </div>
                </div>
            @else
                <div class="alert alert-danger" role="alert">
                    <strong>Out of Stock</strong> - This item is currently unavailable.
                </div>
            @endif

            <!-- Back Button -->
            <div class="mt-4">
                <a href="{{ route('franchisee_staff.item.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Items
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Thumbnail click to change main image
    document.querySelectorAll('.thumbnail-img').forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            // Remove active class from all thumbnails
            document.querySelectorAll('.thumbnail-img').forEach(img => {
                img.style.borderColor = '#ddd';
            });
            
            // Add active class to clicked thumbnail
            this.style.borderColor = '#ff6b6b';
            
            // Update main image
            document.getElementById('mainImage').src = this.dataset.image;
        });
    });

    // Set first thumbnail as active
    document.querySelector('.thumbnail-img').style.borderColor = '#ff6b6b';

    // Next/Prev button functionality
    let currentIndex = 0;
    const thumbnails = document.querySelectorAll('.thumbnail-img');
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

    function updateQuantity() {
        const qty = document.getElementById('quantityInput').value;
        document.getElementById('cartQuantity').value = qty;
        document.getElementById('buyNowQuantity').value = qty;
    }
</script>

@endsection