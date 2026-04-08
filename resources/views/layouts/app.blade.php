<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'D-Sizzlers') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <script>
            window.currentUserId = @json(auth()->id());
        </script>
        
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" style="background-color: #F5F5F5;">
        @if(auth()->guard('admin')->check())
            @include('layouts.navigation')
        @elseif(auth()->guard('franchisee')->check())
            @include('layouts.navigation-franchisee')
        @elseif(auth()->guard('franchisor_staff')->check())
            @include('layouts.navigation-franchisor-staff')
        @elseif(auth()->guard('franchisee_staff')->check())
            @include('layouts.navigation-franchisee-staff')
        @else
            @include('layouts.navigation')
        @endif

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Floating Messages Button and Modal -->
        @if(auth()->guard('franchisee')->check() || auth()->guard('admin')->check())
        <button id="openChatModalBtn" class="floating-messages-btn" title="Messages" type="button" style="z-index:9999;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M4.913 2.658c2.075-.27 4.19-.408 6.337-.408 2.147 0 4.262.139 6.337.408 1.922.25 3.291 1.861 3.405 3.727a4.403 4.403 0 0 0-1.032-.211 50.89 50.89 0 0 0-8.42 0c-2.358.196-4.04 2.19-4.04 4.434v4.286a4.47 4.47 0 0 0 2.433 3.984L7.28 21.53A.75.75 0 0 1 6 21v-4.03a48.527 48.527 0 0 1-1.087-.128C2.905 16.58 1.5 14.833 1.5 12.862V6.638c0-1.97 1.405-3.718 3.413-3.979Z" />
                <path d="M15.75 7.5c-1.376 0-2.739.057-4.086.169C10.124 7.797 9 9.103 9 10.609v4.285c0 1.507 1.128 2.814 2.67 2.94 1.243.102 2.5.157 3.768.165l2.782 2.781a.75.75 0 0 0 1.28-.53v-2.39l.33-.026c1.542-.125 2.67-1.433 2.67-2.94v-4.286c0-1.505-1.125-2.811-2.664-2.94A49.392 49.392 0 0 0 15.75 7.5Z" />
            </svg>
        </button>
        <div id="chatModal" style="display:none; position:fixed; bottom:100px; right:32px; width:420px; max-width:95vw; max-height:80vh; background:#fff; border-radius:16px; box-shadow:0 8px 32px rgba(0,0,0,0.25); z-index:10000; overflow:auto;">
            <button onclick="document.getElementById('chatModal').style.display='none'" style="position:absolute;top:8px;right:8px;z-index:10001;">&times;</button>
            <div id="chatModalContent" style="height:calc(80vh - 40px);overflow:auto;"></div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('openChatModalBtn');
            var modal = document.getElementById('chatModal');
            var content = document.getElementById('chatModalContent');
            btn && btn.addEventListener('click', function() {
                modal.style.display = 'block';
                if (!content.innerHTML.trim()) {
                    fetch("{{ route('communication.chat-list') }}")
                        .then(response => response.text())
                        .then(html => { content.innerHTML = html; attachChatLinks(); });
                } else {
                    attachChatLinks();
                }
            });
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    modal.style.display = 'none';
                }
            });
            function attachChatLinks() {
                setTimeout(function() {
                    var links = content.querySelectorAll('.open-chat-link');
                    links.forEach(function(link) {
                        link.onclick = function(e) {
                            e.preventDefault();
                            var url = this.getAttribute('data-chat-url');
                            fetch(url)
                                .then(response => response.text())
                                .then(html => {
                                    // Remove <script> blocks from AJAX-loaded chat view
                                    var tempDiv = document.createElement('div');
                                    tempDiv.innerHTML = html.replace(/<script[\s\S]*?<\/script>/gi, '');
                                    var chatForm = tempDiv.querySelector('#chat-form');
                                    if (chatForm) chatForm.removeAttribute('action');
                                    content.innerHTML = '<button id="backToChatList" style="margin:10px 0 10px 0; padding:8px; background:var(--dsizzlers-orange); color:#fff; border:none; border-radius:50%; box-shadow:var(--shadow-sm); cursor:pointer; transition:background 0.2s; display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px;" title="Back" onmouseover="this.style.background=\'var(--dsizzlers-orange-dark)\'" onmouseout="this.style.background=\'var(--dsizzlers-orange)\'">' +
                                        '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:20px;height:20px;vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>' +
                                    '</button>' + tempDiv.innerHTML;
                                    document.getElementById('backToChatList').onclick = function() {
                                        fetch("{{ route('communication.chat-list') }}")
                                            .then(response => response.text())
                                            .then(html => { content.innerHTML = html; attachChatLinks(); });
                                    };
                                    // --- Begin: Modal chat form logic copied from chat.blade.php ---
                                    (function() {
                                        var chatForm = content.querySelector('#chat-form');
                                        if (!chatForm) return;
                                        var messageInput = content.querySelector('#message_text');
                                        var fileInput = content.querySelector('#file-input');
                                        var fileBtn = content.querySelector('#file-btn');
                                        var clearFileBtn = content.querySelector('#clear-file-btn');
                                        var fileNameDisplay = content.querySelector('#file-name-display');
                                        var messagesBox = content.querySelector('#messages');
                                        var selectedFile = null;
                                        var lastMessageId = 0;
                                        // Find last message id
                                        var lastMsg = messagesBox && messagesBox.querySelector('.chat-message:last-child');
                                        if (lastMsg && lastMsg.dataset.messageId) {
                                            lastMessageId = parseInt(lastMsg.dataset.messageId, 10) || 0;
                                        }
                                        if (fileBtn && fileInput) {
                                            fileBtn.addEventListener('click', function(e) {
                                                e.preventDefault();
                                                fileInput.click();
                                            });
                                        }
                                        if (fileInput) {
                                            fileInput.addEventListener('change', function(e) {
                                                if (e.target.files[0]) {
                                                    selectedFile = e.target.files[0];
                                                    if (fileNameDisplay) fileNameDisplay.textContent = '✓ ' + selectedFile.name;
                                                    if (clearFileBtn) clearFileBtn.classList.add('show');
                                                }
                                            });
                                        }
                                        if (clearFileBtn) {
                                            clearFileBtn.addEventListener('click', function(e) {
                                                e.preventDefault();
                                                selectedFile = null;
                                                if (fileInput) fileInput.value = '';
                                                if (fileNameDisplay) fileNameDisplay.textContent = '';
                                                clearFileBtn.classList.remove('show');
                                            });
                                        }
                                        chatForm.addEventListener('submit', function(e) {
                                            e.preventDefault();
                                            var text = messageInput.value.trim();
                                            var file = selectedFile || (fileInput && fileInput.files[0]) || null;
                                            if (!text && !file) return;
                                            var tempId = 'temp-' + Date.now();
                                            // Optimistic UI: show temp message with spinner
                                            function injectSpinnerCSS() {
                                                if (!document.getElementById('chat-spinner-style')) {
                                                    var style = document.createElement('style');
                                                    style.id = 'chat-spinner-style';
                                                    style.innerHTML = '@keyframes spin { 0% { transform: rotate(0deg);} 100% { transform: rotate(360deg);} }';
                                                    document.head.appendChild(style);
                                                }
                                            }
                                            injectSpinnerCSS();
                                            var timeStr = new Date();
                                            var preview = null;
                                            if (file && file.type && file.type.startsWith('image/')) {
                                                var reader = new FileReader();
                                                reader.onload = function(e) {
                                                    preview = e.target.result;
                                                    appendTemp();
                                                };
                                                reader.readAsDataURL(file);
                                            } else {
                                                appendTemp();
                                            }
                                            function appendTemp() {
                                                var html = '<div class="chat-message sent" data-temp-id="' + tempId + '"><div class="chat-avatar"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></div><div class="chat-message-content"><div class="chat-bubble"><div class="chat-sender-name">You</div>';
                                                if (text) html += '<div class="chat-message-text">' + text + '</div>';
                                                if (preview) html += '<img src="' + preview + '" class="chat-image">';
                                                html += '<small class="chat-sending-status" style="display:flex;align-items:center;gap:6px;color:#888;"><span class="chat-spinner" style="display:inline-block;width:16px;height:16px;border:2px solid #ccc;border-top:2px solid #ff9800;border-radius:50%;animation:spin 1s linear infinite;"></span>Sending…</small>';
                                                html += '</div><span class="chat-message-time">' + (typeof formatTime === 'function' ? formatTime(timeStr.toISOString()) : 'now') + '</span></div></div>';
                                                if (messagesBox) {
                                                    messagesBox.insertAdjacentHTML('beforeend', html);
                                                    messagesBox.scrollTop = messagesBox.scrollHeight;
                                                }
                                            }
                                            var formData = new FormData();
                                            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                                            formData.append('message_text', text);
                                            if (file) formData.append('file', file);
                                            // Get conversation id from URL
                                            var match = window.location.pathname.match(/\/communication\/(\d+)/);
                                            var convId = match ? match[1] : null;
                                            if (!convId) {
                                                // Try to get from chatForm data attribute or hidden input
                                                var convInput = chatForm.querySelector('input[name="conversation_id"]');
                                                if (convInput) convId = convInput.value;
                                            }
                                            if (!convId) {
                                                alert('Conversation ID missing.');
                                                return;
                                            }
                                            var postUrl = '/communication/' + convId + '/send';
                                            fetch(postUrl, {
                                                method: 'POST',
                                                body: formData,
                                                headers: {
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                                }
                                            })
                                            .then(response => {
                                                if (!response.ok) {
                                                    return response.text().then(text => { throw new Error(text); });
                                                }
                                                return response.json();
                                            })
                                            .then(msg => {
                                                // Remove temp message
                                                var tempEl = messagesBox && messagesBox.querySelector('[data-temp-id="' + tempId + '"]');
                                                if (tempEl) tempEl.remove();
                                                if (messagesBox) {
                                                    var msgHtml = '<div class="chat-message sent" data-message-id="' + msg.id + '"><div class="chat-avatar"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></div><div class="chat-message-content"><div class="chat-bubble"><div class="chat-sender-name">You</div>';
                                                    if (msg.message_text) msgHtml += '<div class="chat-message-text">' + msg.message_text + '</div>';
                                                    if (msg.file_path && msg.file_type && msg.file_type.startsWith('image/')) msgHtml += '<img src="' + msg.file_path + '" class="chat-image">';
                                                    else if (msg.file_path) msgHtml += '<div class="chat-attachment"><a href="' + msg.file_path + '" download>' + (msg.file_name || 'Attachment') + '</a></div>';
                                                    msgHtml += '</div><span class="chat-message-time">now</span></div></div>';
                                                    messagesBox.insertAdjacentHTML('beforeend', msgHtml);
                                                    messagesBox.scrollTop = messagesBox.scrollHeight;
                                                    lastMessageId = msg.id;
                                                }
                                                messageInput.value = '';
                                                if (fileInput) fileInput.value = '';
                                                if (fileNameDisplay) fileNameDisplay.textContent = '';
                                                if (clearFileBtn) clearFileBtn.classList.remove('show');
                                            })
                                            .catch(err => {
                                                // Show error on temp message
                                                var tempEl = messagesBox && messagesBox.querySelector('[data-temp-id="' + tempId + '"]');
                                                if (tempEl) {
                                                    var statusEl = tempEl.querySelector('.chat-sending-status');
                                                    if (statusEl) {
                                                        statusEl.innerText = 'Failed: ' + err.message;
                                                        statusEl.style.color = '#ff4444';
                                                    }
                                                }
                                                alert('Failed to send message.');
                                            });
                                        });
                                        // --- Polling for new messages (copied from chat.blade) ---
                                        function fetchNewMessages() {
                                            var match = window.location.pathname.match(/\/communication\/(\d+)/);
                                            var convId = match ? match[1] : null;
                                            if (!convId) {
                                                var convInput = chatForm.querySelector('input[name="conversation_id"]');
                                                if (convInput) convId = convInput.value;
                                            }
                                            if (!convId) return;
                                            fetch('/communication/' + convId + '/messages?after=' + lastMessageId)
                                                .then(res => {
                                                    if (!res.ok) throw new Error('Failed to fetch messages');
                                                    return res.json();
                                                })
                                                .then(messages => {
                                                    messages.forEach(function(msg) {
                                                        if (document.querySelector('[data-message-id="' + msg.id + '"]')) return;
                                                        var className = (msg.sender_type === 'admin' || msg.sender_type === 'franchisor_staff') ? 'sent' : 'received';
                                                        var senderName = msg.sender_name || 'User';
                                                        var fileUrl = msg.file_path ? (msg.file_path.startsWith('http') ? msg.file_path : '/storage/' + msg.file_path) : '';
                                                        var timeStr = msg.formatted_time || '';
                                                        var attachment = '';
                                                        if (msg.file_path && msg.file_type && msg.file_type.startsWith('image/')) {
                                                            attachment = '<img src="' + fileUrl + '" class="chat-image">';
                                                        } else if (msg.file_path) {
                                                            attachment = '<div class="chat-attachment"><a href="' + fileUrl + '" download>' + (msg.file_name || 'Attachment') + '</a></div>';
                                                        }
                                                        var msgHtml = '<div class="chat-message ' + className + '" data-message-id="' + msg.id + '"><div class="chat-avatar"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></div><div class="chat-message-content"><div class="chat-bubble"><div class="chat-sender-name">' + senderName + '</div>';
                                                        if (msg.message_text) msgHtml += '<div class="chat-message-text">' + msg.message_text + '</div>';
                                                        msgHtml += attachment;
                                                        msgHtml += '</div><span class="chat-message-time">' + timeStr + '</span></div></div>';
                                                        messagesBox.insertAdjacentHTML('beforeend', msgHtml);
                                                        messagesBox.scrollTop = messagesBox.scrollHeight;
                                                        lastMessageId = msg.id;
                                                    });
                                                })
                                                .catch(function(err) {
                                                    // Optionally log error
                                                });
                                        }
                                        setInterval(fetchNewMessages, 3000);
                                    })();
                                    // --- End: Modal chat form logic copied from chat.blade.php ---
                                });
                        };
                    });
                }, 100);
            }
        });
        </script>
        @endif
        @stack('scripts')
        <!-- Footer -->
        <footer class="w-full bg-gray-800 text-white text-center py-4 mt-8">
            <div class="container mx-auto">
                &copy; {{ date('Y') }} DSizzlers. All rights reserved.
            </div>
        </footer>
    </body>
</html>
