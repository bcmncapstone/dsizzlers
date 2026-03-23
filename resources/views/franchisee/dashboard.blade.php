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
                    <strong>✓</strong>
                    {{ session('success') }}
                </div>
            @endif
        </div>

        <!-- Digital Marketing Posts -->
        <section class="marketing-section" style="margin-top: 24px;">
            <h3>📢 Digital Marketing Posts</h3>
            <div class="marketing-posts-container">
                @forelse($digitalMarketing ?? [] as $post)
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
                            <p class="marketing-post-description">{{ $post->description }}</p>
                        @endif
                        <small class="marketing-post-date">Posted on {{ $post->created_at->format('M d, Y h:i A') }}</small>
                        <div class="button-group">
                            <button onclick="dashFranchiseeViewImage({{ $post->id }})" class="btn btn-gallery">👁️ View</button>
                            <a href="{{ media_url($post->image_path) }}" download="marketing-{{ $post->id }}.jpg" class="btn btn-camera">⬇️ Download</a>
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
                <button onclick="dashFranchiseeCloseModal()" class="btn btn-close">✕</button>
                <img id="dash-franchisee-modal-img" src="" alt="Full Size" class="modal-image">
                <div class="modal-download-container">
                    <a id="dash-franchisee-download-btn" href="" download class="btn btn-camera">⬇️ Download Image</a>
                </div>
            </div>
        </div>

        <!-- Dashboard Cards Grid -->
        <div class="card-grid">
            
            <!-- Update Password Card -->
            <a href="{{ route('franchisee.password.update') }}" class="card card-orange">
                <div class="card-icon-wrapper">
                    <div class="card-icon">🔑</div>
                </div>
                <h3>Update Profile</h3>
                <p>Secure your account with a new password and username</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Manage Branch Card -->
            <a href="{{ route('franchisee.branch.dashboard') }}" class="card card-red">
                <div class="card-icon-wrapper">
                    <div class="card-icon">🏢</div>
                </div>
                <h3>Manage Branch</h3>
                <p>View and manage your branch information</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Items Card -->
            <a href="{{ route('franchisee.item.index') }}" class="card card-yellow">
                <div class="card-icon-wrapper">
                    <div class="card-icon">🍽️</div>
                </div>
                <h3>Items</h3>
                <p>Manage menu items and products</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Cart Card -->
            <a href="{{ route('franchisee.cart.index') }}" class="card card-blue">
                <div class="card-icon-wrapper">
                    <div class="card-icon">🛒</div>
                </div>
                <h3>Cart</h3>
                <p>Check cart</p>
                <div class="card-arrow">View →</div>
            </a>

               <!-- Orders Card -->
            <a href="{{ route('franchisee.orders.index') }}" class="card card-green">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📦</div>
                </div>
                <h3>Orders</h3>
                <p>Check orders</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Add Staff Card -->
            <a href="{{ route('account.create') }}" class="card card-red">
                <div class="card-icon-wrapper">
                    <div class="card-icon">➕</div>
                </div>
                <h3>Add Staff</h3>
                <p>Create new staff accounts and manage permissions</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- User Accounts Card -->
            <a href="{{ route('franchisee.account.index') }}" class="card card-purple">
                <div class="card-icon-wrapper">
                    <div class="card-icon">👥</div>
                </div>
                <h3>User Accounts</h3>
                <p>Manage all staff and user accounts</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Reports Card -->
            <a href="{{ route('franchisee.reports.index') }}" class="card card-blue">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📈</div>
                </div>
                <h3>Reports</h3>
                <p>View detailed analytics and business reports</p>
                <div class="card-arrow">View →</div>
            </a>

            <!-- Messages Card -->
            <a href="{{ route('communication.index') }}" class="card card-green">
                <div class="card-icon-wrapper">
                    <div class="card-icon">💬</div>
                </div>
                <h3>Messages</h3>
                <p>Communicate with franchisor and team members</p>
                <div class="card-arrow">View →</div>
            </a>
        </div>

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

    function dashFranchiseeCloseModal() {
        document.getElementById('dash-franchisee-image-modal').classList.remove('show');
    }
</script>
@endpush
