<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Chat</title>

    @vite(['resources/js/app.js'])

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .chat-container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }
        .messages {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 10px;
            max-width: 70%;
            word-wrap: break-word;
        }
        .sent {
            background-color: #007bff;
            color: white;
            margin-left: auto;
            text-align: right;
        }
        .received {
            background-color: #e9ecef;
            color: black;
        }
        .message-time {
            font-size: 0.75em;
            opacity: 0.7;
            margin-top: 3px;
        }
        .chat-form {
            padding: 15px;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 5px;
            align-items: center;
        }
        .file-input-wrapper {
            display: flex;
            gap: 5px;
        }
        .file-btn {
            cursor: pointer;
            font-size: 18px;
            border: none;
            background: none;
            padding: 5px 10px;
        }
        .file-btn:hover {
            opacity: 0.7;
        }
        .message-time {
            font-size: 12px;
            margin-top: 5px;
            opacity: 0.7;
            display: block;
        }
        .file-input-group {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .file-btn {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.2s;
        }
        .file-btn:hover {
            opacity: 0.7;
        }
        #file-name-display {
            font-size: 12px;
            color: #666;
            margin-left: 5px;
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .chat-image {
            max-width: 180px !important;
            max-height: 220px !important;
            width: auto !important;
            height: auto !important;
            margin-top: 5px;
            border-radius: 10px;
            display: block;
            object-fit: contain;
        }
        .sending-status {
            display: block;
            font-size: 11px;
            margin-top: 3px;
            opacity: 0.8;
        }
    </style>
</head>

<body>
<div class="chat-container">

    {{-- MESSAGES --}}
    <div id="messages" class="messages">
        @foreach($messages as $msg)
            @php
                $isCurrentUser =
                    $currentUserId == $msg->sender_id &&
                    $currentUserType == $msg->sender_type;

                $displayName = $msg->sender_name ?? 'User';
                $class = $isCurrentUser ? 'sent' : 'received';
            @endphp

            <div class="message {{ $class }}" data-message-id="{{ $msg->id }}">
                <strong>{{ $displayName }}:</strong>

                @if($msg->message_text)
                    <div>{{ $msg->message_text }}</div>
                @endif

                {{-- IMAGE --}}
                @if(
                    $msg->file_path &&
                    str_starts_with($msg->file_type, 'image/') &&
                    Storage::disk('public')->exists($msg->file_path)
                )
                    <img
                        src="{{ Storage::url($msg->file_path) }}"
                        class="chat-image"
                        alt="Image"
                    >
                @endif

                {{-- FILE --}}
                @if(
                    $msg->file_path &&
                    !str_starts_with($msg->file_type, 'image/') &&
                    Storage::disk('public')->exists($msg->file_path)
                )
                    <div class="attachment">
                        <a href="{{ Storage::url($msg->file_path) }}" download="{{ $msg->file_name }}">
                            📎 {{ $msg->file_name }}
                        </a>
                    </div>
                @endif

                <span class="message-time">{{ $msg->created_at->format('h:i A') }}</span>
            </div>
        @endforeach
    </div>

    {{-- FORM --}}
    <form id="chat-form" class="chat-form" enctype="multipart/form-data">
        @csrf
        <input type="text" name="message_text" id="message_text" placeholder="Type a message…" style="flex:1">
        <div class="file-input-group">
            <button type="button" class="file-btn" id="file-btn" title="Attach Photo or File">
                📎
            </button>
            <span id="file-name-display"></span>
            <button type="button" class="file-btn" id="clear-file-btn" title="Remove attachment" style="display: none; color: red;">
                ✕
            </button>
        </div>
        <button type="submit">Send</button>
        <input type="file" name="file" id="file-input" accept="image/*,.pdf,.doc,.docx,.txt" hidden>
    </form>

</div>

<script>
const currentUserId = {{ $currentUserId !== null ? $currentUserId : 'null' }};
const currentUserType = "{{ $currentUserType }}";
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
        clearFileBtn.style.display = 'inline-block';
    }
});

// Clear file button
clearFileBtn.addEventListener('click', (e) => {
    e.preventDefault();
    selectedFile = null;
    fileInput.value = '';
    fileNameDisplay.textContent = '';
    clearFileBtn.style.display = 'none';
});

/* 
   FORM SUBMIT
*/
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

    // TEMP MESSAGE (instant UI)
    if (file && file.type.startsWith('image/')) {
        previewImage(file, preview => {
            appendTempMessage(tempId, text, preview);
        });
    } else {
        appendTempMessage(tempId, text, null);
    }

    sendForm(formData, tempId);
});

/* 
   SEND FORM - FIXED WITH PROPER ERROR HANDLING
 */
function sendForm(formData, tempId) {
    fetch('/communication/{{ $conversation->id }}/send', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            // Handle HTTP errors
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
        console.log('Message sent successfully:', msg);
        
        // Remove temp message
        const tempEl = document.querySelector(`[data-temp-id="${tempId}"]`);
        if (tempEl) tempEl.remove();

        appendMessage(msg);
        lastMessageId = msg.id;

        // Clear form
        messageInput.value = '';
        selectedFile = null;
        fileNameDisplay.textContent = '';
        fileInput.value = '';
        clearFileBtn.style.display = 'none';
    })
    .catch(err => {
        console.error('Send error:', err);
        
        const tempEl = document.querySelector(`[data-temp-id="${tempId}"]`);
        if (tempEl) {
            const statusEl = tempEl.querySelector('.sending-status');
            if (statusEl) {
                statusEl.innerText = 'Failed: ' + err.message;
                statusEl.style.color = '#ff4444';
            }
        }
        
        // Show alert with error
        alert('Failed to send message:\n' + err.message);
    });
}

/*
   POLLING (NO DUPLICATES)
 */
function fetchNewMessages() {
    fetch(`/communication/{{ $conversation->id }}/messages?after=${lastMessageId}`)
        .then(res => {
            if (!res.ok) throw new Error('Failed to fetch messages');
            return res.json();
        })
        .then(messages => {
            messages.forEach(msg => {
                // Skip own messages
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

/* 
   FORMAT TIME
*/
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

/* 
   APPEND FINAL MESSAGE
*/
function appendMessage(message) {
    if (document.querySelector(`[data-message-id="${message.id}"]`)) return;

    const isMe =
        currentUserId == message.sender_id &&
        currentUserType === message.sender_type;

    const className = isMe ? 'sent' : 'received';
    const displayName = message.sender_name || 'Unknown User';
    const fileUrl = message.file_path ? `/storage/${message.file_path}?t=${Date.now()}` : '';
    const timeStr = message.formatted_time || formatTime(message.created_at);

    let attachment = '';
    if (message.file_path && message.file_type?.startsWith('image/')) {
        attachment = `<img src="${fileUrl}" class="chat-image">`;
    } else if (message.file_path) {
        attachment = `
            <div class="attachment">
                <a href="${fileUrl}" download="${message.file_name}">
                    📎 ${message.file_name}
                </a>
            </div>`;
    } else if (message.file_name) {
        attachment = `
            <div class="attachment">
                <span style="opacity: 0.7;">📎 ${message.file_name} (processing...)</span>
            </div>`;
    }

    messagesBox.insertAdjacentHTML('beforeend', `
        <div class="message ${className}" data-message-id="${message.id}">
            <strong>${displayName}:</strong>
            <div>${message.message_text || ''}</div>
            ${attachment}
            <span class="message-time">${timeStr}</span>
        </div>
    `);

    scrollToBottom();
}

/* 
   TEMP MESSAGE
 */
function appendTempMessage(tempId, text, preview) {
    const now = new Date();
    let hours = now.getHours();
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    const timeStr = `${hours}:${minutes} ${ampm}`;

    messagesBox.insertAdjacentHTML('beforeend', `
        <div class="message sent" data-temp-id="${tempId}">
            <strong>You:</strong>
            <div>${text || ''}</div>
            ${preview ? `<img src="${preview}" class="chat-image">` : ''}
            <small class="sending-status">Sending…</small>
            <span class="message-time">${timeStr}</span>
        </div>
    `);
    scrollToBottom();
}

/*
   IMAGE PREVIEW
*/
function previewImage(file, callback) {
    const reader = new FileReader();
    reader.onload = e => callback(e.target.result);
    reader.readAsDataURL(file);
}

function scrollToBottom() {
    messagesBox.scrollTop = messagesBox.scrollHeight;
}

// REAL-TIME WEBSOCKET LISTENER
if (window.Echo) {
    window.Echo.private('conversation.{{ $conversation->id }}')
        .listen('MessageSent', (e) => {
            console.log('New message received via WebSocket:', e.message);
            
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