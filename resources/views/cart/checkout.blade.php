@extends($layout ?? 'layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Checkout</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Order Summary --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong>Order Summary</strong>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart as $item)
                        <tr>
                             <td>
                        @forelse ($item['item_images'] as $img)
                            <img src="{{ asset('storage/' . $img) }}" width="40" class="me-1 mb-1 rounded">
                        @empty
                            <img src="{{ asset('images/default-item.png') }}" alt="Default Image" width="40">
                        @endforelse
                    </td>
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

    {{-- Customer Info --}}
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

        {{-- Delivery Address --}}
        <div class="mb-3">
            <label class="form-label">Delivery Address</label>

            <div class="row g-2">
                <div class="col-md-6">
                    <select id="region" class="form-control" required>
                        <option value="">Select Region</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <select id="province" class="form-control" disabled required>
                        <option value="">Select Province</option>
                    </select>
                </div>

                <div class="col-md-6 mt-2">
                    <select id="city" class="form-control" disabled required>
                        <option value="">Select City</option>
                    </select>
                </div>

                <div class="col-md-6 mt-2">
                    <select id="barangay" class="form-control" disabled required>
                        <option value="">Select Barangay</option>
                    </select>
                </div>

                <div class="col-md-12 mt-2">
                    <input type="text" id="street" class="form-control" placeholder="House No. / Street Name" required>
                </div>
            </div>

            {{-- Hidden field that stores the full formatted address --}}
            <input type="hidden" name="address" id="fullAddress">
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
// Image preview
function previewReceipt(event) {
    const preview = document.getElementById('receiptPreview');
    preview.src = URL.createObjectURL(event.target.files[0]);
    preview.style.display = 'block';
}

// Load Regions
fetch("https://psgc.gitlab.io/api/regions/")
    .then(res => res.json())
    .then(data => {
        let region = document.getElementById("region");
        data.forEach(item => {
            region.innerHTML += `<option value="${item.name}" data-code="${item.code}">${item.name}</option>`;
        });
    });

// Region → Provinces
document.getElementById("region").addEventListener("change", function () {
    let code = this.options[this.selectedIndex].getAttribute('data-code');
    let province = document.getElementById("province");

    province.disabled = false;
    province.innerHTML = `<option value="">Loading...</option>`;

    fetch(`https://psgc.gitlab.io/api/regions/${code}/provinces/`)
        .then(res => res.json())
        .then(data => {
            province.innerHTML = `<option value="">Select Province</option>`;
            data.forEach(item => {
                province.innerHTML += `<option value="${item.name}" data-code="${item.code}">${item.name}</option>`;
            });
        });

    updateFullAddress();
});

// Province → Cities
document.getElementById("province").addEventListener("change", function () {
    let code = this.options[this.selectedIndex].getAttribute('data-code');
    let city = document.getElementById("city");

    city.disabled = false;
    city.innerHTML = `<option value="">Loading...</option>`;

    fetch(`https://psgc.gitlab.io/api/provinces/${code}/cities-municipalities/`)
        .then(res => res.json())
        .then(data => {
            city.innerHTML = `<option value="">Select City</option>`;
            data.forEach(item => {
                city.innerHTML += `<option value="${item.name}" data-code="${item.code}">${item.name}</option>`;
            });
        });

    updateFullAddress();
});

// City → Barangays
document.getElementById("city").addEventListener("change", function () {
    let code = this.options[this.selectedIndex].getAttribute('data-code');
    let barangay = document.getElementById("barangay");

    barangay.disabled = false;
    barangay.innerHTML = `<option value="">Loading...</option>`;

    fetch(`https://psgc.gitlab.io/api/cities-municipalities/${code}/barangays/`)
        .then(res => res.json())
        .then(data => {
            barangay.innerHTML = `<option value="">Select Barangay</option>`;
            data.forEach(item => {
                barangay.innerHTML += `<option value="${item.name}">${item.name}</option>`;
            });
        });

    updateFullAddress();
});

// Street Input
document.getElementById("street").addEventListener("input", updateFullAddress);

// Combine Full Address
function updateFullAddress() {
    const region = document.getElementById("region").value;
    const province = document.getElementById("province").value;
    const city = document.getElementById("city").value;
    const barangay = document.getElementById("barangay").value;
    const street = document.getElementById("street").value;

    let full = "";

    if (street) full += street + ", ";
    if (barangay) full += barangay + ", ";
    if (city) full += city + ", ";
    if (province) full += province + ", ";
    if (region) full += region;

    document.getElementById("fullAddress").value = full;
}
</script>
@endsection
