@extends($layout ?? 'layouts.app')

@section('content')
<div class="cart-page">
    <div class="cart-container">
        {{-- Header --}}
        <div class="cart-header">
            <h2>Cart</h2>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div style="padding: 0 25px;">
                <div class="cart-success-alert js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">✓ {{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div style="padding: 0 25px;">
                <div class="cart-error-alert js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">✕ {{ session('error') }}</div>
            </div>
        @endif

        {{-- Empty Cart --}}
        @if(empty($cart))
            <div class="cart-empty-message">
                <p>Your cart is empty</p>
                <a href="{{ url()->previous() }}" class="cart-empty-link">← Continue Shopping</a>
            </div>
        @else

        {{-- Bulk Remove Container --}}
        <div id="bulkRemoveForm" class="cart-bulk-section">
            <div class="cart-table-wrapper">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" class="cart-checkbox">
                            </th>
                            <th>Item Image</th>
                            <th>Item Name</th>
                            <th>Price</th>
                            <th width="150">Quantity</th>
                            <th>Subtotal</th>
                            <th width="80">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cart as $id => $item)
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_items[]" value="{{ $id }}" class="cart-checkbox">
                                </td>
                                <td>
                                    <div class="cart-item-images-container">
                                        @forelse ($item['item_images'] as $img)
                                            <img src="{{ media_url($img) }}" alt="{{ $item['name'] }}" class="cart-item-image">
                                        @empty
                                            <img src="{{ asset('images/default-item.png') }}" alt="Default Image" class="cart-item-image">
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <span class="cart-item-name">{{ $item['name'] }}</span>
                                </td>
                                <td>
                                    <span class="cart-item-price">₱{{ number_format($item['price'], 2) }}</span>
                                    </td>

                                {{-- Update Quantity Form --}}
                                <td>
                                    <form 
                                        action="{{ route(strpos(Route::currentRouteName(), 'franchisee_staff.') === 0 
                                            ? 'franchisee_staff.cart.update' 
                                            : 'franchisee.cart.update', ['id' => $id]) }}" 
                                        method="POST" 
                                        class="cart-quantity-form"
                                    >
                                        @csrf
                                        <input type="number" 
                                               name="quantity" 
                                               value="{{ $item['quantity'] }}"
                                               min="1" 
                                               max="{{ $item['stock_quantity'] }}"
                                               class="cart-quantity-input" 
                                               required>
                                        <button type="submit" class="cart-btn-update">Update</button>
                                    </form>
                                </td>

                                <td>
                                    <span class="cart-item-subtotal">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                                </td>

                                {{-- Remove Button --}}
                                <td>
                                    <form action="{{ route(strpos(Route::currentRouteName(), 'franchisee_staff.') === 0 
                                        ? 'franchisee_staff.cart.remove' 
                                        : 'franchisee.cart.remove', $id) }}" 
                                          method="POST" 
                                          style="display: inline;">
                                        @csrf
                                        <button type="submit" class="cart-btn-remove"
                                            onclick="return confirm('Remove this item?')">✕</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer with Remove Selected & Total --}}
            <div class="cart-footer">
                <div class="cart-actions-left">
                    <button type="button" id="removeSelectedBtn" class="cart-btn-remove-selected" disabled>
                        Remove Selected
                    </button>
                </div>

                <div class="cart-total">
                    <span class="cart-total-label">Total:</span>
                    <span class="cart-total-amount">₱{{ number_format($total, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Checkout Section --}}
        <div class="cart-checkout-section">
            <a href="{{ route(strpos(Route::currentRouteName(), 'franchisee_staff.') === 0 
                ? 'franchisee_staff.cart.checkout' 
                : 'franchisee.cart.checkout') }}" 
               class="cart-btn-checkout">
                Proceed to Checkout
            </a>
        </div>

        @endif
    </div>
</div>

{{-- JavaScript --}}
<script>
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('input[name="selected_items[]"]');
    const removeSelectedBtn = document.getElementById('removeSelectedBtn');

    // Select All
    selectAllCheckbox?.addEventListener('change', function() {
        itemCheckboxes.forEach(cb => cb.checked = this.checked);
        toggleRemoveButton();
    });

    // Enable/disable Remove Selected button
    itemCheckboxes.forEach(cb => cb.addEventListener('change', toggleRemoveButton));

    function toggleRemoveButton() {
        const anyChecked = [...itemCheckboxes].some(cb => cb.checked);
        removeSelectedBtn.disabled = !anyChecked;
    }

    // Remove Selected → auto-create POST forms for each item
    removeSelectedBtn?.addEventListener('click', function() {
        const selectedItems = [...document.querySelectorAll('input[name="selected_items[]"]:checked')].map(cb => cb.value);

        if (selectedItems.length === 0) return alert('No items selected.');
        if (!confirm('Remove selected items from cart?')) return;

        selectedItems.forEach(id => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route(strpos(Route::currentRouteName(), 'franchisee_staff.') === 0 
                ? 'franchisee_staff.cart.remove'
                : 'franchisee.cart.remove', 'ID_PLACEHOLDER') }}"
                .replace('ID_PLACEHOLDER', id);

            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = '{{ csrf_token() }}';

            form.appendChild(token);
            document.body.appendChild(form);
            form.submit();
        });
    });

    // Auto-hide flash alerts using controller-provided timeout
    document.querySelectorAll('.js-flash-alert').forEach(function(alertEl) {
        const timeout = parseInt(alertEl.dataset.timeout || '3000', 10);

        setTimeout(function() {
            alertEl.style.transition = 'opacity 0.4s ease';
            alertEl.style.opacity = '0';

            setTimeout(function() {
                alertEl.remove();
            }, 400);
        }, Number.isFinite(timeout) ? timeout : 3000);
    });
</script>
@endsection
