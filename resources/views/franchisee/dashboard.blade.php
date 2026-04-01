@extends('layouts.franchisee') 

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        
        <!-- Welcome Header -->
        <div class="dashboard-header">
            <h1>Welcome, Franchisee!</h1>
            <p>Manage your branch operations efficiently</p>
            
            @if(session('success'))
                <div class="alert alert-success">
                    <strong>Success.</strong>
                    {{ session('success') }}
                </div>
            @endif
        </div>

        <!-- Summary Stats (Admin-style) -->
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
                    <div class="sales-stat-label">Pending Orders</div>
                    <div class="sales-stat-value">{{ intval($pendingOrders ?? 0) }}</div>
                </div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Staff Members</div>
                    <div class="sales-stat-value">{{ intval($staffCount ?? 0) }}</div>
                </div>
            </div>

            <div class="sales-stat-card">
                <div class="sales-stat-content">
                    <div class="sales-stat-label">Low + Out of Stock</div>
                    <div class="sales-stat-value">{{ intval($lowStockCount ?? 0) + intval($outOfStockCount ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Dashboard Data Panels (Admin-style) -->
        <div class="sales-charts-grid">
            <div class="sales-chart-section">
                <h3 class="sales-chart-title">Order Status</h3>
                <div class="sales-table-overflow">
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>Status</th>
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
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="sales-chart-section">
                <h3 class="sales-chart-title">Top Selling Items</h3>
                <div class="sales-table-overflow">
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Units Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topItems ?? collect() as $item)
                                <tr>
                                    <td>{{ $item->item_name }}</td>
                                    <td>{{ intval($item->total_quantity) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="sales-table-empty">No sales data yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Digital Marketing Posts -->
        <section class="marketing-section" style="margin-top: 24px;">
            <h3>Digital Marketing Post</h3>
            <div class="marketing-posts-container">
                @php $dashboardDigitalMarketing = $digitalMarketing ?? collect(); @endphp
                @forelse($dashboardDigitalMarketing as $post)
                    <div class="marketing-post">
                        <img
                            src="{{ media_url($post->image_path) }}"
                            alt="Marketing Image"
                            id="dash-franchisee-marketing-img-{{ $post->id }}"
                            class="marketing-post-image"
                            onclick="dashFranchiseeViewImage({{ $post->id }})"
                            title="Click to view full size"
                        >
                        @if($post->description)
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <p class="marketing-post-description" id="dash-franchisee-desc-{{ $post->id }}" style="margin-bottom: 0;">{{ $post->description }}</p>
                                <button type="button" class="btn btn-edit" onclick="dashFranchiseeCopyDescription({{ $post->id }})" title="Copy Description">Copy</button>
                            </div>
                        @endif
                        <small class="marketing-post-date">Posted on {{ $post->created_at->format('M d, Y h:i A') }}</small>
                        <div class="button-group">
                            <button onclick="dashFranchiseeViewImage({{ $post->id }})" class="btn btn-gallery">View</button>
                            <a href="{{ route('marketing.download', ['url' => urlencode(media_url($post->image_path)), 'filename' => 'marketing-' . $post->id . '.jpg']) }}" class="btn btn-camera">Download</a>
                        </div>
                    </div>
                @empty
                    <p class="empty-state">No digital marketing posts available yet.</p>
                @endforelse
            </div>
        </section>

        <!-- Full Image Modal -->
        <div id="dash-franchisee-image-modal" class="camera-modal" onclick="dashFranchiseeCloseModal()">
            <div class="camera-modal-content" onclick="event.stopPropagation()">
                <button onclick="dashFranchiseeCloseModal()" class="btn btn-close">Close</button>
                <img id="dash-franchisee-modal-img" src="" alt="Full Size" class="modal-image">
                <div class="modal-download-container">
                    <a id="dash-franchisee-download-btn" href="" download class="btn btn-camera">Download Image</a>
                </div>
            </div>
        </div>

        <!-- Dashboard Cards Grid -->
        <div class="card-grid">
            
            <!-- Update Password Card -->
            <a href="{{ route('franchisee.password.update') }}" class="card card-orange">
                <div class="card-icon-wrapper">
                </div>
                <h3>Update Profile</h3>
                <p>Secure your account with a new password and username</p>
                <div class="card-arrow">View →</div>
            </a>

              <!-- Add Staff Card -->
            <a href="{{ route('franchisee.staff.index') }}" class="card card-red">
                <div class="card-icon-wrapper">
                </div>
                <h3>Add Staff</h3>
                <p>Create new staff accounts and manage permissions</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- User Accounts Card -->
            <a href="{{ route('franchisee.account.index') }}" class="card card-purple">
                <div class="card-icon-wrapper">
                </div>
                <h3>View Contract</h3>
                <p>Manage Contract</p>
                <div class="card-arrow">View →</div>
            </a>

               <!-- Manage Branch Card -->
            <a href="{{ route('franchisee.branch.dashboard') }}" class="card card-red">
                <div class="card-icon-wrapper">
                </div>
                <h3>Manage Branch</h3>
                <p>View and manage your branch information</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Items Card -->
            <a href="{{ route('franchisee.item.index') }}" class="card card-yellow">
                <div class="card-icon-wrapper">
                </div>
                <h3>Item</h3>
                <p>Manage menu items and products</p>
                <div class="card-arrow">View →</div>
            </a>

                   <!-- Orders Card -->
            <a href="{{ route('franchisee.orders.index') }}" class="card card-green">
                <div class="card-icon-wrapper">
                </div>
                <h3>Order</h3>
                <p>Check orders</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Cart Card -->
            <a href="{{ route('franchisee.cart.index') }}" class="card card-blue">
                <div class="card-icon-wrapper">
                </div>
                <h3>Cart</h3>
                <p>Check cart</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Stock Card -->
            <a href="{{ route('franchisee.stock.index') }}" class="card card-teal">
                <div class="card-icon-wrapper">
                </div>
                <h3>Stock</h3>
                <p>Manage inventory and stock levels</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Reports Card -->
            <a href="{{ route('franchisee.reports.index') }}" class="card card-blue">
                <div class="card-icon-wrapper">
                </div>
                <h3>Report</h3>
                <p>View detailed analytics and business reports</p>
                <div class="card-arrow">View →</div>
            </a>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function dashFranchiseeViewImage(postId) {
        const img = document.getElementById('dash-franchisee-marketing-img-' + postId);
        const modal = document.getElementById('dash-franchisee-image-modal');
        const modalImg = document.getElementById('dash-franchisee-modal-img');
        const downloadBtn = document.getElementById('dash-franchisee-download-btn');
        modalImg.src = img.src;
        downloadBtn.href = img.src;
        downloadBtn.download = 'marketing-' + postId + '.jpg';
        modal.classList.add('show');
    }

    function dashFranchiseeCopyDescription(postId) {
        const descElem = document.getElementById('dash-franchisee-desc-' + postId);
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

    function dashFranchiseeCloseModal() {
        document.getElementById('dash-franchisee-image-modal').classList.remove('show');
    }
</script>
@endpush
