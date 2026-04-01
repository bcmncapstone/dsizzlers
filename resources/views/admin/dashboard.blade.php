{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        
        <!-- Welcome Header -->
        <div class="dashboard-header">
            <h1>Franchisor Dashboard</h1>
            <p>Overview of sales, orders, inventory health, and branch performance.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom: 16px;">
                <strong>✓</strong>
                {{ session('success') }}
            </div>
        @endif

        <!-- Summary of Reports -->
        <div class="sales-stats-grid">
            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Total Sales</div>
                    <div class="sales-stat-value">₱{{ number_format($totalSales ?? 0, 2) }}</div>
                </div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Sales This Month</div>
                    <div class="sales-stat-value">₱{{ number_format($salesThisMonth ?? 0, 2) }}</div>
                </div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Total Orders</div>
                    <div class="sales-stat-value">{{ intval($totalOrders ?? 0) }}</div>
                </div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Total Items Sold</div>
                    <div class="sales-stat-value">{{ intval($totalItemsSold ?? 0) }}</div>
                </div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Active Franchisees</div>
                    <div class="sales-stat-value">{{ intval($activeFranchisees ?? 0) }} / {{ intval($totalFranchisees ?? 0) }}</div>
                </div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Low + Out of Stock</div>
                    <div class="sales-stat-value">{{ intval($lowStockCount ?? 0) + intval($outOfStockCount ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Line Chart + Status Data -->
        <div class="sales-charts-grid">
            <div class="sales-chart-section">
                <h3 class="sales-chart-title">Sales Trend (Last 14 Days)</h3>
                <div class="sales-chart-container">
                    <canvas id="dashboardSalesTrendChart"></canvas>
                </div>
            </div>

            <div class="sales-chart-section">
                <h3 class="sales-chart-title">Order Status</h3>
                <div class="sales-table-overflow">
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>Order Status</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Pending</td>
                                <td>{{ intval($pendingOrders ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td>Preparing</td>
                                <td>{{ intval($preparingOrders ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td>Shipped</td>
                                <td>{{ intval($shippedOrders ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td>Delivered</td>
                                <td>{{ intval($deliveredOrders ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td>Cancelled</td>
                                <td>{{ intval($cancelledOrders ?? 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="sales-table-section" style="margin-bottom: 24px;">
            <div class="sales-table-header">
                <h3>Recent Orders Data</h3>
                <a href="{{ route('admin.manageOrder.index') }}" class="sales-table-pdf-btn">View All Orders</a>
            </div>

            <div class="sales-table-overflow">
                <table class="sales-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Order Status</th>
                            <th>Payment Status</th>
                            <th>Total</th>
                            <th>Order Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $dashboardRecentOrders = $recentOrders ?? collect(); @endphp
                        @forelse($dashboardRecentOrders as $order)
                            <tr>
                                <td>#{{ $order->order_id }}</td>
                                <td>{{ $order->name ?: 'N/A' }}</td>
                                <td>{{ ucfirst(strtolower($order->order_status ?? 'Pending')) }}</td>
                                <td>{{ ucfirst(strtolower($order->payment_status ?? 'Pending')) }}</td>
                                <td>₱{{ number_format($order->total_amount ?? 0, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="sales-table-empty">No recent orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Digital Marketing Upload -->
        <section class="marketing-section" style="margin-top: 28px;">
            <h3>Digital Marketing Management</h3>

            @if($errors->any())
                <div class="alert alert-error">
                    <ul style="margin:0;padding-left:18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="marketing-upload-container">
                <h4>Upload Digital Marketing</h4>

                <form method="POST" action="{{ route('digital-marketing.store') }}" enctype="multipart/form-data" id="dashboardMarketingForm">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Select Image:</label>

                        <input
                            type="file"
                            name="image"
                            id="dash_marketing_image"
                            accept="image/*"
                            required
                            class="file-input-hidden"
                            onchange="dashPreviewImage(event)"
                        >

                        <div class="button-group">
                            <button type="button" onclick="document.getElementById('dash_marketing_image').click()" class="btn btn-gallery">
                                Choose from Gallery
                            </button>
                            <button type="button" onclick="dashOpenCamera()" class="btn btn-camera">
                                Take Photo
                            </button>
                        </div>

                        <span id="dash-file-name" class="file-name-display"></span>

                        <div id="dash-image-preview" class="image-preview-container">
                            <img id="dash-preview-img" src="" alt="Preview" class="preview-image">
                            <button type="button" onclick="dashRemoveImage()" class="btn btn-remove">Remove Image</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="dash_description" class="form-label">Description (Optional):</label>
                        <textarea name="description" id="dash_description" rows="3" placeholder="Enter a description for this marketing post..." class="form-textarea"></textarea>
                    </div>

                    <button type="submit" class="btn btn-submit">Upload Post</button>
                </form>
            </div>

            <!-- Camera Modal -->
            <div id="dash-camera-modal" class="camera-modal">
                <div class="camera-modal-content">
                    <h3>Take Photo</h3>
                    <video id="dash-camera-stream" autoplay playsinline class="camera-stream"></video>
                    <canvas id="dash-camera-canvas" class="camera-canvas"></canvas>
                    <div class="modal-button-group">
                        <button type="button" onclick="dashCapturePhoto()" class="btn btn-camera">Capture</button>
                        <button type="button" onclick="dashCloseCamera()" class="btn btn-remove">Close</button>
                    </div>
                </div>
            </div>

            <!-- Existing Posts -->
            <div style="margin-top: 28px;">
                <h4>Uploaded Posts</h4>
                <div class="marketing-posts-container">
                    @forelse($digitalMarketing ?? [] as $post)
                        <div class="marketing-post">
                            <img
                                src="{{ is_object($post) && isset($post->image_path) ? media_url($post->image_path) : '' }}"
                                alt="Marketing Image"
                                id="admin-dash-img-{{ is_object($post) && isset($post->id) ? $post->id : '' }}"
                                class="marketing-post-image"
                                onclick="adminDashViewImage({{ is_object($post) && isset($post->id) ? $post->id : 'null' }})"
                                title="Click to view full size"
                                style="cursor:pointer;"
                            >
                            @if($post->description)
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <p class="marketing-post-description" id="admin-dash-desc-{{ $post->id }}" style="margin-bottom: 0;">{{ $post->description }}</p>
                                    <button type="button" class="btn btn-edit" onclick="adminDashCopyDescription({{ $post->id }})" title="Copy Description">Copy</button>
                                </div>
                            @endif
                            <small class="marketing-post-date">Posted on {{ $post->created_at->format('M d, Y h:i A') }}</small>
                            <div class="button-group" style="margin-top:8px;">
                                <button onclick="adminDashViewImage({{ $post->id }})" class="btn btn-gallery">View</button>
                                <a href="{{ route('marketing.download', ['url' => urlencode(is_object($post) && isset($post->image_path) ? media_url($post->image_path) : ''), 'filename' => 'marketing-' . ($post->id ?? 'image') . '.jpg']) }}" class="btn btn-camera">Download</a>
                                <form method="POST" action="{{ route('digital-marketing.destroy', $post->id) }}" style="display:inline;" onsubmit="return confirm('Archive this post?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-remove">Archive</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="empty-state">No posts uploaded yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Full Image Modal -->
            <div id="admin-dash-image-modal" class="camera-modal" onclick="adminDashCloseModal()">
                <div class="camera-modal-content" onclick="event.stopPropagation()">
                    <button onclick="adminDashCloseModal()" class="btn btn-close">Close</button>
                    <img id="admin-dash-modal-img" src="" alt="Full Size" class="modal-image">
                    <div class="modal-download-container">
                        <a id="admin-dash-download-btn" href="" download class="btn btn-camera">Download Image</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="sales-table-section" style="margin-bottom: 24px;">
            <div class="sales-table-header">
                <h3>Quick Actions</h3>
            </div>
            <div class="card-grid">
            
            <!-- Create Account Card -->
            <a href="{{ route('accounts.index') }}" class="card card-orange">
                <div class="card-icon-wrapper">
                </div>
                <h3>Account</h3>
                <p>Add new user accounts and manage permissions</p>
                <div class="card-arrow">View →</div>
            </a>

               <!-- Update Password Card -->
            <a href="{{ route('admin.password.update') }}" class="card card-orange">
                <div class="card-icon-wrapper">
                </div>
                <h3>Update Profile</h3>
                <p>Secure your account with a new password</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Manage Contract Card -->
            <a href="{{ route('admin.branches.index') }}" class="card card-red">
                <div class="card-icon-wrapper">
                </div>
                <h3>Contract</h3>
                <p>View and manage contracts</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Add Item Card -->
            <a href="{{ route('admin.items.index') }}" class="card card-yellow">
                <div class="card-icon-wrapper">
                </div>
                <h3>Item</h3>
                <p>View and manage items</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Manage Orders Card -->
            <a href="{{ route('admin.manageOrder.index') }}" class="card card-blue">
                <div class="card-icon-wrapper">
                </div>
                <h3>Order</h3>
                <p>Track and manage all customer orders</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Stock Management Card -->
            <a href="{{ route('admin.stock.index') }}" class="card card-purple">
                <div class="card-icon-wrapper">
                </div>
                <h3>Stock Management</h3>
                <p>Monitor inventory levels and stock transactions</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Reports Card -->
            <a href="{{ route('admin.reports.index') }}" class="card card-purple">
                <div class="card-icon-wrapper">
                </div>
                <h3>Report</h3>
                <p>View detailed analytics and business reports</p>
                <div class="card-arrow">View →</div>
            </a>

        
            </div>
        </section>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const dashboardChartCanvas = document.getElementById('dashboardSalesTrendChart');

    if (dashboardChartCanvas) {
        const labels = @json($salesTrendLabels ?? []);
        const salesData = @json($salesTrendValues ?? []);
        const orderData = @json($salesTrendOrderCounts ?? []);

        new Chart(dashboardChartCanvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Sales (₱)',
                        data: salesData,
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.14)',
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'ySales',
                        borderWidth: 3,
                    },
                    {
                        label: 'Delivered Orders',
                        data: orderData,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.08)',
                        fill: false,
                        tension: 0.3,
                        yAxisID: 'yOrders',
                        borderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                },
                scales: {
                    ySales: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                    },
                    yOrders: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            precision: 0,
                            stepSize: 1,
                        }
                    }
                }
            }
        });
    }
</script>

@endsection

@push('scripts')
<script>
    let dashCameraStream = null;

    function dashPreviewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('dash-preview-img').src = e.target.result;
                document.getElementById('dash-image-preview').classList.add('show');
                document.getElementById('dash-file-name').textContent = '✓ ' + file.name;
            };
            reader.readAsDataURL(file);
        }
    }

    async function dashOpenCamera() {
        const modal = document.getElementById('dash-camera-modal');
        const video = document.getElementById('dash-camera-stream');
        modal.classList.add('show');
        try {
            dashCameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            video.srcObject = dashCameraStream;
        } catch (error) {
            alert('Unable to access camera. Please check permissions or use "Choose from Gallery".\n\nError: ' + error.message);
            dashCloseCamera();
        }
    }

    function dashCapturePhoto() {
        const video = document.getElementById('dash-camera-stream');
        const canvas = document.getElementById('dash-camera-canvas');
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        canvas.toBlob(function(blob) {
            const file = new File([blob], 'camera-photo.jpg', { type: 'image/jpeg' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('dash_marketing_image').files = dataTransfer.files;
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('dash-preview-img').src = e.target.result;
                document.getElementById('dash-image-preview').classList.add('show');
                document.getElementById('dash-file-name').textContent = '✓ camera-photo.jpg';
            };
            reader.readAsDataURL(file);
            dashCloseCamera();
        }, 'image/jpeg', 0.95);
    }

    function dashCloseCamera() {
        const modal = document.getElementById('dash-camera-modal');
        const video = document.getElementById('dash-camera-stream');
        if (dashCameraStream) {
            dashCameraStream.getTracks().forEach(track => track.stop());
            dashCameraStream = null;
        }
        video.srcObject = null;
        modal.classList.remove('show');
    }

    function dashRemoveImage() {
        document.getElementById('dash_marketing_image').value = '';
        document.getElementById('dash-image-preview').classList.remove('show');
        document.getElementById('dash-file-name').textContent = '';
    }

    function adminDashViewImage(postId) {
        const img = document.getElementById('admin-dash-img-' + postId);
        const modal = document.getElementById('admin-dash-image-modal');
        const modalImg = document.getElementById('admin-dash-modal-img');
        const downloadBtn = document.getElementById('admin-dash-download-btn');
        modalImg.src = img.src;
        downloadBtn.href = img.src;
        downloadBtn.download = 'marketing-' + postId + '.jpg';
        modal.classList.add('show');
    }

    function adminDashCopyDescription(postId) {
        const descElem = document.getElementById('admin-dash-desc-' + postId);
        if (!descElem) return;
        const text = descElem.textContent || descElem.innerText;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Description copied to clipboard!');
            }, function() {
                alert('Failed to copy description.');
            });
        } else {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                alert('Description copied to clipboard!');
            } catch (err) {
                alert('Failed to copy description.');
            }
            document.body.removeChild(textarea);
        }
    }

    function adminDashCloseModal() {
        document.getElementById('admin-dash-image-modal').classList.remove('show');
    }
</script>
@endpush
