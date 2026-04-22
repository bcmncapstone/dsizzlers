@extends('layouts.franchisor-staff')

@section('content')
@php
    $itemImage = data_get($item, 'item_images.0');

    $stockStatusLabel = 'In Stock';
    $stockStatusClass = 'stock-edit-status-ok';

    if ((int) $item->stock_quantity === 0) {
        $stockStatusLabel = 'Out of Stock';
        $stockStatusClass = 'stock-edit-status-out';
    } elseif ((int) $item->stock_quantity <= 10) {
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
                    <strong>{{ $item->item_name }}</strong>.
                </p>
            </div>

            <a href="{{ route('franchisor-staff.stock.index') }}" class="stock-edit-back-link">
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
                                <img src="{{ media_url($itemImage) }}" alt="{{ $item->item_name }}" class="stock-edit-thumb">
                            @else
                                <div class="stock-edit-thumb stock-edit-thumb-fallback">No image</div>
                            @endif
                        </div>

                        <div class="stock-edit-item-copy">
                            <p class="stock-edit-section-label">Item</p>
                            <h2>{{ $item->item_name }}</h2>
                            <p class="stock-edit-item-category">
                                {{ !empty($item->item_category) ? ucfirst($item->item_category) : 'Uncategorized' }}
                            </p>
                            <span class="stock-edit-status {{ $stockStatusClass }}">{{ $stockStatusLabel }}</span>
                        </div>
                    </div>

                    <div class="stock-edit-stat-list">
                        <div class="stock-edit-stat">
                            <span class="stock-edit-stat-label">Current Quantity</span>
                            <strong>{{ $item->stock_quantity }}</strong>
                        </div>

                        <div class="stock-edit-stat">
                            <span class="stock-edit-stat-label">Unit Price</span>
                            <strong>&#8369;{{ number_format($item->price, 2) }}</strong>
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

                    <form method="POST" action="{{ route('franchisor-staff.stock.update', $item->item_id) }}" id="stockForm" class="stock-edit-form">
                        @csrf

                        <div class="stock-edit-field">
                            <div class="stock-edit-label-row">
                                <label for="new_quantity">New Quantity</label>
                            </div>

                            <div class="stock-edit-stepper">
                                <button type="button" onclick="decrementQuantity()" aria-label="Decrease quantity">-</button>
                                <input
                                    type="number"
                                    name="new_quantity"
                                    id="new_quantity"
                                    value="{{ old('new_quantity', $item->stock_quantity) }}"
                                    min="0"
                                    required
                                >
                                <button type="button" onclick="incrementQuantity()" aria-label="Increase quantity">+</button>
                            </div>
                        </div>

                        <div class="stock-edit-field">
                            <label for="notes">Notes (Optional)</label>
                            <textarea
                                name="notes"
                                id="notes"
                                rows="4"
                                placeholder="e.g., Restocking, adjustment, spoilage"
                            >{{ old('notes') }}</textarea>
                        </div>

                        <div class="stock-edit-actions">
                            <a href="{{ route('franchisor-staff.stock.index') }}" class="stock-edit-btn-secondary">Cancel</a>
                            <button type="submit" class="stock-edit-btn-primary">Update Stock</button>
                        </div>
                    </form>
                </section>
            </section>
        </div>
    </div>
</div>

<style>
    .stock-edit-page {
        padding: 14px 12px 24px;
        background: linear-gradient(180deg, #fff8f2 0%, #f8fafc 100%);
        min-height: calc(100vh - 120px);
    }

    .stock-edit-shell {
        max-width: 900px;
        margin: 0 auto;
    }

    .stock-edit-header-card,
    .stock-edit-card,
    .stock-edit-alert {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.07);
        border: 1px solid #f0e2d8;
    }

    .stock-edit-header-card {
        padding: 18px 20px;
        margin-bottom: 14px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
    }

    .stock-edit-eyebrow {
        margin: 0 0 8px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: #b85c1e;
    }

    .stock-edit-header-card h1 {
        margin: 0;
        color: #0f172a;
        font-size: 24px;
        line-height: 1.05;
    }

    .stock-edit-subtitle {
        margin: 8px 0 0;
        max-width: 560px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.45;
    }

    .stock-edit-back-link {
        flex-shrink: 0;
        text-decoration: none;
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid #e7cdbb;
        color: #9a4f1d;
        font-weight: 700;
        font-size: 12px;
        background: #fffaf7;
        transition: 0.2s ease;
    }

    .stock-edit-back-link:hover {
        background: #fef1e6;
    }

    .stock-edit-alert {
        padding: 13px 15px;
        margin-bottom: 14px;
    }

    .stock-edit-alert-error {
        border-color: #fecaca;
        background: #fef2f2;
        color: #b91c1c;
    }

    .stock-edit-alert ul {
        margin: 10px 0 0 18px;
    }

    .stock-edit-grid {
        display: grid;
        grid-template-columns: 240px minmax(0, 1fr);
        gap: 14px;
        align-items: start;
    }

    .stock-edit-sidebar,
    .stock-edit-main {
        min-width: 0;
    }

    .stock-edit-card {
        padding: 15px;
    }

    .stock-edit-item-top {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .stock-edit-thumb-wrap {
        flex-shrink: 0;
    }

    .stock-edit-thumb {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #e7e5e4;
        background: #f8fafc;
        display: block;
    }

    .stock-edit-thumb-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 14px;
    }

    .stock-edit-section-label,
    .stock-edit-stat-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .stock-edit-section-label {
        margin: 0 0 8px;
        color: #b85c1e;
    }

    .stock-edit-item-copy h2 {
        margin: 0;
        color: #0f172a;
        font-size: 19px;
        line-height: 1.1;
    }

    .stock-edit-item-category {
        margin: 4px 0 10px;
        color: #64748b;
        font-size: 13px;
    }

    .stock-edit-status {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .stock-edit-status-ok {
        background: #ecfdf5;
        color: #166534;
    }

    .stock-edit-status-low {
        background: #fef3c7;
        color: #92400e;
    }

    .stock-edit-status-out {
        background: #fee2e2;
        color: #991b1b;
    }

    .stock-edit-stat-list {
        display: grid;
        gap: 10px;
        margin-top: 14px;
    }

    .stock-edit-stat {
        border: 1px solid #ece7e2;
        background: #fffaf7;
        border-radius: 12px;
        padding: 12px 14px;
    }

    .stock-edit-stat-label {
        color: #94a3b8;
        margin-bottom: 8px;
    }

    .stock-edit-stat strong {
        color: #0f172a;
        font-size: 18px;
        line-height: 1;
    }

    .stock-edit-note {
        margin-top: 12px;
        padding: 11px 13px;
        border-radius: 12px;
        background: #fff5eb;
        color: #7c2d12;
        line-height: 1.5;
        font-size: 12px;
    }

    .stock-edit-card-head {
        margin-bottom: 12px;
    }

    .stock-edit-card-head h3 {
        margin: 0;
        color: #0f172a;
        font-size: 19px;
        line-height: 1.1;
    }

    .stock-edit-card-head p {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.45;
    }

    .stock-edit-form {
        display: grid;
        gap: 12px;
    }

    .stock-edit-field label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
    }

    .stock-edit-label-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }

    .stock-edit-label-row label {
        margin-bottom: 0;
    }

    .stock-edit-helper {
        font-size: 11px;
        color: #64748b;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        padding: 5px 8px;
        font-weight: 600;
    }

    .stock-edit-stepper {
        display: grid;
        grid-template-columns: 42px 104px 42px;
        border: 1px solid #e7d8cc;
        border-radius: 14px;
        overflow: hidden;
        background: #fffaf7;
        width: min(100%, 188px);
    }

    .stock-edit-stepper button,
    .stock-edit-stepper input,
    .stock-edit-field textarea {
        border: 0;
        outline: none;
        font: inherit;
    }

    .stock-edit-stepper button {
        background: #fff4ea;
        color: #9a4f1d;
        cursor: pointer;
        font-size: 22px;
        line-height: 1;
        transition: 0.2s ease;
    }

    .stock-edit-stepper button:hover {
        background: #fee7d6;
    }

    .stock-edit-stepper input {
        width: 100%;
        min-width: 0;
        height: 48px;
        text-align: center;
        background: transparent;
        color: #0f172a;
        font-size: 20px;
        font-weight: 700;
        appearance: textfield;
        -moz-appearance: textfield;
    }

    .stock-edit-stepper input::-webkit-outer-spin-button,
    .stock-edit-stepper input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .stock-edit-field textarea {
        width: 100%;
        min-height: 78px;
        resize: vertical;
        box-sizing: border-box;
        padding: 10px 12px;
        border: 1px solid #e7d8cc;
        border-radius: 14px;
        background: #fffaf7;
        color: #0f172a;
        font-size: 13px;
    }

    .stock-edit-field textarea:focus,
    .stock-edit-stepper:focus-within {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
    }

    .stock-edit-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding-top: 2px;
    }

    .stock-edit-btn-secondary,
    .stock-edit-btn-primary {
        text-decoration: none;
        border: 0;
        border-radius: 999px;
        padding: 9px 16px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .stock-edit-btn-secondary {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #dbe2ea;
    }

    .stock-edit-btn-secondary:hover {
        background: #f1f5f9;
    }

    .stock-edit-btn-primary {
        background: #b85c1e;
        color: #ffffff;
    }

    .stock-edit-btn-primary:hover {
        background: #9a4f1d;
    }

    @media (max-width: 900px) {
        .stock-edit-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .stock-edit-page {
            padding: 12px 8px 22px;
        }

        .stock-edit-header-card,
        .stock-edit-card {
            padding: 14px;
        }

        .stock-edit-header-card {
            flex-direction: column;
        }

        .stock-edit-header-card h1 {
            font-size: 22px;
        }

        .stock-edit-item-top {
            flex-direction: column;
        }

        .stock-edit-stepper {
            grid-template-columns: 40px minmax(90px, 1fr) 40px;
            width: 100%;
        }

        .stock-edit-stepper input {
            height: 44px;
            font-size: 18px;
        }

        .stock-edit-actions {
            grid-template-columns: 1fr;
            display: grid;
        }
    }
</style>

<script>
    (function() {
        const currentQuantity = {{ (int) $item->stock_quantity }};

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
            const itemName = @json($item->item_name);
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
