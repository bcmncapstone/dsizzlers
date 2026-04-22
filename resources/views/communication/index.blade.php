@extends($layout ?? 'layouts.app')

@section('content')
<div class="communication-page">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg" style="color: var(--dsizzlers-orange);">
            <rect x="2" y="4" width="20" height="16" rx="2" ry="2"></rect>
            <path d="m22 7-10 5L2 7"></path>
        </svg>
        <h2 style="margin: 0;">Communication</h2>
    </div>

    <hr>

    <div class="communication-grid">

    <section class="conversation-section panel">
        <div class="conversation-content-shell">
        <h3>Conversations</h3>

        <div class="new-conversation-layout">
            <div class="new-conversation-container">
                <div class="form-wrapper-limited">
                    <h4>Start a New Conversation</h4>

                    <form method="POST" action="{{ route('communication.start') }}">
                        @csrf
                        <div class="form-group-compact">
                            <label for="partner_id">Select Franchisee</label>
                            <select name="partner_id" id="partner_id" required>
                                <option value="" disabled selected>- Choose a Franchisee -</option>
                                @foreach(\App\Models\Franchisee::all() as $franchisee)
                                    <option value="{{ $franchisee->franchisee_id }}">{{ $franchisee->franchisee_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full-mobile">Create Conversation</button>
                    </form>
                </div>
            </div>
        </div>

        <hr>

        <h4>Existing Conversations</h4>
        <div class="button-group" style="margin-bottom: 12px;">
            <a href="{{ route('communication.index', ['conversation_view' => 'active', 'announcement_view' => $announcementView ?? 'active']) }}" class="btn btn-gallery conversation-toggle" data-view="active" id="conversations-active-link">Active Conversations</a>
            <a href="{{ route('communication.index', ['conversation_view' => 'archived', 'announcement_view' => $announcementView ?? 'active']) }}" class="btn btn-camera conversation-toggle" data-view="archived" id="conversations-archived-link">Archived Conversations</a>
        </div>

<div id="conversations-container" class="conversation-container">
    <ul class="conversations-list-refined">
        @forelse($conversations as $conversation)
            @php
                $isCurrentUserAdmin = auth()->guard('admin')->check() || auth()->guard('franchisor_staff')->check();
                if ($isCurrentUserAdmin) {
                    $displayName = $conversation->franchisee ? ($conversation->franchisee->franchisee_name ?: 'Franchisee') : 'Franchisee';
                } else {
                    $admin = $conversation->admin;
                    $displayName = $admin ? trim(($admin->admin_fname ?? '') . ' ' . ($admin->admin_lname ?? '')) ?: 'System Administrator' : 'System Administrator';
                }
                $initial = strtoupper(substr($displayName, 0, 1));
            @endphp
            
            <li class="conversation-row">
                <div class="user-info">
                    <div class="user-initial">{{ $initial }}</div>
                    <div class="user-text">
                        <a href="{{ url('/communication/' . $conversation->id) }}" class="user-name-link">
                            {{ $displayName }}
                        </a>
                        <span class="user-meta">Last message recently</span>
                    </div>
                </div>

                <div class="action-buttons">
                    @if(($conversationView ?? 'active') === 'archived')
                        <form method="POST" action="{{ route('communication.restore', $conversation->id) }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn-restore-subtle" onclick="return confirm('Restore?');">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                Restore
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('communication.archive', $conversation->id) }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn-archive-subtle" onclick="return confirm('Archive?');">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                                Archive
                            </button>
                        </form>
                    @endif
                </div>
            </li>
        @empty
            <p class="empty-state">No conversations available.</p>
        @endforelse
    </ul>
</div>
        </div>
    </section>

    <hr style="display:none;">

    @if(auth()->guard('admin')->check())
        <section class="marketing-section panel">
            <h3>Digital Marketing Management</h3>

            <div class="button-group" style="margin-bottom: 12px;">
                <a href="{{ route('communication.index', ['conversation_view' => $conversationView ?? 'active', 'announcement_view' => 'active']) }}" class="btn btn-gallery announcement-toggle" data-view="active" id="announcements-active-link">Active Announcements</a>
                <a href="{{ route('communication.index', ['conversation_view' => $conversationView ?? 'active', 'announcement_view' => 'archived']) }}" class="btn btn-camera announcement-toggle" data-view="archived" id="announcements-archived-link">Archived Announcements</a>
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
                    <button type="button" onclick="closeCameraModal()" class="image-modal-close" aria-label="Close camera modal">&times;</button>
                    <h3>Take Photo</h3>
                    <video id="camera-stream" autoplay playsinline class="camera-stream"></video>
                    <canvas id="camera-canvas" class="camera-canvas"></canvas>
                    <div class="modal-button-group">
                        <button type="button" onclick="capturePhoto()" class="btn btn-primary">Capture</button>
                    </div>
                </div>
            </div>

            <hr>

            <h4>Uploaded Marketing Materials</h4>

            <div id="announcements-container" class="marketing-posts-container">
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

                        <div id="description-display-{{ $post->id }}" style="padding: 12px;">
                            @if($post->description)
                                <p class="marketing-post-description" id="desc-{{ $post->id }}" style="margin: 0 0 4px 0; padding: 0;">{{ $post->description }}</p>
                            @endif
                            <small class="marketing-post-date" style="padding: 0; margin-bottom: 10px; display: block;">Posted on {{ $post->created_at->format('M d, Y h:i A') }}</small>

                            <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                                <button type="button" onclick="viewFullImage({{ $post->id }}, 'admin')" class="btn btn-gallery" style="padding: 6px 14px; font-size: 13px; border-radius: 5px; min-width: 70px;">View</button>
                                <a href="{{ route('marketing.download', ['url' => urlencode(media_url($post->image_path)), 'filename' => 'marketing-' . $post->id . '.jpg']) }}" class="btn btn-camera" style="padding: 6px 14px; font-size: 13px; border-radius: 5px; min-width: 70px;">Download</a>

                                @if(auth()->guard('admin')->check())
                                    @if(($announcementView ?? 'active') === 'archived')
                                        <form method="POST" action="{{ route('digital-marketing.restore', $post->id) }}" style="display:inline; margin: 0;" onsubmit="return confirm('Restore this post?');">
                                            @csrf
                                            <button type="submit" class="btn btn-camera" style="padding: 6px 14px; font-size: 13px; border-radius: 5px; min-width: 70px;">Restore</button>
                                        </form>
                                    @else
                                        <button type="button" onclick="editPost({{ $post->id }})" class="btn btn-edit" style="padding: 6px 14px; font-size: 13px; border-radius: 5px; min-width: 70px;">Edit</button>
                                        <form method="POST" action="{{ route('digital-marketing.destroy', $post->id) }}" style="display:inline; margin: 0;" onsubmit="return confirm('Archive this post?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-remove" style="padding: 6px 14px; font-size: 13px; border-radius: 5px; min-width: 70px; margin: 0;">Archive</button>
                                        </form>
                                    @endif
                                @endif

                                @if($post->description)
                                    <button type="button" class="btn btn-edit" onclick="copyDescription({{ $post->id }})" title="Copy Description" style="padding: 6px 14px; font-size: 13px; border-radius: 5px; min-width: 70px;">Copy</button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="empty-state">No digital marketing posts uploaded yet.</p>
                @endforelse
            </div>
        </section>
    @endif

    @if(auth()->guard('franchisee')->check())
        <section class="marketing-section panel">
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
                        <div style="padding: 12px;">
                            @if($post->description)
                                <p class="marketing-post-description" id="franchisee-desc-{{ $post->id }}" style="margin: 0 0 4px 0; padding: 0;">{{ $post->description }}</p>
                            @endif
                            <small class="marketing-post-date" style="padding: 0; margin-bottom: 10px; display: block;">Posted on {{ $post->created_at->format('M d, Y h:i A') }}</small>

                            <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                                <button type="button" onclick="viewFullImage({{ $post->id }}, 'franchisee')" class="btn btn-gallery" style="padding: 6px 14px; font-size: 13px; border-radius: 5px; min-width: 70px;">View</button>
                                <a href="{{ route('marketing.download', ['url' => urlencode(media_url($post->image_path)), 'filename' => 'marketing-' . $post->id . '.jpg']) }}" class="btn btn-camera" style="padding: 6px 14px; font-size: 13px; border-radius: 5px; min-width: 70px;">Download</a>
                                @if($post->description)
                                    <button type="button" class="btn btn-edit" onclick="copyFranchiseeDescription({{ $post->id }})" title="Copy Description" style="padding: 6px 14px; font-size: 13px; border-radius: 5px; min-width: 70px;">Copy</button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="empty-state">No digital marketing posts available.</p>
                @endforelse
            </div>
        </section>
    @endif

    </div>

    <div id="image-modal" class="camera-modal" onclick="closeImageModal()">
        <div class="camera-modal-content" onclick="event.stopPropagation()">
                <button type="button" onclick="closeImageModal()" class="image-modal-close" aria-label="Close image preview">&times;</button>
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


    function copyDescription(postId) {
        const descElem = document.getElementById('desc-' + postId);
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


    function copyFranchiseeDescription(postId) {
        const descElem = document.getElementById('franchisee-desc-' + postId);
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

    function closeImageModal() {
        document.getElementById('image-modal').classList.remove('show');
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeImageModal();
            closeCameraModal();
        }
    });

    // Conversation list AJAX toggle (smooth switch between active/archived)
    (function() {
        const container = document.getElementById('conversations-container');
        if (!container) return;

        function setActiveButton(view) {
            document.querySelectorAll('.conversation-toggle').forEach(btn => {
                if (btn.dataset.view === view) btn.classList.add('is-active');
                else btn.classList.remove('is-active');
            });
        }

        async function loadConversations(url, pushState = true) {
            const oldList = container.querySelector('.conversations-list-refined');
            if (oldList) {
                oldList.style.transition = 'opacity 100ms ease';
                oldList.style.opacity = '0';
            }

            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const text = await res.text();
                const doc = new DOMParser().parseFromString(text, 'text/html');
                const newList = doc.querySelector('.conversations-list-refined');
                if (newList) {
                    // Replace list
                    await new Promise(r => setTimeout(r, 160));
                    container.innerHTML = '';
                    container.appendChild(newList.cloneNode(true));

                    // Fade in
                    const inserted = container.querySelector('.conversations-list-refined');
                    inserted.style.opacity = '0';
                    inserted.style.transition = 'opacity 220ms ease';
                    requestAnimationFrame(() => inserted.style.opacity = '1');
                } else {
                    // Fallback to full navigation if partial not found
                    location.href = url;
                }

                // Update URL without full reload
                if (pushState) history.pushState({}, '', url);

                // Update active button state by inspecting query param
                const params = new URL(url).searchParams;
                setActiveButton(params.get('conversation_view') || 'active');
            } catch (err) {
                console.error('Failed to load conversations', err);
                location.href = url;
            }
        }

        // Attach click handlers
        document.querySelectorAll('.conversation-toggle').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                loadConversations(url);
            });
        });

        // Handle browser back/forward
        window.addEventListener('popstate', function() {
            loadConversations(location.href, false);
        });

        // initialize active button state
        const params = new URL(location.href).searchParams;
        setActiveButton(params.get('conversation_view') || 'active');
    })();

    // Announcement AJAX toggle (Active / Archived) — swaps announcement posts without navigating away
    (function() {
        const container = document.getElementById('announcements-container');
        if (!container) return;

        function setActiveButton(view) {
            document.querySelectorAll('.announcement-toggle').forEach(btn => {
                if (btn.dataset.view === view) btn.classList.add('is-active');
                else btn.classList.remove('is-active');
            });
        }

        async function loadAnnouncements(url, pushState = true) {
            // show overlay
            let overlay = container.querySelector('.conversations-loading-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'conversations-loading-overlay';
                overlay.innerHTML = '<div class="skeleton-row"></div><div class="skeleton-row short"></div><div class="skeleton-row"></div>';
                container.appendChild(overlay);
                getComputedStyle(overlay).opacity;
            }
            overlay.style.opacity = '1';

            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const text = await res.text();
                const doc = new DOMParser().parseFromString(text, 'text/html');
                const newList = doc.querySelector('#announcements-container');

                if (newList) {
                    container.innerHTML = newList.innerHTML;

                    // hide overlay
                    overlay.style.transition = 'opacity 160ms ease';
                    overlay.style.opacity = '0';
                    setTimeout(() => { try { overlay.remove(); } catch(e){} }, 180);

                    if (pushState) history.pushState({}, '', url);
                    const params = new URL(url).searchParams;
                    setActiveButton(params.get('announcement_view') || 'active');
                    return;
                }

                // fallback
                location.href = url;
            } catch (err) {
                console.error('Failed to load announcements', err);
                location.href = url;
            }
        }

        document.querySelectorAll('.announcement-toggle').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                loadAnnouncements(this.href);
            });
        });

        window.addEventListener('popstate', function() {
            loadAnnouncements(location.href, false);
        });

        const params = new URL(location.href).searchParams;
        setActiveButton(params.get('announcement_view') || 'active');
    })();
</script>
@endsection
