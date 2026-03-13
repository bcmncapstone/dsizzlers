@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="form-container" style="max-width: 800px;">
        <h2>Add New Item</h2>

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

        <form method="POST" action="{{ route($prefix . '.items.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- Item Images Section -->
<div class="item-images-section">
    <h3>Item Images</h3>
    <p class="form-section-description">Upload up to 3 images (First one is required)</p>
    
    @for ($i = 0; $i < 3; $i++)
        <div class="form-group image-upload-group">
            <label class="form-label">
                Image {{ $i + 1 }} {{ $i == 0 ? '*' : '(Optional)' }}
            </label>

            <input 
                type="file" 
                name="item_image[]" 
                accept="image/*" 
                class="form-control image-input"
                {{ $i == 0 ? 'required' : '' }}
            >

            <!-- File Info + Remove Button -->
            <div class="file-info" style="display:none; margin-top:8px;">
                <span class="file-name"></span>
                <button type="button" class="remove-btn">✕ Remove</button>
            </div>
        </div>
    @endfor
</div>
            <!-- Basic Information -->
            <div class="form-group">
                <label class="form-label" for="item_name">Item Name *</label>
                <input type="text" name="item_name" id="item_name" class="form-control" required value="{{ old('item_name') }}" placeholder="e.g., Pork Sisig">
                @error('item_name')
                    <span class="form-error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="item_description">Description *</label>
                <textarea name="item_description" id="item_description" class="form-control" required value="{{ old('item_description') }}" placeholder="Describe your item..." rows="4">{{ old('item_description') }}</textarea>
                @error('item_description')
                    <span class="form-error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Pricing & Inventory Grid -->
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="price">Price (₱) *</label>
                    <input type="number" step="0.01" min="0.01" name="price" id="price" class="form-control" required value="{{ old('price') }}" placeholder="0.00">
                    @error('price')
                        <span class="form-error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="stock_quantity">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" class="form-control" required value="{{ old('stock_quantity') }}" placeholder="0">
                    @error('stock_quantity')
                        <span class="form-error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Category -->
             <div class="form-group">
                <label class="form-label" for="item_category">Category: *</label>
                <select name="item_category" class="form-control" required onchange="toggleFields(this.value)">
                    <option value="none">-</option>
                    <option value="toolsandequipment">Tools and Equipment</option>
                    <option value="meat">Meat</option>
                    <option value="drygoods">Dry Goods</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="form-actions">
                <a href="{{ route($prefix . '.items.index') }}" class="btn btn-secondary">← Back to Items</a>
                <button type="submit" class="btn btn-primary">Save Item</button>
            </div>
        </form>
    </div>
</div>

@endsection
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.image-upload-group').forEach(function(group) {

        const input = group.querySelector('.image-input');
        const fileInfo = group.querySelector('.file-info');
        const fileName = group.querySelector('.file-name');
        const removeBtn = group.querySelector('.remove-btn');

        // When file is selected
        input.addEventListener('change', function () {
            if (this.files.length > 0) {
                fileName.textContent = this.files[0].name;
                fileInfo.style.display = 'flex';
                fileInfo.style.alignItems = 'center';
                fileInfo.style.gap = '10px';
            }
        });

        // Remove file
        removeBtn.addEventListener('click', function () {
            input.value = '';
            fileInfo.style.display = 'none';
            fileName.textContent = '';
        });

    });

});
document.addEventListener('DOMContentLoaded', function () {
    // Hide error alerts after 5 seconds (5000 ms)
    const errorAlert = document.querySelector('.alert.alert-error');
    if (errorAlert) {
        setTimeout(() => {
            // Fade out effect
            errorAlert.style.transition = "opacity 0.5s ease";
            errorAlert.style.opacity = '0';

            // Remove from DOM after fade
            setTimeout(() => {
                errorAlert.remove();
            }, 500);
        }, 5000); // 5 seconds
    }
});
</script>