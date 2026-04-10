@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="form-container" style="max-width: 800px;">
        <h2>Add Item</h2>

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

        <form method="POST" action="{{ route($prefix . '.items.store') }}" enctype="multipart/form-data" id="createItemForm">
            @csrf

            <!-- Item Images Section -->
<div class="item-images-section">
    <h3>Item Images</h3>
    <p class="form-section-description">Upload up to 3 images</p>
    
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
                <div id="duplicate-warning" style="display:none; margin-top:8px; padding:10px 14px; background:#fef3c7; border:1px solid #fcd34d; border-radius:6px; color:#92400e; font-size:13px;">
                    <strong>⚠ An item with this name already exists.</strong>
                    <span id="duplicate-details"></span>
                </div>
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
                    <input type="number" name="stock_quantity" id="stock_quantity" class="form-control" required min="1" value="{{ old('stock_quantity') }}" placeholder="0">
                    @error('stock_quantity')
                        <span class="form-error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Category -->
             <div class="form-group">
                <label class="form-label" for="item_category">Category: *</label>
                <select name="item_category" id="item_category" class="form-control" required>
                    <option value="" disabled {{ old('item_category') ? '' : 'selected' }}>Select category</option>
                    <option value="food" {{ old('item_category') === 'food' ? 'selected' : '' }}>Food</option>
                    <option value="supplies" {{ old('item_category') === 'supplies' ? 'selected' : '' }}>Supplies</option>
                    <option value="package" {{ old('item_category') === 'package' ? 'selected' : '' }}>Package</option>

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

<!-- Duplicate Item Modal -->
<div id="duplicate-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:white; border-radius:10px; padding:28px 32px; max-width:480px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="margin:0 0 8px; font-size:18px; color:#1f2937;">Duplicate Item Found</h3>
        <p style="margin:0 0 16px; color:#6b7280; font-size:14px;">An item with the name <strong id="modal-item-name"></strong> already exists in your inventory.</p>
        <div id="modal-item-info" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; padding:12px 16px; margin-bottom:20px; font-size:13px;">
            <div><strong>Current Stock:</strong> <span id="modal-stock"></span></div>
            <div><strong>Price:</strong> ₱<span id="modal-price"></span></div>
            <div><strong>Category:</strong> <span id="modal-category"></span></div>
        </div>
        <p style="margin:0 0 16px; color:#4b5563; font-size:14px;">What would you like to do?</p>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a id="modal-edit-btn" href="#" class="btn btn-primary" style="flex:1; text-align:center; padding:10px 16px; text-decoration:none; background:#2563eb; color:#fff; border-radius:6px; font-weight:600; font-size:14px;">
                Update Existing Item
            </a>
            <button type="button" id="modal-create-btn" class="btn btn-secondary" style="flex:1; padding:10px 16px; border-radius:6px; font-weight:600; font-size:14px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; cursor:pointer;">
                Create New Entry
            </button>
        </div>
        <button type="button" id="modal-cancel-btn" style="display:block; margin:12px auto 0; background:none; border:none; color:#9ca3af; cursor:pointer; font-size:13px;">Cancel</button>
    </div>
</div>

@endsection
<script>
document.addEventListener('DOMContentLoaded', function () {
    var checkUrl = @json(route($prefix . '.items.check-duplicate'));
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var duplicateData = null;
    var forceCreate = false;

    var itemNameInput = document.getElementById('item_name');
    var warning = document.getElementById('duplicate-warning');
    var details = document.getElementById('duplicate-details');
    var form = document.getElementById('createItemForm');

    var modal = document.getElementById('duplicate-modal');
    var modalItemName = document.getElementById('modal-item-name');
    var modalStock = document.getElementById('modal-stock');
    var modalPrice = document.getElementById('modal-price');
    var modalCategory = document.getElementById('modal-category');
    var modalEditBtn = document.getElementById('modal-edit-btn');
    var modalCreateBtn = document.getElementById('modal-create-btn');
    var modalCancelBtn = document.getElementById('modal-cancel-btn');

    var debounceTimer = null;

    function checkDuplicate() {
        var name = itemNameInput.value.trim();
        if (name.length < 2) {
            warning.style.display = 'none';
            duplicateData = null;
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            fetch(checkUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ item_name: name })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.exists) {
                    duplicateData = data;
                    details.textContent = ' (Stock: ' + data.item.stock_quantity + ', Price: ₱' + parseFloat(data.item.price).toFixed(2) + ')';
                    warning.style.display = 'block';
                } else {
                    duplicateData = null;
                    warning.style.display = 'none';
                }
            })
            .catch(function () {
                duplicateData = null;
                warning.style.display = 'none';
            });
        }, 400);
    }

    itemNameInput.addEventListener('input', function () {
        forceCreate = false;
        checkDuplicate();
    });

    itemNameInput.addEventListener('blur', checkDuplicate);

    function showModal() {
        modalItemName.textContent = duplicateData.item.item_name;
        modalStock.textContent = duplicateData.item.stock_quantity;
        modalPrice.textContent = parseFloat(duplicateData.item.price).toFixed(2);
        modalCategory.textContent = duplicateData.item.item_category ? duplicateData.item.item_category.charAt(0).toUpperCase() + duplicateData.item.item_category.slice(1) : 'Uncategorized';
        modalEditBtn.href = duplicateData.edit_url;
        modal.style.display = 'flex';
    }

    function hideModal() {
        modal.style.display = 'none';
    }

    form.addEventListener('submit', function (e) {
        if (duplicateData && !forceCreate) {
            e.preventDefault();
            showModal();
        }
    });

    modalCreateBtn.addEventListener('click', function () {
        forceCreate = true;
        hideModal();
        form.submit();
    });

    modalCancelBtn.addEventListener('click', hideModal);

    modal.addEventListener('click', function (e) {
        if (e.target === modal) hideModal();
    });

    // Image upload handling
    document.querySelectorAll('.image-upload-group').forEach(function(group) {
        var input = group.querySelector('.image-input');
        var fileInfo = group.querySelector('.file-info');
        var fileName = group.querySelector('.file-name');
        var removeBtn = group.querySelector('.remove-btn');

        input.addEventListener('change', function () {
            if (this.files.length > 0) {
                fileName.textContent = this.files[0].name;
                fileInfo.style.display = 'flex';
                fileInfo.style.alignItems = 'center';
                fileInfo.style.gap = '10px';
            }
        });

        removeBtn.addEventListener('click', function () {
            input.value = '';
            fileInfo.style.display = 'none';
            fileName.textContent = '';
        });
    });

    // Hide error alerts after 5 seconds
    var errorAlert = document.querySelector('.alert.alert-error');
    if (errorAlert) {
        setTimeout(function () {
            errorAlert.style.transition = 'opacity 0.5s ease';
            errorAlert.style.opacity = '0';
            setTimeout(function () { errorAlert.remove(); }, 500);
        }, 5000);
    }
});
</script>