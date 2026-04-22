@extends('layouts.franchisee-staff')

@section('content')
@php
    $itemImage = data_get($stock, 'item.item_images.0');

    $stockStatusLabel = 'In Stock';
    $stockStatusClass = 'stock-edit-status-ok';

    if ((int) $stock->current_quantity === 0) {
        $stockStatusLabel = 'Out of Stock';
        $stockStatusClass = 'stock-edit-status-out';
    } elseif ((int) $stock->current_quantity <= (int) $stock->minimum_quantity) {
        $stockStatusLabel = 'Low Stock';
        $stockStatusClass = 'stock-edit-status-low';
    }
@endphp

<div class="stock-edit-page">
    <div class="stock-edit-shell">
        <div class="stock-edit-header-card">
            <div>
                <p class="stock-edit-eyebrow">Stock Management</p>
                <h1>Update Item Quantity</h1>
                <p class="stock-edit-subtitle">
                    Review the current stock details and update the inventory record for
                    <strong>{{ $stock->item->item_name }}</strong>.
                </p>
            </div>

            <a href="{{ route('franchisee-staff.stock.index') }}" class="stock-edit-back-link">
                Back to Stock
            </a>
        </div>

        @if(session('error'))
            <div class="stock-edit-alert stock-edit-alert-error js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="stock-edit-alert stock-edit-alert-error">
                <strong>Please fix the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="stock-edit-grid">
            <aside class="stock-edit-sidebar">
                <section class="stock-edit-card">
                    <div class="stock-edit-item-top">
                        <div class="stock-edit-thumb-wrap">
                            @if ($itemImage)
                                <img src="{{ media_url($itemImage) }}" alt="{{ $stock->item->item_name }}" class="stock-edit-thumb">
                            @else
                                <div class="stock-edit-thumb stock-edit-thumb-fallback">No image</div>
                            @endif
                        </div>

                        <div class="stock-edit-item-copy">
                            <p class="stock-edit-section-label">Item</p>
                            <h2>{{ $stock->item->item_name }}</h2>
                            <p class="stock-edit-item-category">
                                {{ !empty($stock->item->item_category) ? ucfirst($stock->item->item_category) : 'Uncategorized' }}
                            </p>
                            <span class="stock-edit-status {{ $stockStatusClass }}">{{ $stockStatusLabel }}</span>
                        </div>
                    </div>

                    <div class="stock-edit-stat-list">
                        <div class="stock-edit-stat">
                            <span class="stock-edit-stat-label">Current Quantity</span>
                            <strong>{{ $stock->current_quantity }}</strong>
                        </div>

                        <div class="stock-edit-stat">
                            <span class="stock-edit-stat-label">Minimum Stock</span>
                            <strong>{{ $stock->minimum_quantity }}</strong>
                        </div>
                    </div>
                </section>
            </aside>

            <section class="stock-edit-main">
                <section class="stock-edit-card">
                    <div class="stock-edit-card-head">
                        <h3>Adjust Quantity</h3>
                        <p>Set the new stock level and leave a short note if needed.</p>
                    </div>

                    <form method="POST" action="{{ route('franchisee-staff.stock.update', $stock->stock_id) }}" id="stockForm" class="stock-edit-form">
                        @csrf

                        <div class="stock-edit-field">
                            <div class="stock-edit-label-row">
                                <label for="new_quantity">New Quantity</label>
                            </div>

                            <div class="stock-edit-stepper">
                                <button type="button" onclick="decrementQuantity()" aria-label="Decrease quantity">-</button>
                                <input type="number" name="new_quantity" id="new_quantity" value="{{ old('new_quantity', $stock->current_quantity) }}" min="0" required>
                                <button type="button" onclick="incrementQuantity()" aria-label="Increase quantity">+</button>
                            </div>
                        </div>

                        <div class="stock-edit-field">
                            <label for="notes">Notes (Optional)</label>
                            <textarea name="notes" id="notes" rows="4" placeholder="e.g., Restocking, adjustment, spoilage">{{ old('notes') }}</textarea>
                        </div>

                        <div class="stock-edit-actions">
                            <a href="{{ route('franchisee-staff.stock.index') }}" class="stock-edit-btn-secondary">Cancel</a>
                            <button type="submit" class="stock-edit-btn-primary">Update Stock</button>
                        </div>
                    </form>
                </section>
            </section>
        </div>
    </div>
</div>

@include('admin.stock.partials.edit-styles')

<script>
    (function() {
        const currentQuantity = {{ (int) $stock->current_quantity }};

        function getQuantityInput() {
            return document.getElementById('new_quantity');
        }

        window.incrementQuantity = function() {
            const input = getQuantityInput();
            if (!input) return;
            input.value = (parseInt(input.value, 10) || 0) + 1;
        };

        window.decrementQuantity = function() {
            const input = getQuantityInput();
            if (!input) return;
            const currentValue = parseInt(input.value, 10) || 0;
            if (currentValue > 0) {
                input.value = currentValue - 1;
            }
        };

        document.querySelectorAll('.js-flash-alert').forEach(function(alert) {
            const timeout = Number(alert.dataset.timeout || 3000);
            window.setTimeout(function() {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                window.setTimeout(function() {
                    alert.remove();
                }, 300);
            }, timeout);
        });

        const quantityInput = getQuantityInput();
        const stockForm = document.getElementById('stockForm');
        if (!stockForm || !quantityInput) return;

        stockForm.addEventListener('submit', async function(event) {
            if (stockForm.dataset.confirmApproved === 'true') {
                delete stockForm.dataset.confirmApproved;
                return;
            }

            event.preventDefault();

            const newQuantity = quantityInput.value;
            const itemName = @json($stock->item->item_name);
            const confirmed = await window.showConfirmModal(
                `Are you sure you want to update ${itemName} from ${currentQuantity} to ${newQuantity}?`,
                { confirmText: 'Update Stock' }
            );

            if (confirmed) {
                stockForm.dataset.confirmApproved = 'true';
                stockForm.requestSubmit();
            }
        });
    })();
</script>
@endsection
