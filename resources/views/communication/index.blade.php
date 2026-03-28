@extends($layout ?? 'layouts.app')

@section('content')
<div class="communication-page">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg" style="color: var(--dsizzlers-orange);">
            <rect x="2" y="4" width="20" height="16" rx="2" ry="2"></rect>
            <path d="m22 7-10 5L2 7"></path>
        </svg>
        <h2 style="margin: 0;">Communication Management</h2>
    </div>

    <hr>

    <section class="conversation-section">
        <h3>Conversations</h3>

        <div class="new-conversation-container">
            <h4>Start a New Conversation</h4>

            <form method="POST" action="{{ route('communication.start') }}">
                @csrf

                @php
                    $isFranchisor = auth()->guard('admin')->check() || auth()->guard('franchisor_staff')->check();
                    $isFranchisee = auth()->guard('franchisee')->check() || auth()->guard('franchisee_staff')->check();
                @endphp

                @if($isFranchisor)
                    <label for="partner_id">Select Franchisee</label><br>
                    <select name="partner_id" id="partner_id" required>
                        <option value="" disabled selected>- Choose a Franchisee -</option>
                        @foreach(\App\Models\Franchisee::all() as $franchisee)
                            <option value="{{ $franchisee->franchisee_id }}">{{ $franchisee->franchisee_name }}</option>
                        @endforeach
                    </select>
                @elseif($isFranchisee)
                    <label for="partner_id">Select Franchisor</label><br>
                    <select name="partner_id" id="partner_id" required>
                        <option value="" disabled selected>- Choose a Franchisor -</option>
                        @foreach(\App\Models\Admin::all() as $admin)
                            <option value="{{ $admin->admin_id }}">{{ $admin->admin_fname }} {{ $admin->admin_lname }}</option>
                        @endforeach
                    </select>
                @else
                    <label for="partner_id">Select Franchisee</label><br>
                    <select name="partner_id" id="partner_id" required>
                        <option value="" disabled selected>- Choose a Franchisee -</option>
                        @foreach(\App\Models\Franchisee::all() as $franchisee)
                            <option value="{{ $franchisee->franchisee_id }}">{{ $franchisee->franchisee_name }}</option>
                        @endforeach
                    </select>
                @endif

                <br><br>
                <button type="submit" class="btn btn-primary">Create</button>
            </form>
        </div>

        <hr>

        <h4>Existing Conversations</h4>
        <div class="button-group" style="margin-bottom: 12px;">
            <a href="{{ route('communication.index', ['conversation_view' => 'active', 'announcement_view' => $announcementView ?? 'active']) }}" class="btn btn-gallery">Active Conversations</a>
            <a href="{{ route('communication.index', ['conversation_view' => 'archived', 'announcement_view' => $announcementView ?? 'active']) }}" class="btn btn-camera">Archived Conversations</a>
        </div>

        <ul class="conversations-list">
            @forelse($conversations as $conversation)
                @php
                    $admin = $conversation->admin;
                    $franchisee = $conversation->franchisee;
                    $isCurrentUserAdmin = auth()->guard('admin')->check() || auth()->guard('franchisor_staff')->check();

                    if ($isCurrentUserAdmin) {
                        $displayName = $franchisee ? ($franchisee->franchisee_name ?: 'Franchisee') : 'Franchisee';
                    } else {
                        $adminName = $admin ? trim(($admin->admin_fname ?? '') . ' ' . ($admin->admin_lname ?? '')) ?: 'System Administrator' : 'System Administrator';
                        $displayName = $adminName;
                    }
                @endphp
                <li>
                    <a href="{{ url('/communication/' . $conversation->id) }}">{{ $displayName }}</a>

                    @if(($conversationView ?? 'active') === 'archived')
                        <form method="POST" action="{{ route('communication.restore', $conversation->id) }}" style="margin-top: 8px;">
                            @csrf
                            <button type="submit" class="btn btn-camera" onclick="return confirm('Restore this conversation?');">Restore Conversation</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('communication.archive', $conversation->id) }}" style="margin-top: 8px;">
                            @csrf
                            <button type="submit" class="btn btn-remove" onclick="return confirm('Archive this conversation?');">Archive Conversation</button>
                        </form>
                    @endif
                </li>
            @empty
                <p class="empty-state">No conversations available.</p>
            @endforelse
        </ul>
    </section>

    <hr>

    @if(auth()->guard('admin')->check())
        <section class="marketing-section">
            <h3>Digital Marketing Management</h3>

            <div class="button-group" style="margin-bottom: 12px;">
                <a href="{{ route('communication.index', ['conversation_view' => $conversationView ?? 'active', 'announcement_view' => 'active']) }}" class="btn btn-gallery">Active Announcements</a>
                <a href="{{ route('communication.index', ['conversation_view' => $conversationView ?? 'active', 'announcement_view' => 'archived']) }}" class="btn btn-camera">Archived Announcements</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="marketing-upload-container">
                <h4>Upload Digital Marketing</h4>

                <form method="POST" action="{{ route('digital-marketing.store') }}" enctype="multipart/form-data" id="digitalMarketingForm">
                    @csrf

                    <div class="form-group">
                        <label for="marketing_image" class="form-label">Select Image:</label>

                        <input
                            type="file"
                            name="image"
                            id="marketing_image"
                            accept="image/*"
                            required
                            class="file-input-hidden"
                            onchange="previewMarketingImage(event)"
                        >

                        <div class="button-group">
                            <button type="button" onclick="document.getElementById('marketing_image').click()" class="btn btn-gallery">Choose from Gallery</button>
                            <button type="button" onclick="openCameraModal()" class="btn btn-camera">Take Photo</button>
                        </div>

                        <span id="file-name" class="file-name-display"></span>

                        <div id="image-preview" class="image-preview-container">
                            <img id="preview-img" src="" alt="Preview" class="preview-image">
                            <button type="button" onclick="removeImage()" class="btn btn-remove">Remove Image</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description (Optional):</label>
                        <textarea name="description" id="description" rows="3" placeholder="Enter a description for this marketing post..." class="form-textarea"></textarea>
                    </div>

                    <button type="submit" class="btn btn-submit">Upload Post</button>
                </form>
            </div>

            <div id="camera-modal" class="camera-modal">
                <div class="camera-modal-content">
                    <h3>Take Photo</h3>
                    <video id="camera-stream" autoplay playsinline class="camera-stream"></video>
                    <canvas id="camera-canvas" class="camera-canvas"></canvas>
                    <div class="modal-button-group">
                        <button type="button" onclick="capturePhoto()" class="btn btn-camera">Capture</button>
                        <button type="button" onclick="closeCameraModal()" class="btn btn-close">Close</button>
                    </div>
                </div>
            </div>

            <hr>

            <h4>Uploaded Marketing Materials</h4>

            <div class="marketing-posts-container">
                @forelse($digitalMarketing as $post)
                    <div class="marketing-post">
                        <img
                            src="{{ media_url($post->image_path) }}"
                            alt="Marketing Image"
                            id="admin-marketing-img-{{ $post->id }}"
                            class="marketing-post-image"
                            onclick="viewFullImage({{ $post->id }}, 'admin')"
                            title="Click to view full size"
                        >

                        @if(($announcementView ?? 'active') !== 'archived' && auth()->guard('admin')->check())
                            <form id="edit-form-{{ $post->id }}" method="POST" action="{{ route('digital-marketing.update', $post->id) }}" class="edit-form-hidden">
                                @csrf
                                @method('PUT')
                                <textarea name="description" rows="3" class="form-textarea">{{ $post->description }}</textarea>
                                <div class="button-group">
                                    <button type="submit" class="btn btn-camera">Save</button>
                                    <button type="button" onclick="cancelEdit({{ $post->id }})" class="btn btn-submit">Cancel</button>
                                </div>
                            </form>
                        @endif

                        <div id="description-display-{{ $post->id }}">
                            @if($post->description)
                                <p class="marketing-post-description">{{ $post->description }}</p>
                            @endif
                            <small class="marketing-post-date">Posted on {{ $post->created_at->format('M d, Y h:i A') }}</small>
                        </div>

                        <div class="button-group">
                            <button onclick="viewFullImage({{ $post->id }}, 'admin')" class="btn btn-gallery">View</button>

                            @if(auth()->guard('admin')->check())
                                @if(($announcementView ?? 'active') === 'archived')
                                    <form method="POST" action="{{ route('digital-marketing.restore', $post->id) }}" class="form-inline" onsubmit="return confirm('Restore this post?');">
                                        @csrf
                                        <button type="submit" class="btn btn-camera">Restore</button>
                                    </form>
                                @else
                                    <button onclick="editPost({{ $post->id }})" class="btn btn-edit">Edit</button>
                                    <form method="POST" action="{{ route('digital-marketing.destroy', $post->id) }}" class="form-inline" onsubmit="return confirm('Archive this post?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-remove">Archive</button>
                                    </form>
                                @endif
                            @endif

                            <a href="{{ media_url($post->image_path) }}" download="marketing-{{ $post->id }}.jpg" class="btn btn-camera">Download</a>
                        </div>
                    </div>
                @empty
                    <p class="empty-state">No digital marketing posts uploaded yet.</p>
                @endforelse
            </div>
        </section>
    @endif

    @if(auth()->guard('franchisee')->check())
        <section class="marketing-section">
            <h3>Digital Marketing Posts</h3>

            <div class="button-group" style="margin-bottom: 12px;">
                <a href="{{ route('communication.index', ['conversation_view' => $conversationView ?? 'active', 'announcement_view' => 'active']) }}" class="btn btn-gallery">Active Announcements</a>
            </div>

            <div class="marketing-posts-container">
                @forelse($digitalMarketing as $post)
                    <div class="marketing-post">
                        <img
                            src="{{ media_url($post->image_path) }}"
                            alt="Marketing Image"
                            id="franchisee-marketing-img-{{ $post->id }}"
                            class="marketing-post-image"
                            onclick="viewFullImage({{ $post->id }}, 'franchisee')"
                            title="Click to view full size"
                        >
                        @if($post->description)
                            <p class="marketing-post-description">{{ $post->description }}</p>
                        @endif
                        <small class="marketing-post-date">Posted on {{ $post->created_at->format('M d, Y h:i A') }}</small>

                        <div class="button-group">
                            <button onclick="viewFullImage({{ $post->id }}, 'franchisee')" class="btn btn-gallery">View</button>
                            <a href="{{ media_url($post->image_path) }}" download="marketing-{{ $post->id }}.jpg" class="btn btn-camera">Download</a>
                        </div>
                    </div>
                @empty
                    <p class="empty-state">No digital marketing posts available.</p>
                @endforelse
            </div>
        </section>
    @endif

    <div id="image-modal" class="camera-modal" onclick="closeImageModal()">
        <div class="camera-modal-content" onclick="event.stopPropagation()">
            <button onclick="closeImageModal()" class="btn btn-close">Close</button>
            <img id="modal-image" src="" alt="Full Size" class="modal-image">
            <div class="modal-download-container">
                <a id="modal-download-btn" href="" download class="btn btn-camera">Download Image</a>
            </div>
        </div>
    </div>
</div>

<script>
    let cameraStream = null;

    function previewMarketingImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('image-preview').classList.add('show');
                document.getElementById('file-name').textContent = file.name;
            };
            reader.readAsDataURL(file);
        }
    }

    async function openCameraModal() {
        const modal = document.getElementById('camera-modal');
        const video = document.getElementById('camera-stream');

        modal.classList.add('show');

        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' }
            });
            video.srcObject = cameraStream;
        } catch (error) {
            alert('Unable to access camera. Please check permissions or use "Choose from Gallery" instead.\n\nError: ' + error.message);
            closeCameraModal();
        }
    }

    function capturePhoto() {
        const video = document.getElementById('camera-stream');
        const canvas = document.getElementById('camera-canvas');
        const context = canvas.getContext('2d');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob(function(blob) {
            const file = new File([blob], 'camera-photo.jpg', { type: 'image/jpeg' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('marketing_image').files = dataTransfer.files;

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('image-preview').classList.add('show');
                document.getElementById('file-name').textContent = 'camera-photo.jpg';
            };
            reader.readAsDataURL(file);
            closeCameraModal();
        }, 'image/jpeg', 0.95);
    }

    function closeCameraModal() {
        const modal = document.getElementById('camera-modal');
        const video = document.getElementById('camera-stream');

        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }

        video.srcObject = null;
        modal.classList.remove('show');
    }

    function removeImage() {
        document.getElementById('marketing_image').value = '';
        document.getElementById('image-preview').classList.remove('show');
        document.getElementById('file-name').textContent = '';
    }

    function editPost(postId) {
        document.getElementById('description-display-' + postId).style.display = 'none';
        document.getElementById('edit-form-' + postId).style.display = 'block';
    }

    function cancelEdit(postId) {
        document.getElementById('edit-form-' + postId).style.display = 'none';
        document.getElementById('description-display-' + postId).style.display = 'block';
    }

    function viewFullImage(postId, type) {
        const img = document.getElementById(type + '-marketing-img-' + postId);
        const modal = document.getElementById('image-modal');
        const modalImg = document.getElementById('modal-image');
        const downloadBtn = document.getElementById('modal-download-btn');

        modal.classList.add('show');
        modalImg.src = img.src;
        downloadBtn.href = img.src;
        downloadBtn.download = 'marketing-' + postId + '.jpg';
    }

    function closeImageModal() {
        document.getElementById('image-modal').classList.remove('show');
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeImageModal();
            closeCameraModal();
        }
    });
</script>
@endsection
