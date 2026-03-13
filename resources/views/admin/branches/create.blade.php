@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-2xl mx-auto">
        
        <!-- Header -->
        <div class="bg-white shadow-sm p-8 rounded-lg mb-6">
            <h1>Add Branch</h1>
            <p class="header-subtitle">Create a new branch location with franchisee details</p>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="error-alert">
                <h3>Please fix the following errors:</h3>
                <ul class="error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form -->
        <form id="branchForm" action="{{ route('admin.branches.store') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-sm p-8 rounded-lg">
            @csrf

            <!-- Leaflet Branch Location -->
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
            <div class="form-group">
                <label class="form-label" style="margin-bottom: 16px;">Branch Location</label>

                <!-- Map Container -->
                <div style="position: relative; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08); overflow: hidden; border: 1px solid #e5e7eb;">
                    <div id="map" style="height: 400px; width: 100%; background: #e9ecef; position: relative;"></div>
                    <div id="map-loading" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.9); z-index: 1000;">
                        <div style="text-align: center;">
                            <div style="border: 4px solid #f0f0f0; border-top: 4px solid #FF5722; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 12px;"></div>
                            <p style="color: #999; font-size: 14px;">Loading map...</p>
                        </div>
                    </div>
                </div>

                <!-- Helper Text -->
                <p class="field-help">Use the Address field below or click on the map to set the branch location. Drag the marker to adjust position.</p>

                <!-- Address Input & Suggestions (Shopee-like) -->
                <div style="margin-top:12px; position:relative;">
                    <label class="form-label">Address</label>
                    <input type="text" name="location_address" id="location_address" class="form-input" placeholder="Search address or drag the pin" value="{{ old('location') }}">
                    <div id="location_suggestions" class="suggestions-box" style="display:none; position:absolute; z-index:3000; background:white; width:100%; border:1px solid #e5e7eb; border-radius:6px; max-height:240px; overflow:auto;"></div>
                </div>

                <!-- Hidden Fields -->
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                <!-- Hidden location field required by server validation -->
                <input type="hidden" name="location" id="location_hidden">

                @error('latitude')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>


            <!-- Franchisee Information Section -->
            <div class="section-divider">
                <h2 style="font-size: 16px; font-weight: 600; color: #1a1a1a; border-left: 3px solid #FF5722; padding-left: 12px;">
                    Franchisee Information
                </h2>
            </div>

            <!-- Select existing franchisee to auto-fill details -->
            <div class="form-group">
                <label for="franchisee_select" class="form-label">Select Franchisee</label>
                <select id="franchisee_select" name="franchisee_id" class="form-input" onchange="fillFranchiseeInfo(this)">
                    <option value="">-- Select Franchisee --</option>
                    @isset($franchisees)
                        @foreach($franchisees as $fr)
                            <option value="{{ $fr->franchisee_id }}" data-name="{{ $fr->franchisee_name }}" data-email="{{ $fr->franchisee_email }}" data-contact="{{ $fr->franchisee_contactNo }}">{{ $fr->franchisee_name }} — {{ $fr->franchisee_email }}</option>
                        @endforeach
                    @endisset
                </select>
                <p class="field-help">Choose an existing franchisee to auto-fill details</p>
            </div>

            <!-- First Name -->
            <div class="form-group">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" name="first_name" id="first_name" placeholder="Franchisee's first name" 
                       value="{{ old('first_name') }}" class="form-input" required>
                @error('first_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <!-- Last Name -->
            <div class="form-group">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" name="last_name" id="last_name" placeholder="Franchisee's last name" 
                       value="{{ old('last_name') }}" class="form-input" required>
                @error('last_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" placeholder="franchisee@example.com" 
                       value="{{ old('email') }}" class="form-input" required>
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <!-- Contact Number -->
            <div class="form-group">
                <label for="contact_number" class="form-label">Contact Number</label>
                <input type="text" name="contact_number" id="contact_number" placeholder="09xxxxxxxxx" 
                       value="{{ old('contact_number') }}" class="form-input" required>
                @error('contact_number')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <!-- Contract Section -->
            <div class="section-divider">
                <h2 style="font-size: 16px; font-weight: 600; color: #1a1a1a; border-left: 3px solid #FF5722; padding-left: 12px;">
                    Contract Information
                </h2>
            </div>

            <!-- Contract File Upload -->
            <div class="form-group">
                <label for="contract_file" class="form-label">Upload Contract</label>
                <div class="file-input-wrapper">
                    <input type="file" name="contract_file" id="contract_file" class="file-input" required>
                    <span class="file-input-label" id="contract_filename">Choose file or drag and drop</span>
                    <button type="button" id="contract_clear_btn" title="Remove selected file" style="margin-left:8px; background:transparent; border:1px solid #e5e7eb; border-radius:6px; padding:6px 8px; cursor:pointer;">✕</button>
                </div>
                <p class="field-help">Supported formats: PDF, DOC, DOCX (Max 10MB)</p>
                @error('contract_file')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <!-- Contract Expiration -->
            <div class="form-group">
                <label for="contract_expiration" class="form-label">Contract Expiration Date</label>
                  <input type="date" name="contract_expiration" id="contract_expiration" 
                      value="{{ old('contract_expiration') }}" class="form-input" required
                      min="{{ date('Y-m-d') }}">
                @error('contract_expiration')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <script>
                function fillFranchiseeInfo(select) {
                    const opt = select.options[select.selectedIndex];
                    const first = document.getElementById('first_name');
                    const last = document.getElementById('last_name');
                    const email = document.getElementById('email');
                    const contact = document.getElementById('contact_number');

                    if (!opt || !opt.value) {
                        first.value = '';
                        last.value = '';
                        email.value = '';
                        contact.value = '';
                        return;
                    }

                    const fullName = opt.dataset.name || '';
                    const nameParts = fullName.trim().split(/\s+/);
                    const firstName = nameParts.length ? nameParts.shift() : '';
                    const lastName = nameParts.length ? nameParts.join(' ') : '';

                    first.value = firstName;
                    last.value = lastName;
                    email.value = opt.dataset.email || '';
                    contact.value = opt.dataset.contact || '';
                }
            </script>

            <!-- Form Buttons -->
            <div class="form-actions">
                <a href="{{ route('admin.branches.index') }}" class="cancel-button">Cancel</a>
                <button type="submit" class="submit-button">Add Branch</button>
            </div>
        </form>

    </div>
</div>

<!-- Loading animation + map CSS overrides -->
<style>
    /* Loading spinner */
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    #map-loading { animation: fadeOut 0.5s ease 2s forwards; }
    @keyframes fadeOut { to { opacity: 0; pointer-events: none; } }

    /* Strong map-scoped overrides to prevent global CSS from shrinking tiles */
    /* Force tile & image sizes inside the map container */
    #map .leaflet-tile, #map .leaflet-tile img, #map .leaflet-pane img, #map .leaflet-image-layer img {
        width: 256px !important;
        height: 256px !important;
        max-width: none !important;
        border-radius: 0 !important;
        display: block !important;
        object-fit: none !important;
    }

    /* Ensure the map container has explicit dimensions and isn't affected by global transforms */
    #map { width: 100% !important; height: 400px !important; display: block !important; }

    /* z-index stacking to keep map above top bars */
    .map-wrapper { position: relative; z-index: 2000; }
    .map-center-pin { z-index: 2100; pointer-events: none; }
    #map-loading { z-index: 2050 !important; }
</style>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const defaultLocation = [9.65, 123.85];
    const latInput = document.getElementById('latitude');
    const lonInput = document.getElementById('longitude');

    const initialLat = parseFloat(latInput.value) || defaultLocation[0];
    const initialLng = parseFloat(lonInput.value) || defaultLocation[1];

    // Initialize Map - minimal config for speed
    const map = L.map('map', { 
        center: [initialLat, initialLng], 
        zoom: 13,
        attributionControl: false,
        zoomControl: true
    });

    // Use OpenStreetMap Mapnik tiles with retry and better caching
    const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 19,
        minZoom: 2,
        crossOrigin: true,
        maxNativeZoom: 18,
        tileSize: 256,
        errorTileUrl: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
        updateWhenZooming: false,
        updateWhenIdle: true,
        keepBuffer: 2
    }).addTo(map);

    // Ensure each tile enforces correct sizing even if global CSS has !important rules
    if (tileLayer && tileLayer.on) {
        tileLayer.on('tileload', function(e) {
            try {
                const t = e.tile;
                t.style.setProperty('width', '256px', 'important');
                t.style.setProperty('height', '256px', 'important');
                t.style.setProperty('max-width', 'none', 'important');
                t.style.setProperty('border-radius', '0', 'important');
                t.style.setProperty('display', 'block', 'important');
                t.style.setProperty('object-fit', 'none', 'important');
            } catch (err) {
                // ignore
            }
        });

        // When all tiles loaded for the current view, remove loading overlay
        tileLayer.on('load', function() {
            const loader = document.getElementById('map-loading');
            if (loader) loader.style.display = 'none';
        });
    }

    // Also aggressively patch any img elements already in the DOM under #map
    setTimeout(() => {
        document.querySelectorAll('#map img').forEach(function(img){
            img.style.setProperty('width', '256px', 'important');
            img.style.setProperty('height', '256px', 'important');
            img.style.setProperty('max-width', 'none', 'important');
            img.style.setProperty('border-radius', '0', 'important');
            img.style.setProperty('display', 'block', 'important');
            img.style.setProperty('object-fit', 'none', 'important');
        });
    }, 100);

    // Pre-cache tiles for current view on load
    setTimeout(() => {
        map.invalidateSize(true);
        tileLayer.bringToFront();
        // trigger a tiny view reset after resize to ensure tiles cover entire container
        map.setView([initialLat, initialLng], map.getZoom());
    }, 50);

    // also listen for ready event just in case
    map.whenReady(() => {
        map.invalidateSize(true);
    });

    // Marker icon
    const markerIcon = L.icon({
        iconUrl: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iNDgiIHZpZXdCb3g9IjAgMCAzMiA0OCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMTYgMEM5LjU4NTggMCA0IDUuNTg1OCA0IDEyQzQgMjEuNzUgMTYgMzQgMTYgMzRDMTYgMzQgMjggMjEuNzUgMjggMTJDMjggNS41ODU4IDIyLjQxNDIgMCAxNiAwWiIgZmlsbD0iI0ZGNTcyMiIvPjxjaXJjbGUgY3g9IjE2IiBjeT0iMTIiIHI9IjQiIGZpbGw9IiNmZmZmZmYiLz48L3N2Zz4=',
        iconSize: [32, 48],
        iconAnchor: [16, 48],
        popupAnchor: [0, -50]
    });

    const marker = L.marker([initialLat, initialLng], { 
        draggable: true, 
        icon: markerIcon 
    }).addTo(map);

    function setCoordinates(lat, lng) {
        latInput.value = lat.toFixed(6);
        lonInput.value = lng.toFixed(6);
    }

    setCoordinates(initialLat, initialLng);

    // Reverse geocode helper: fills Address input and hidden location
    function reverseGeocode(lat, lng) {
        if (!lat || !lng) return;
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.display_name) {
                    const addrEl = document.getElementById('location_address');
                    if (addrEl) addrEl.value = data.display_name;
                    const locHidden = document.getElementById('location_hidden');
                    if (locHidden) locHidden.value = data.display_name;
                }
            })
            .catch(err => { console.error('reverse geocode error', err); });
    }

    marker.on('dragend', function () {
        const pos = marker.getLatLng();
        setCoordinates(pos.lat, pos.lng);
        reverseGeocode(pos.lat, pos.lng);
    });

    map.on('click', function(e) {
        marker.setLatLng([e.latlng.lat, e.latlng.lng]);
        map.panTo(e.latlng);
        setCoordinates(e.latlng.lat, e.latlng.lng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
    });

    // No top search box: address suggestions below drive the search

    // Address suggestions (Shopee-like) using the same Nominatim API
    const addressInput = document.getElementById('location_address');
    const suggestionsBox = document.getElementById('location_suggestions');

    // debounce helper
    function debounce(fn, wait) {
        let t;
        return function(...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    function clearSuggestions() {
        suggestionsBox.innerHTML = '';
        suggestionsBox.style.display = 'none';
    }

    function renderSuggestions(list) {
        suggestionsBox.innerHTML = '';
        if (!list || !list.length) { clearSuggestions(); return; }
        list.forEach(item => {
            const div = document.createElement('div');
            div.className = 'suggestion-item';
            div.style.padding = '8px 12px';
            div.style.cursor = 'pointer';
            div.style.borderBottom = '1px solid #f3f4f6';
            div.textContent = item.display_name;
            div.dataset.lat = item.lat;
            div.dataset.lon = item.lon;
            div.addEventListener('click', function() {
                const lat = parseFloat(this.dataset.lat);
                const lon = parseFloat(this.dataset.lon);
                // set map and marker
                map.setView([lat, lon], 16);
                marker.setLatLng([lat, lon]);
                setCoordinates(lat, lon);
                addressInput.value = this.textContent;
                // populate hidden location field
                const locHidden = document.getElementById('location_hidden');
                if (locHidden) locHidden.value = this.textContent;
                clearSuggestions();
            });
            suggestionsBox.appendChild(div);
        });
        suggestionsBox.style.display = 'block';
    }

    const fetchSuggestions = debounce(function(query) {
        if (!query || !query.trim()) { clearSuggestions(); return; }
        fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                renderSuggestions(data);
            })
            .catch(err => { console.error('suggestion error', err); clearSuggestions(); });
    }, 250);

    // wire suggestions to address input
    addressInput.addEventListener('input', function(e) {
        const q = this.value;
        fetchSuggestions(q);
    });

    // close suggestions when clicking outside
    document.addEventListener('click', function(e){
        if (!suggestionsBox.contains(e.target) && e.target !== addressInput) clearSuggestions();
    });

    // also allow selecting the top result when pressing Enter in addressInput
    addressInput.addEventListener('keydown', function(e){
        if (e.key === 'Enter') {
            e.preventDefault();
            const q = addressInput.value.trim();
            if (!q) return;
            fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.length) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);
                        map.setView([lat, lon], 16);
                        marker.setLatLng([lat, lon]);
                        setCoordinates(lat, lon);
                        addressInput.value = data[0].display_name;
                        // populate hidden location field for server validation
                        const locHidden = document.getElementById('location_hidden');
                        if (locHidden) locHidden.value = data[0].display_name;
                    }
                })
                .catch(() => {});
            clearSuggestions();
        }
    });

    // (top search removed) use Address suggestions and Enter handling on the Address input

    // Contract file UI: show filename and allow clearing selection
    const contractInput = document.getElementById('contract_file');
    const contractFilename = document.getElementById('contract_filename');
    const contractClearBtn = document.getElementById('contract_clear_btn');

    function updateContractLabel() {
        if (!contractInput || !contractFilename) return;
        const f = contractInput.files && contractInput.files[0];
        if (f) {
            contractFilename.textContent = f.name;
            // when a file is selected, mark not required (server will accept or validate)
            contractInput.required = false;
        } else {
            contractFilename.textContent = 'Choose file or drag and drop';
        }
    }

    if (contractInput) {
        contractInput.addEventListener('change', updateContractLabel);
    }

    if (contractClearBtn) {
        contractClearBtn.addEventListener('click', function() {
            if (!contractInput) return;
            contractInput.value = '';
            contractInput.required = false; // allow submit without file
            updateContractLabel();
        });
    }

    // Ensure hidden location is populated on form submit
    const branchForm = document.getElementById('branchForm');
    if (branchForm) {
        branchForm.addEventListener('submit', function(e) {
            const locHidden = document.getElementById('location_hidden');
            if (locHidden) {
                if (addressInput && addressInput.value.trim()) {
                    locHidden.value = addressInput.value.trim();
                } else if (latInput && lonInput && latInput.value && lonInput.value) {
                    locHidden.value = `${latInput.value},${lonInput.value}`;
                }
            }
        });
    }

});
</script>
@endsection
