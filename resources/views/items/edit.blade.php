@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="form-container" style="max-width: 800px;">
        <h2>Edit Item</h2>

        @php
            $prefix = auth()->guard('franchisor_staff')->check() ? 'franchisor-staff' : 'admin';
        @endphp

        @if ($errors->any())
            <div class="alert alert-error">
                <strong>✕ Please fix the highlighted fields.</strong>
                <ul style="margin: 8px 0 0 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route($prefix . '.items.update', $item->item_id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Item Images Section -->
            <div class="item-images-section">
                <h3>Item Images</h3>
                <p class="form-section-description">Upload up to 3 images (First one is required)</p>
                
                <div class="form-group">
                    <label class="form-label">Image 1 *</label>
                    <input type="file" name="item_image[]" accept="image/*" class="form-control" required>
                    @error('item_image')
                        <span class="form-error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Image 2 (Optional)</label>
                    <input type="file" name="item_image[]" accept="image/*" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Image 3 (Optional)</label>
                    <input type="file" name="item_image[]" accept="image/*" class="form-control">
                </div>

                <!-- Current Images Display -->
                @forelse ($item->item_images as $img)
                    <img src="{{ asset('storage/' . $img) }}" alt="Current image" width="120" class="me-1 mb-1">
                @empty
                    No images
                @endforelse
            </div>

            <!-- Basic Information -->
            <div class="form-group">
                <label class="form-label" for="item_name">Item Name *</label>
                <input type="text" name="item_name" id="item_name" class="form-control" required value="{{ old('item_name', $item->item_name) }}" placeholder="e.g., Sizzling Burger">
                @error('item_name')
                    <span class="form-error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="item_description">Description</label>
                <textarea name="item_description" id="item_description" class="form-control" placeholder="Describe your item..." rows="4">{{ old('item_description', $item->item_description) }}</textarea>
                @error('item_description')
                    <span class="form-error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Pricing & Inventory Grid -->
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="price">Price (₱) *</label>
                    <input type="number" step="0.01" name="price" id="price" class="form-control" required value="{{ old('price', $item->price) }}" placeholder="0.00">
                    @error('price')
                        <span class="form-error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="stock_quantity">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" class="form-control" required value="{{ old('stock_quantity', $item->stock_quantity) }}" placeholder="0">
                    @error('stock_quantity')
                        <span class="form-error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Category -->
            <div class="form-group">
                <label class="form-label" for="item_category">Category</label>
                <input type="text" name="item_category" id="item_category" class="form-control" value="{{ old('item_category', $item->item_category) }}" placeholder="e.g., Main Course, Appetizer, Dessert">
                @error('item_category')
                    <span class="form-error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="form-actions">
                <a href="{{ route($prefix . '.items.index') }}" class="btn btn-secondary">← Back to Items</a>
                <button type="submit" class="btn btn-primary">🍽️ Update Item</button>
            </div>
        </form>
    </div>
</div>

@endsection
