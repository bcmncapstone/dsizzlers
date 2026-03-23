{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        
        <!-- Welcome Header -->
        <div class="dashboard-header">
            <h1>Welcome, Admin!</h1>

            @if(session('success'))
                <div class="alert alert-success">
                    <strong>✓</strong>
                    {{ session('success') }}
                </div>
            @endif
        </div>

        <!-- Report Summary Stats -->
        <div class="sales-stats-grid">
            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Total Items Sold</div>
                    <div class="sales-stat-value">{{ intval($totalItemsSold ?? 0) }}</div>
                </div>
                <div class="sales-stat-icon">📦</div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Low/Out Stock</div>
                    <div class="sales-stat-value">{{ ($lowStockCount ?? 0) + ($outOfStockCount ?? 0) }}</div>
                </div>
                <div class="sales-stat-icon">⚠️</div>
            </div>
        </div>

        <!-- Revenue & KPI Snapshot -->
        <div class="sales-stats-grid" style="margin-top: 16px;">
            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Revenue Today</div>
                    <div class="sales-stat-value">₱{{ number_format($todayRevenue ?? 0, 2) }}</div>
                </div>
                <div class="sales-stat-icon">Today</div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Revenue This Week</div>
                    <div class="sales-stat-value">₱{{ number_format($weekRevenue ?? 0, 2) }}</div>
                </div>
                <div class="sales-stat-icon">Week</div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Revenue This Month</div>
                    <div class="sales-stat-value">₱{{ number_format($monthRevenue ?? 0, 2) }}</div>
                </div>
                <div class="sales-stat-icon">Month</div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Average Order Value</div>
                    <div class="sales-stat-value">₱{{ number_format($averageOrderValue ?? 0, 2) }}</div>
                </div>
                <div class="sales-stat-icon">AOV</div>
            </div>
        </div>

        <!-- Order Status Overview -->
        <section class="marketing-section" style="margin-top: 24px;">
            <h3>Order Status Overview</h3>
            <div class="sales-stats-grid" style="margin-top: 12px;">
                @foreach(($ordersByStatus ?? []) as $status => $count)
                    <a href="{{ route('admin.manageOrder.index', ['status' => $status]) }}" class="sales-stat-link" title="View {{ $status }} orders">
                        <div class="sales-stat-card">
                            <div class="sales-stat-content">
                                <div class="sales-stat-label">{{ $status }}</div>
                                <div class="sales-stat-value">{{ intval($count) }}</div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <!-- Product Performance -->
        <section class="marketing-section" style="margin-top: 24px;">
            <h3>Product Performance</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 product-performance-grid" style="margin-top: 12px;">
                <div class="bg-white shadow-sm p-6 rounded-lg overflow-x-auto product-performance-card">
                    <h4 style="margin-bottom: 12px;">Top 5 Best-Selling Items</h4>
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Units Sold</th>
                                <th>Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($topSellingItems ?? []) as $item)
                                <tr>
                                    <td>{{ $item->item_name }}</td>
                                    <td>{{ intval($item->total_quantity) }}</td>
                                    <td>₱{{ number_format($item->total_sales, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No sales data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="bg-white shadow-sm p-6 rounded-lg overflow-x-auto product-performance-card">
                    <h4 style="margin-bottom: 12px;">Slow-Moving Items (Last 30 Days)</h4>
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>30D Sold</th>
                                <th>In Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($slowMovingItems ?? []) as $item)
                                <tr>
                                    <td>{{ $item->item_name }}</td>
                                    <td>{{ intval($item->sold_30d) }}</td>
                                    <td>{{ intval($item->stock_quantity) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No item movement data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Stock Risk Forecast -->
        <section class="marketing-section" style="margin-top: 24px;">
            <h3>Stock Risk Forecast</h3>
            <div class="bg-white shadow-sm p-6 rounded-lg overflow-x-auto" style="margin-top: 12px;">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Current Stock</th>
                            <th>Avg Daily Sales</th>
                            <th>Estimated Days Left</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($stockRiskForecast ?? []) as $item)
                            <tr>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ intval($item->stock_quantity) }}</td>
                                <td>{{ number_format($item->avg_daily_sales, 2) }}</td>
                                <td>{{ number_format($item->days_left, 1) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No forecast data available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Branch Performance -->
        <section class="marketing-section" style="margin-top: 24px;">
            <h3>Branch Performance Ranking</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" style="margin-top: 12px;">
                <div class="bg-white shadow-sm p-6 rounded-lg overflow-x-auto">
                    <h4 style="margin-bottom: 12px;">Top Branches</h4>
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th>Franchisee</th>
                                <th>Orders</th>
                                <th>Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($topBranches ?? []) as $branch)
                                <tr>
                                    <td>{{ $branch->franchisee_name }}</td>
                                    <td>{{ intval($branch->orders_count) }}</td>
                                    <td>₱{{ number_format($branch->total_sales, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No branch sales data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="bg-white shadow-sm p-6 rounded-lg overflow-x-auto">
                    <h4 style="margin-bottom: 12px;">Branches Needing Attention</h4>
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th>Franchisee</th>
                                <th>Orders</th>
                                <th>Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($bottomBranches ?? []) as $branch)
                                <tr>
                                    <td>{{ $branch->franchisee_name }}</td>
                                    <td>{{ intval($branch->orders_count) }}</td>
                                    <td>₱{{ number_format($branch->total_sales, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No branch sales data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- 7-Day Trend Snapshot -->
        <section class="marketing-section" style="margin-top: 24px;">
            <h3>7-Day Trend Snapshot</h3>
            <div class="bg-white shadow-sm p-6 rounded-lg" style="margin-top: 12px;">
                @if(!empty($trendData) && count($trendData) > 0)
                    <div class="sales-chart-container" style="height: 320px;">
                        <canvas id="trendLineChart"></canvas>
                    </div>
                @else
                    <p class="text-center" style="margin: 0;">No trend data available.</p>
                @endif
            </div>
        </section>

        <!-- Digital Marketing Upload -->
        <section class="marketing-section" style="margin-top: 28px;">
            <h3>📢 Digital Marketing Management</h3>

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
                <h4>📤 Upload Digital Marketing</h4>

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
                                📁 Choose from Gallery
                            </button>
                            <button type="button" onclick="dashOpenCamera()" class="btn btn-camera">
                                📷 Take Photo
                            </button>
                        </div>

                        <span id="dash-file-name" class="file-name-display"></span>

                        <div id="dash-image-preview" class="image-preview-container">
                            <img id="dash-preview-img" src="" alt="Preview" class="preview-image">
                            <button type="button" onclick="dashRemoveImage()" class="btn btn-remove">✕ Remove Image</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="dash_description" class="form-label">Description (Optional):</label>
                        <textarea name="description" id="dash_description" rows="3" placeholder="Enter a description for this marketing post..." class="form-textarea"></textarea>
                    </div>

                    <button type="submit" class="btn btn-submit">🚀 Upload Post</button>
                </form>
            </div>

            <!-- Camera Modal -->
            <div id="dash-camera-modal" class="camera-modal">
                <div class="camera-modal-content">
                    <h3>📷 Take Photo</h3>
                    <video id="dash-camera-stream" autoplay playsinline class="camera-stream"></video>
                    <canvas id="dash-camera-canvas" class="camera-canvas"></canvas>
                    <div class="modal-button-group">
                        <button type="button" onclick="dashCapturePhoto()" class="btn btn-camera">📸 Capture</button>
                        <button type="button" onclick="dashCloseCamera()" class="btn btn-remove">✕ Close</button>
                    </div>
                </div>
            </div>

            <!-- Existing Posts -->
            <div style="margin-top: 28px;">
                <h4>📋 Uploaded Posts</h4>
                <div class="marketing-posts-container">
                    @forelse($digitalMarketing ?? [] as $post)
                        <div class="marketing-post">
                            <img
                                src="{{ media_url($post->image_path) }}"
                                alt="Marketing Image"
                                id="admin-dash-img-{{ $post->id }}"
                                class="marketing-post-image"
                                onclick="adminDashViewImage({{ $post->id }})"
                                title="Click to view full size"
                                style="cursor:pointer;"
                            >
                            @if($post->description)
                                <p class="marketing-post-description">{{ $post->description }}</p>
                            @endif
                            <small class="marketing-post-date">Posted on {{ $post->created_at->format('M d, Y h:i A') }}</small>
                            <div class="button-group" style="margin-top:8px;">
                                <button onclick="adminDashViewImage({{ $post->id }})" class="btn btn-gallery">👁️ View</button>
                                <a href="{{ media_url($post->image_path) }}" download="marketing-{{ $post->id }}.jpg" class="btn btn-camera">⬇️ Download</a>
                                <form method="POST" action="{{ route('digital-marketing.destroy', $post->id) }}" style="display:inline;" onsubmit="return confirm('Delete this post?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-remove">🗑️ Delete</button>
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
                    <button onclick="adminDashCloseModal()" class="btn btn-close">✕</button>
                    <img id="admin-dash-modal-img" src="" alt="Full Size" class="modal-image">
                    <div class="modal-download-container">
                        <a id="admin-dash-download-btn" href="" download class="btn btn-camera">⬇️ Download Image</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Dashboard Cards Grid -->
        <div class="card-grid">
            
            <!-- Create Account Card -->
            <a href="{{ route('accounts.create') }}" class="card card-orange">
                <div class="card-icon-wrapper">
                    <div class="card-icon"></div>
                </div>
                <h3>Account</h3>
                <p>Add new user accounts and manage permissions</p>
                <div class="card-arrow">View →</div>
            </a>

             <!-- Update Password Card -->
            <a href="{{ route('admin.password.update') }}" class="card card-orange">
                <div class="card-icon-wrapper">
                    <div class="card-icon">🔑</div>
                </div>
                <h3>Update Password</h3>
                <p>Secure your account with a new password</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Manage Branches Card -->
            <a href="{{ route('admin.branches.index') }}" class="card card-red">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📝</div>
                </div>
                <h3>Contract</h3>
                <p>View and manage contracts</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Add Item Card -->
            <a href="{{ route('admin.items.create') }}" class="card card-yellow">
                <div class="card-icon-wrapper">
                    <div class="card-icon">🍽️</div>
                </div>
                <h3>Add Item</h3>
                <p>Create new menu items and products</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Manage Orders Card -->
            <a href="{{ route('admin.manageOrder.index') }}" class="card card-blue">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📦</div>
                </div>
                <h3>Order</h3>
                <p>Track and manage all customer orders</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Stock Management Card -->
            <a href="{{ route('admin.stock.index') }}" class="card card-purple">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📊</div>
                </div>
                <h3>Stock Management</h3>
                <p>Monitor inventory levels and stock transactions</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Reports Card -->
            <a href="{{ route('admin.reports.index') }}" class="card card-purple">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📈</div>
                </div>
                <h3>Report</h3>
                <p>View detailed analytics and business reports</p>
                <div class="card-arrow">View →</div>
            </a>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let dashCameraStream = null;

    const trendData = @json($trendData ?? []);

    document.addEventListener('DOMContentLoaded', function () {
        const trendCanvas = document.getElementById('trendLineChart');

        if (trendCanvas && Array.isArray(trendData) && trendData.length > 0 && typeof Chart !== 'undefined') {
            const labels = trendData.map(item => item.label);
            const revenue = trendData.map(item => Number(item.revenue || 0));
            const deliveredOrders = trendData.map(item => Number(item.orders_count || 0));
            const stockouts = trendData.map(item => Number(item.stockout_items || 0));

            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Revenue',
                            data: revenue,
                            yAxisID: 'y',
                            borderColor: '#FF5722',
                            backgroundColor: 'rgba(255, 87, 34, 0.12)',
                            tension: 0.35,
                            fill: true,
                            pointRadius: 3
                        },
                        {
                            label: 'Delivered Orders',
                            data: deliveredOrders,
                            yAxisID: 'y1',
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.12)',
                            tension: 0.35,
                            fill: false,
                            pointRadius: 3
                        },
                        {
                            label: 'Stockout Items',
                            data: stockouts,
                            yAxisID: 'y1',
                            borderColor: '#dc2626',
                            backgroundColor: 'rgba(220, 38, 38, 0.12)',
                            tension: 0.35,
                            fill: false,
                            pointRadius: 3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            position: 'left',
                            ticks: {
                                callback: function (value) {
                                    return '₱' + Number(value).toLocaleString();
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    }
                }
            });
        }
    });

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

    function adminDashCloseModal() {
        document.getElementById('admin-dash-image-modal').classList.remove('show');
    }
</script>
@endpush
