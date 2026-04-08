<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Chat - D Sizzlers</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="chat-page-wrapper">

<div class="chat-header">
    @php
        // Get conversation participants
        $admin = $conversation->admin;
        $franchisee = $conversation->franchisee;
        
        // Build conversation name
        $adminName = $admin 
            ? trim(($admin->admin_fname ?? '') . ' ' . ($admin->admin_lname ?? '')) ?: 'System Administrator'
            : 'System Administrator';
        
        $franchiseeName = $franchisee 
            ? ($franchisee->franchisee_name ?: 'Franchisee') 
            : 'Franchisee';
        
        $conversationTitle = $adminName . ' ↔ ' . $franchiseeName;
    @endphp
    {{ $conversationTitle }}
</div>

<div class="chat-container">
    {{-- MESSAGES --}}
    <div id="messages" class="chat-messages">
        @foreach($messages as $msg)
            @php
                $isCurrentUser =
                    $currentUserId == $msg->sender_id &&
                    $currentUserType == $msg->sender_type;

                $displayName = $msg->sender_name ?? 'User';
                $class = $isCurrentUser ? 'sent' : 'received';
            @endphp

            <div class="chat-message {{ $class }}" data-message-id="{{ $msg->id }}">
                <div class="chat-avatar">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                
                <div class="chat-message-content">
                    <div class="chat-bubble">
                        <div class="chat-sender-name">{{ $displayName }}</div>
                        @if($msg->message_text)
                            <div class="chat-message-text">{{ $msg->message_text }}</div>
                        @endif

                        {{-- IMAGE --}}
                        @if(
                            $msg->file_path &&
                            str_starts_with($msg->file_type, 'image/')
                        )
                            <img
                                src="{{ media_url($msg->file_path) }}"
                                class="chat-image"
                                alt="Image"
                            >
                        @endif

                        {{-- FILE --}}
                        @if(
                            $msg->file_path &&
                            !str_starts_with($msg->file_type, 'image/')
                        )
                            <div class="chat-attachment">
                                <a href="{{ media_url($msg->file_path) }}" download="{{ $msg->file_name }}">
                                    📎 {{ $msg->file_name }}
                                </a>
                            </div>
                        @endif
                    </div>
                    <span class="chat-message-time">{{ $msg->created_at->format('h:i A') }}</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- FORM --}}
    <div class="chat-form-wrapper">
        <form id="chat-form" class="chat-form" enctype="multipart/form-data">
            <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
            @csrf
            <button type="button" class="chat-file-btn" id="file-btn" title="Attach">
                ➕
            </button>
            
            <div class="chat-input-wrapper">
                <input 
                    type="text" 
                    name="message_text" 
                    id="message_text" 
                    class="chat-input" 
                    placeholder="Type a message…"
                    autocomplete="off"
                >
                <span id="file-name-display" class="chat-file-name"></span>
                <button type="button" class="chat-clear-file" id="clear-file-btn" title="Remove">✕</button>
            </div>
            
            <button type="submit" class="chat-send-btn" title="Send">➤</button>
            <input type="file" name="file" id="file-input" accept="image/*,.pdf,.doc,.docx,.txt" hidden>
        </form>
    </div>
</div>

<script>
const currentUserId = {{ $currentUserId !== null ? $currentUserId : 'null' }};
const currentUserType = "{{ $currentUserType }}";
const currentUserName = "{{ $currentUserType === 'admin' ? $adminName : $franchiseeName }}";
let lastMessageId = {{ $messages->last()->id ?? 0 }};

const form = document.getElementById('chat-form');
const messageInput = document.getElementById('message_text');
const fileInput = document.getElementById('file-input');
const fileBtn = document.getElementById('file-btn');
const clearFileBtn = document.getElementById('clear-file-btn');
const fileNameDisplay = document.getElementById('file-name-display');
const messagesBox = document.getElementById('messages');

let selectedFile = null;

// File button
fileBtn.addEventListener('click', (e) => {
    e.preventDefault();
    fileInput.click();
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files[0]) {
        selectedFile = e.target.files[0];
        fileNameDisplay.textContent = '✓ ' + selectedFile.name;
        clearFileBtn.classList.add('show');
    }
});

clearFileBtn.addEventListener('click', (e) => {
    e.preventDefault();
    selectedFile = null;
    fileInput.value = '';
    fileNameDisplay.textContent = '';
    clearFileBtn.classList.remove('show');
});

form.addEventListener('submit', function (e) {
    e.preventDefault();

    const text = messageInput.value.trim();
    const file = selectedFile;

    if (!text && !file) return;

    const tempId = 'temp-' + Date.now();
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('message_text', text);
    if (file) {
        formData.append('file', file);
    }

    if (file && file.type.startsWith('image/')) {
        previewImage(file, preview => {
            appendTempMessage(tempId, text, preview);
        });
    } else {
        appendTempMessage(tempId, text, null);
    }

    sendForm(formData, tempId);
});

function sendForm(formData, tempId) {
    fetch('/communication/{{ $conversation->id }}/send', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                let errorMsg = 'Server error';
                try {
                    const json = JSON.parse(text);
                    errorMsg = json.message || json.error || 'Server error';
                    if (json.errors) {
                        errorMsg = Object.values(json.errors).flat().join(', ');
                    }
                } catch (e) {
                    errorMsg = `HTTP ${response.status}: ${text.substring(0, 100)}`;
                }
                throw new Error(errorMsg);
            });
        }
        return response.json();
    })
    .then(msg => {
        const tempEl = document.querySelector(`[data-temp-id="${tempId}"]`);
        if (tempEl) tempEl.remove();

        appendMessage(msg);
        lastMessageId = msg.id;

        messageInput.value = '';
        selectedFile = null;
        fileNameDisplay.textContent = '';
        fileInput.value = '';
        clearFileBtn.classList.remove('show');
    })
    .catch(err => {
        console.error('Send error:', err);
        
        const tempEl = document.querySelector(`[data-temp-id="${tempId}"]`);
        if (tempEl) {
            const statusEl = tempEl.querySelector('.chat-sending-status');
            if (statusEl) {
                statusEl.innerText = 'Failed: ' + err.message;
                statusEl.style.color = '#ff4444';
            }
        }
        alert('Failed to send message:\n' + err.message);
    });
}

function fetchNewMessages() {
    fetch(`/communication/{{ $conversation->id }}/messages?after=${lastMessageId}`)
        .then(res => {
            if (!res.ok) throw new Error('Failed to fetch messages');
            return res.json();
        })
        .then(messages => {
            messages.forEach(msg => {
                if (
                    msg.sender_id == currentUserId &&
                    msg.sender_type === currentUserType
                ) {
                    lastMessageId = msg.id;
                    return;
                }

                appendMessage(msg);
                lastMessageId = msg.id;
            });
        })
        .catch(err => {
            console.error('Fetch messages error:', err);
        });
}

function formatTime(isoString) {
    if (!isoString) return '';
    const date = new Date(isoString);
    if (isNaN(date.getTime())) return '';
    
    let hours = date.getHours();
    const minutes = date.getMinutes().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    
    return `${hours}:${minutes} ${ampm}`;
}

function appendMessage(message) {
    if (document.querySelector(`[data-message-id="${message.id}"]`)) return;

    const isMe =
        currentUserId == message.sender_id &&
        currentUserType === message.sender_type;

    const className = isMe ? 'sent' : 'received';
    const senderName = message.sender_name || 'User';
    const fileUrl = message.file_path
        ? ((message.file_path.startsWith('http://') || message.file_path.startsWith('https://'))
            ? message.file_path
            : `/storage/${message.file_path}?t=${Date.now()}`)
        : '';
    const timeStr = message.formatted_time || formatTime(message.created_at);

    let attachment = '';
    if (message.file_path && message.file_type?.startsWith('image/')) {
        attachment = `<img src="${fileUrl}" class="chat-image">`;
    } else if (message.file_path) {
        attachment = `
            <div class="chat-attachment">
                <a href="${fileUrl}" download="${message.file_name}">
                    📎 ${message.file_name}
                </a>
            </div>`;
    }

    messagesBox.insertAdjacentHTML('beforeend', `
        <div class="chat-message ${className}" data-message-id="${message.id}">
            <div class="chat-avatar">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <div class="chat-message-content">
                <div class="chat-bubble">
                    <div class="chat-sender-name">${senderName}</div>
                    ${message.message_text ? `<div class="chat-message-text">${message.message_text}</div>` : ''}
                    ${attachment}
                </div>
                <span class="chat-message-time">${timeStr}</span>
            </div>
        </div>
    `);

    scrollToBottom();
}

function appendTempMessage(tempId, text, preview) {
    // Always inject spinner animation CSS if missing (for modal/AJAX context)
    if (!document.getElementById('chat-spinner-style')) {
        const style = document.createElement('style');
        style.id = 'chat-spinner-style';
        style.innerHTML = `@keyframes spin { 0% { transform: rotate(0deg);} 100% { transform: rotate(360deg);} }`;
        document.head.appendChild(style);
    }
    const timeStr = formatTime(new Date().toISOString());
    messagesBox.insertAdjacentHTML('beforeend', `
        <div class="chat-message sent" data-temp-id="${tempId}">
            <div class="chat-avatar">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <div class="chat-message-content">
                <div class="chat-bubble">
                    <div class="chat-sender-name">${currentUserName}</div>
                    ${text ? `<div class="chat-message-text">${text}</div>` : ''}
                    ${preview ? `<img src="${preview}" class="chat-image">` : ''}
                    <small class="chat-sending-status" style="display:flex;align-items:center;gap:6px;color:#888;">
                        <span class="chat-spinner" style="display:inline-block;width:16px;height:16px;border:2px solid #ccc;border-top:2px solid #ff9800;border-radius:50%;animation:spin 1s linear infinite;"></span>
                        Sending…
                    </small>
                </div>
                <span class="chat-message-time">${timeStr}</span>
            </div>
        </div>
    `);
    scrollToBottom();
}

function previewImage(file, callback) {
    const reader = new FileReader();
    reader.onload = e => callback(e.target.result);
    reader.readAsDataURL(file);
}

function scrollToBottom() {
    messagesBox.scrollTop = messagesBox.scrollHeight;
}

if (window.Echo) {
    window.Echo.private('conversation.{{ $conversation->id }}')
        .listen('MessageSent', (e) => {
            if (e.message.sender_id != currentUserId || e.message.sender_type !== currentUserType) {
                appendMessage(e.message);
                lastMessageId = e.message.id;
            }
        });
}

setInterval(fetchNewMessages, 3000);
window.onload = scrollToBottom;
</script>

</body>
</html>