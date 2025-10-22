@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">🛒 My Cart</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(empty($cart))
        <p>Your cart is empty.</p>
        <a href="{{ url()->previous() }}" class="btn btn-primary">Go Back</a>
    @else
        <form id="bulkRemoveForm" method="POST" action="">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Item Name</th>
                            <th>Price</th>
                            <th width="150">Quantity</th>
                            <th>Subtotal</th>
                            <th width="100">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cart as $id => $item)
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_items[]" value="{{ $id }}" class="item-checkbox">
                                </td>
                                <td>{{ $item['name'] }}</td>
                                <td>₱{{ number_format($item['price'], 2) }}</td>
                                <td>
                                 <form action="{{ route(strpos(Route::currentRouteName(), 'franchisee_staff.') === 0 ? 'franchisee_staff.cart.update' : 'franchisee.cart.update', ['id' => $id]) }}" method="POST" class="d-flex align-items-center">
                                 @csrf
                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="{{ $item['stock_quantity'] }}" class="form-control form-control-sm w-50 me-2" required>
                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                                </td>

                                <td>₱{{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                                <td>
                                    <form action="{{ route(strpos(Route::currentRouteName(), 'franchisee_staff.') === 0 ? 'franchisee_staff.cart.remove' : 'franchisee.cart.remove', $id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove this item?')">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Select All & Remove Selected --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <button type="button" id="removeSelectedBtn" class="btn btn-danger btn-sm" disabled>
                        🗑 Remove Selected
                    </button>
                </div>
                <h4>Total: ₱{{ number_format($total, 2) }}</h4>
            </div>
        </form>

        {{-- Proceed to Checkout --}}
        <div class="text-end mt-4">
            <a href="{{ route(strpos(Route::currentRouteName(), 'franchisee_staff.') === 0 ? 'franchisee_staff.cart.checkout' : 'franchisee.cart.checkout') }}" 
               class="btn btn-success btn-lg">
                Proceed to Checkout →
            </a>
        </div>
    @endif
</div>

{{-- JavaScript Section --}}
<script>
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const removeSelectedBtn = document.getElementById('removeSelectedBtn');
    const bulkRemoveForm = document.getElementById('bulkRemoveForm');

    // Select All functionality
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

    // Remove Selected click event
    removeSelectedBtn?.addEventListener('click', function() {
        const selectedItems = [...document.querySelectorAll('.item-checkbox:checked')].map(cb => cb.value);

        if (selectedItems.length === 0) return alert('No items selected.');
        if (!confirm('Remove selected items from cart?')) return;

        // Dynamically create hidden forms for each selected item
        selectedItems.forEach(id => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route(strpos(Route::currentRouteName(), 'franchisee_staff.') === 0 ? 'franchisee_staff.cart.remove' : 'franchisee.cart.remove', 'ID_PLACEHOLDER') }}".replace('ID_PLACEHOLDER', id);
            
            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = '{{ csrf_token() }}';
            
            form.appendChild(token);
            document.body.appendChild(form);
            form.submit();
        });
    });
</script>
@endsection
