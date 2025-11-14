@extends($layout ?? 'layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Checkout</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{--Order Summary --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong>Order Summary</strong>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>₱{{ number_format($item['price'], 2) }}</td>
                            <td>₱{{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="text-end">
                <h5>Total: ₱{{ number_format($total, 2) }}</h5>
            </div>
        </div>
    </div>

    {{--Customer Info --}}
    <form action="{{ route($cartKey . '.cart.placeOrder') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" name="name" id="name" required>
        </div>

        <div class="mb-3">
            <label for="contact" class="form-label">Contact Number</label>
            <input type="text" class="form-control" name="contact" id="contact" required>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Delivery Address</label>
            <textarea class="form-control" name="address" id="address" rows="2" required></textarea>
        </div>

        <div class="mb-3">
            <label for="payment_receipt" class="form-label">Upload Payment Receipt</label>
            <input type="file" class="form-control" name="payment_receipt" id="payment_receipt" accept="image/*" required onchange="previewReceipt(event)">
        </div>

        <div class="mb-3">
            <img id="receiptPreview" style="display:none;max-width:200px;border:1px solid #ddd;padding:4px;border-radius:5px;">
        </div>

        <button type="submit" class="btn btn-success w-100"> Place Order</button>
    </form>
</div>

<script>
function previewReceipt(event) {
    const preview = document.getElementById('receiptPreview');
    preview.src = URL.createObjectURL(event.target.files[0]);
    preview.style.display = 'block';
}
</script>
@endsection
