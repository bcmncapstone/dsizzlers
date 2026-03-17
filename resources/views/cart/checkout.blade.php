@extends($layout ?? 'layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow-sm sm:rounded-lg p-8 mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
            <p class="text-sm text-gray-600 mt-2">Review your order and complete payment</p>
        </div>

        <!-- Error Alert -->
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">
                <p class="text-red-700 font-semibold">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Order Summary -->
        <div class="bg-white shadow-sm sm:rounded-lg p-8 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Order Summary</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left text-sm font-semibold text-gray-900 pb-3">Image</th>
                            <th class="text-left text-sm font-semibold text-gray-900 pb-3">Item</th>
                            <th class="text-center text-sm font-semibold text-gray-900 pb-3">Qty</th>
                            <th class="text-right text-sm font-semibold text-gray-900 pb-3">Price</th>
                            <th class="text-right text-sm font-semibold text-gray-900 pb-3">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cart as $item)
                            <tr class="border-b border-gray-100">
                                <td class="py-3">
                                    @forelse ($item['item_images'] as $img)
                                        <img src="{{ media_url($img) }}" width="40" alt="{{ $item['name'] }}" class="rounded object-cover">
                                    @empty
                                        <img src="{{ asset('images/default-item.png') }}" alt="Default Image" width="40" class="rounded">
                                    @endforelse
                                </td>
                                <td class="py-3 text-sm text-gray-900">{{ $item['name'] }}</td>
                                <td class="py-3 text-sm text-gray-900 text-center">{{ $item['quantity'] }}</td>
                                <td class="py-3 text-sm text-gray-900 text-right">₱{{ number_format($item['price'], 2) }}</td>
                                <td class="py-3 text-sm font-semibold text-orange-600 text-right">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 mt-4 pt-4 text-right">
                <p class="text-lg font-bold text-gray-900">Total: <span class="text-orange-600">₱{{ number_format($total, 2) }}</span></p>
            </div>
        </div>

        <!-- Customer Info Form -->
        <form action="{{ route($cartKey . '.cart.placeOrder') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-sm sm:rounded-lg p-8">
            @csrf

            {{-- Pass Buy Now items if they exist --}}
            @if(request()->has('items'))
                <input type="hidden" name="buy_now_items" value="{{ json_encode(request()->input('items')) }}">
            @endif

            <h2 class="text-lg font-semibold text-gray-900 mb-6">Delivery Information</h2>

            <!-- Full Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-gray-900 mb-2">Full Name <span class="text-red-500">*</span></label>
                <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" name="name" id="name" value="{{ old('name', $checkoutPrefill['name'] ?? '') }}" required>
            </div>

            <!-- Contact Number -->
            <div class="mb-6">
                <label for="contact" class="block text-sm font-semibold text-gray-900 mb-2">Contact Number <span class="text-red-500">*</span></label>
                <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" name="contact" id="contact" value="{{ old('contact', $checkoutPrefill['contact'] ?? '') }}" required>
            </div>

            <!-- Delivery Address -->
            <div class="mb-6 pb-6 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Delivery Address</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="region" class="block text-sm font-semibold text-gray-900 mb-2">Region <span class="text-red-500">*</span></label>
                        <select id="region" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" required>
                            <option value="">Select Region</option>
                        </select>
                    </div>

                    <div>
                        <label for="province" class="block text-sm font-semibold text-gray-900 mb-2">Province <span class="text-red-500">*</span></label>
                        <select id="province" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" disabled required>
                            <option value="">Select Province</option>
                        </select>
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-semibold text-gray-900 mb-2">City / Municipality <span class="text-red-500">*</span></label>
                        <select id="city" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" disabled required>
                            <option value="">Select City</option>
                        </select>
                    </div>

                    <div>
                        <label for="barangay" class="block text-sm font-semibold text-gray-900 mb-2">Barangay <span class="text-red-500">*</span></label>
                        <select id="barangay" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" disabled required>
                            <option value="">Select Barangay</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="street" class="block text-sm font-semibold text-gray-900 mb-2">Street Address <span class="text-red-500">*</span></label>
                        <input type="text" id="street" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="House No. / Street Name" required>
                    </div>
                </div>

                {{-- Hidden field that stores the full formatted address --}}
                <input type="hidden" name="address" id="fullAddress" value="{{ old('address', $checkoutPrefill['address'] ?? '') }}">

                @if(!empty($checkoutPrefill['address']))
                    <p class="mt-2 text-xs text-gray-600">Suggested address from your branch: {{ $checkoutPrefill['address'] }}</p>
                @endif
            </div>

            <!-- Payment Receipt -->
            <div class="mb-6">
                <label for="payment_receipt" class="block text-sm font-semibold text-gray-900 mb-2">Upload Payment Receipt <span class="text-red-500">*</span></label>
                <input type="file" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" name="payment_receipt" id="payment_receipt" accept="image/*" required onchange="previewReceipt(event)">
                <p class="text-xs text-gray-500 mt-2">Accepted formats: JPG, PNG, GIF (Max 5MB)</p>
            </div>

            <!-- Receipt Preview -->
            <div class="mb-6">
                <img id="receiptPreview" style="display:none; max-width:300px; border:1px solid #e5e7eb; padding:8px; border-radius:8px;">
            </div>

            <!-- Form Buttons -->
            <div class="flex gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route($cartKey . '.cart.index') }}" class="flex-1 px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 text-center">
                    Back to Cart
                </a>
                <button type="submit" class="flex-1 px-6 py-3 bg-orange-600 text-white font-semibold rounded-lg hover:bg-orange-700">
                    Place Order
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const prefilledAddress = @json(old('address', $checkoutPrefill['address'] ?? ''));

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

if (prefilledAddress) {
    document.getElementById('fullAddress').value = prefilledAddress;

    ['region', 'province', 'city', 'barangay', 'street'].forEach((fieldId) => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.required = false;
        }
    });

    const streetInput = document.getElementById('street');
    if (streetInput) {
        streetInput.value = prefilledAddress;
    }
}
</script>
@endsection
