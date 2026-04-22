@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<div class="branch-info-page">
    <div class="branch-info-container">
        {{-- Header --}}
        <div class="branch-info-header">
            <h2>Contract Information</h2>
        </div>

        {{-- Branch Information or Empty State --}}
        @if($branches->isNotEmpty())
            @foreach($branches as $branch)
                <div class="branch-info-content" style="margin-bottom: 20px;">
                    <div class="branch-info-item">
                        <span class="branch-info-label">Contract #</span>
                        <span class="branch-info-value">{{ $loop->iteration }}</span>
                    </div>

                    <div class="branch-info-item">
                        <span class="branch-info-label">Location</span>
                        <div class="branch-info-value" style="width: 100%;">
                            <div>{{ $branch->location }}</div>
                            <div
                                class="franchisee-contract-map"
                                id="branch-map-{{ $branch->branch_id }}"
                                data-location="{{ $branch->location }}"
                                style="height: 260px; width: 100%; margin-top: 12px; border-radius: 10px; overflow: hidden; border: 1px solid #e5e7eb;"
                            ></div>
                            <p style="margin-top: 8px; color: #6b7280; font-size: 13px;">This map is view-only and shows your assigned branch location.</p>
                        </div>
                    </div>

                    <div class="branch-info-item">
                        <span class="branch-info-label">Franchisee Name</span>
                        <span class="branch-info-value">{{ $branch->first_name }} {{ $branch->last_name }}</span>
                    </div>

                    <div class="branch-info-item">
                        <span class="branch-info-label">Email</span>
                        <span class="branch-info-value">{{ $branch->email }}</span>
                    </div>

                    <div class="branch-info-item">
                        <span class="branch-info-label">Contact Number</span>
                        <span class="branch-info-value">{{ $branch->contact_number }}</span>
                    </div>

                    <div class="branch-info-item">
                        <span class="branch-info-label">Contract</span>
                        <div class="branch-info-value">
                            @if($branch->contract_file)
                                <div class="branch-info-links">
                                    <a href="{{ route('franchisee.branches.contract', $branch->branch_id) }}" target="_blank" class="branch-info-link">
                                        View
                                    </a>
                                    <span class="branch-info-separator">|</span>
                                    <a href="{{ route('franchisee.branches.contract', ['id' => $branch->branch_id, 'mode' => 'download']) }}" class="branch-info-link">
                                        Download
                                    </a>
                                </div>
                            @else
                                <span style="color: #999;">No contract uploaded</span>
                            @endif
                        </div>
                    </div>

                    <div class="branch-info-item">
                        <span class="branch-info-label">Contract Start Date</span>
                        <span class="branch-info-value">{{ optional($branch->contract_start_date)->format('Y-m-d') ?? 'N/A' }}</span>
                    </div>

                    <div class="branch-info-item">
                        <span class="branch-info-label">Contract Expiration</span>
                        <span class="branch-info-value">{{ optional($branch->contract_expiration)->format('Y-m-d') ?? 'N/A' }}</span>
                    </div>
                </div>
            @endforeach
        @else
            {{-- Empty State --}}
            <div class="branch-empty-container">
                <p class="branch-empty-message">
                    No active contract assigned to your account yet.
                </p>
                <p class="branch-empty-contact">Please contact your administrator to assign a contract to your account.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapElements = document.querySelectorAll('.franchisee-contract-map');

    if (!mapElements.length || typeof L === 'undefined') {
        return;
    }

    const markerIcon = L.icon({
        iconUrl: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iNDgiIHZpZXdCb3g9IjAgMCAzMiA0OCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMTYgMEM5LjU4NTggMCA0IDUuNTg1OCA0IDEyQzQgMjEuNzUgMTYgMzQgMTYgMzRDMTYgMzQgMjggMjEuNzUgMjggMTJDMjggNS41ODU4IDIyLjQxNDIgMCAxNiAwWiIgZmlsbD0iI0ZGNTcyMiIvPjxjaXJjbGUgY3g9IjE2IiBjeT0iMTIiIHI9IjQiIGZpbGw9IiNmZmZmZmYiLz48L3N2Zz4=',
        iconSize: [32, 48],
        iconAnchor: [16, 48],
        popupAnchor: [0, -40]
    });

    const initializeMap = (element, lat, lng, label) => {
        const map = L.map(element, {
            center: [lat, lng],
            zoom: 15,
            attributionControl: false,
            zoomControl: true,
            dragging: false,
            scrollWheelZoom: false,
            doubleClickZoom: false,
            boxZoom: false,
            keyboard: false,
            tap: false,
            touchZoom: false
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 19
        }).addTo(map);

        L.marker([lat, lng], { icon: markerIcon })
            .addTo(map)
            .bindPopup(label);

        setTimeout(() => map.invalidateSize(), 100);
    };

    mapElements.forEach(function (element) {
        const location = element.dataset.location || '';

        if (!location) {
            element.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#6b7280;font-size:13px;">Map location is not available.</div>';
            return;
        }

        fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(location)}`)
            .then(response => response.json())
            .then(results => {
                if (!Array.isArray(results) || !results.length) {
                    throw new Error('Location not found');
                }

                const result = results[0];
                initializeMap(element, parseFloat(result.lat), parseFloat(result.lon), location);
            })
            .catch(() => {
                element.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#6b7280;font-size:13px;padding:0 16px;text-align:center;">Unable to load the map for this branch right now.</div>';
            });
    });
});
</script>
@endpush
