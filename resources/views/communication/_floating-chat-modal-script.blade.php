<script>
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('openChatModalBtn');
    var modal = document.getElementById('chatModal');
    var content = document.getElementById('chatModalContent');
    var headerLeft = document.getElementById('chatModalHeaderLeft');
    var headerTitle = document.getElementById('chatModalTitle');
    var closeBtn = modal ? modal.querySelector('button[aria-label="Close messages"]') : null;
    var pollTimer = null;
    var activeConversationChannel = null;

    function setChatModalHeader(title, showBackButton) {
        if (headerTitle) {
            headerTitle.textContent = title || 'Messages';
        }

        if (!headerLeft) return;

        if (showBackButton) {
            headerLeft.innerHTML = '<button id="backToChatList" type="button" aria-label="Back to conversations" style="display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border:none; border-radius:999px; background:rgba(255,255,255,0.18); color:#fff; cursor:pointer; box-shadow:0 8px 18px rgba(0,0,0,0.12); transition:background 0.2s ease, transform 0.2s ease;" onmouseover="this.style.background=\'rgba(255,255,255,0.28)\'; this.style.transform=\'translateX(-1px)\'" onmouseout="this.style.background=\'rgba(255,255,255,0.18)\'; this.style.transform=\'translateX(0)\'"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg></button>';
        } else {
            headerLeft.innerHTML = '';
        }
    }

    function clearPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function leaveActiveConversationChannel() {
        if (!activeConversationChannel || !window.Echo) {
            activeConversationChannel = null;
            return;
        }

        window.Echo.leave(activeConversationChannel);
        activeConversationChannel = null;
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatTime(isoString) {
        if (!isoString) return '';

        var date = new Date(isoString);
        if (isNaN(date.getTime())) return '';

        var hours = date.getHours();
        var minutes = String(date.getMinutes()).padStart(2, '0');
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;

        return hours + ':' + minutes + ' ' + ampm;
    }

    function normalizeFileUrl(filePath) {
        if (!filePath) return '';
        if (/^https?:\/\//i.test(filePath)) return filePath;
        return '/storage/' + filePath.replace(/^\/+/, '');
    }

    function getCurrentChatElements() {
        var chatContainer = content.querySelector('.chat-container');
        var chatForm = content.querySelector('#chat-form');
        var messagesBox = content.querySelector('#messages');

        if (!chatContainer || !chatForm || !messagesBox) {
            return null;
        }

        return {
            chatContainer: chatContainer,
            chatForm: chatForm,
            messagesBox: messagesBox,
            messageInput: content.querySelector('#message_text'),
            fileInput: content.querySelector('#file-input'),
            fileBtn: content.querySelector('#file-btn'),
            clearFileBtn: content.querySelector('#clear-file-btn'),
            fileNameDisplay: content.querySelector('#file-name-display')
        };
    }

    function renderMessageHtml(message, state) {
        var isCurrentUserMessage = String(message.sender_id) === state.currentUserId && message.sender_type === state.currentUserType;
        var className = isCurrentUserMessage ? 'sent' : 'received';
        var senderName = isCurrentUserMessage ? state.currentUserName : (message.sender_name || 'User');
        var fileUrl = normalizeFileUrl(message.file_path);
        var attachmentHtml = '';

        if (message.file_path && message.file_type && message.file_type.indexOf('image/') === 0) {
            attachmentHtml = '<img src="' + escapeHtml(fileUrl) + '" class="chat-image" alt="Attachment">';
        } else if (message.file_path) {
            attachmentHtml = '<div class="chat-attachment"><a href="' + escapeHtml(fileUrl) + '" download="' + escapeHtml(message.file_name || 'Attachment') + '">' + escapeHtml(message.file_name || 'Attachment') + '</a></div>';
        }

        return '<div class="chat-message ' + className + '" data-message-id="' + escapeHtml(message.id) + '"><div class="chat-avatar"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></div><div class="chat-message-content"><div class="chat-bubble"><div class="chat-sender-name">' + escapeHtml(senderName) + '</div>' + (message.message_text ? '<div class="chat-message-text">' + escapeHtml(message.message_text) + '</div>' : '') + attachmentHtml + '</div><span class="chat-message-time">' + escapeHtml(message.formatted_time || formatTime(message.created_at)) + '</span></div></div>';
    }

    function injectSpinnerCss() {
        if (document.getElementById('chat-spinner-style')) return;

        var style = document.createElement('style');
        style.id = 'chat-spinner-style';
        style.textContent = '@keyframes spin { 0% { transform: rotate(0deg);} 100% { transform: rotate(360deg);} }';
        document.head.appendChild(style);
    }

    function initializeModalChatForm() {
        clearPolling();
        leaveActiveConversationChannel();

        var elements = getCurrentChatElements();
        if (!elements) return;

        var chatContainer = elements.chatContainer;
        var chatForm = elements.chatForm;
        var messagesBox = elements.messagesBox;
        var messageInput = elements.messageInput;
        var fileInput = elements.fileInput;
        var fileBtn = elements.fileBtn;
        var clearFileBtn = elements.clearFileBtn;
        var fileNameDisplay = elements.fileNameDisplay;
        var selectedFile = null;
        var state = {
            conversationId: chatForm.dataset.conversationId || chatContainer.dataset.conversationId || '',
            currentUserId: String(chatContainer.dataset.currentUserId || ''),
            currentUserType: chatContainer.dataset.currentUserType || '',
            currentUserName: chatContainer.dataset.currentUserName || 'You',
            lastMessageId: 0
        };

        var lastMessage = messagesBox.querySelector('.chat-message:last-child');
        if (lastMessage && lastMessage.dataset.messageId) {
            state.lastMessageId = parseInt(lastMessage.dataset.messageId, 10) || 0;
        }

        function scrollToBottom() {
            messagesBox.scrollTo({ top: messagesBox.scrollHeight, behavior: 'auto' });

            var lastMessage = messagesBox.querySelector('.chat-message:last-child');
            if (lastMessage) {
                lastMessage.scrollIntoView({ block: 'end', inline: 'nearest' });
            }
        }

        function scrollToBottomAfterRender() {
            scrollToBottom();
            requestAnimationFrame(scrollToBottom);
            setTimeout(scrollToBottom, 120);
            setTimeout(scrollToBottom, 320);
            setTimeout(scrollToBottom, 600);

            messagesBox.querySelectorAll('img').forEach(function(img) {
                if (img.complete) return;
                img.addEventListener('load', scrollToBottom, { once: true });
                img.addEventListener('error', scrollToBottom, { once: true });
            });
        }

        function resetFileInput() {
            selectedFile = null;
            if (fileInput) fileInput.value = '';
            if (fileNameDisplay) fileNameDisplay.textContent = '';
            if (clearFileBtn) clearFileBtn.classList.remove('show');
        }

        function appendTempMessage(tempId, text, previewUrl) {
            injectSpinnerCss();

            var tempHtml = '<div class="chat-message sent" data-temp-id="' + tempId + '"><div class="chat-avatar"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></div><div class="chat-message-content"><div class="chat-bubble"><div class="chat-sender-name">' + escapeHtml(state.currentUserName) + '</div>' + (text ? '<div class="chat-message-text">' + escapeHtml(text) + '</div>' : '') + (previewUrl ? '<img src="' + escapeHtml(previewUrl) + '" class="chat-image" alt="Attachment preview">' : '') + '<small class="chat-sending-status" style="display:flex;align-items:center;gap:6px;color:#888;"><span class="chat-spinner" style="display:inline-block;width:16px;height:16px;border:2px solid #ccc;border-top:2px solid #ff9800;border-radius:50%;animation:spin 1s linear infinite;"></span>Sending...</small></div><span class="chat-message-time">' + escapeHtml(formatTime(new Date().toISOString())) + '</span></div></div>';
            messagesBox.insertAdjacentHTML('beforeend', tempHtml);
            scrollToBottom();
        }

        function appendMessage(message) {
            if (messagesBox.querySelector('[data-message-id="' + message.id + '"]')) return;

            messagesBox.insertAdjacentHTML('beforeend', renderMessageHtml(message, state));
            state.lastMessageId = Math.max(state.lastMessageId, parseInt(message.id, 10) || 0);
            scrollToBottomAfterRender();
        }

        function subscribeToRealtimeMessages() {
            if (!window.Echo || !state.conversationId) {
                return;
            }

            activeConversationChannel = 'conversation.' + state.conversationId;

            window.Echo.private(activeConversationChannel)
                .listen('MessageSent', function(event) {
                    if (!event || !event.message) {
                        return;
                    }

                    if (String(event.message.sender_id) === state.currentUserId && event.message.sender_type === state.currentUserType) {
                        state.lastMessageId = Math.max(state.lastMessageId, parseInt(event.message.id, 10) || 0);
                        return;
                    }

                    appendMessage(event.message);
                });
        }

        function fetchNewMessages() {
            if (!state.conversationId) return;

            fetch('/communication/' + state.conversationId + '/messages?after=' + state.lastMessageId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache'
                },
                cache: 'no-store'
            })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Failed to fetch messages');
                    }

                    return response.json();
                })
                .then(function(messages) {
                    messages.forEach(function(message) {
                        if (String(message.sender_id) === state.currentUserId && message.sender_type === state.currentUserType) {
                            state.lastMessageId = Math.max(state.lastMessageId, parseInt(message.id, 10) || 0);
                            return;
                        }

                        appendMessage(message);
                    });
                })
                .catch(function() {});
        }

        function syncMessagesFromServer() {
            if (!state.conversationId) return;

            fetch('/communication/' + state.conversationId + '/messages', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache'
                },
                cache: 'no-store'
            })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Failed to sync messages');
                    }

                    return response.json();
                })
                .then(function(messages) {
                    var pendingMessages = Array.from(messagesBox.querySelectorAll('[data-temp-id]')).map(function(node) {
                        return node.cloneNode(true);
                    });

                    messagesBox.innerHTML = '';
                    state.lastMessageId = 0;

                    messages.forEach(function(message) {
                        appendMessage(message);
                    });

                    pendingMessages.forEach(function(node) {
                        messagesBox.appendChild(node);
                    });

                    scrollToBottomAfterRender();
                })
                .catch(function() {
                    scrollToBottomAfterRender();
                });
        }

        if (fileBtn && fileInput) {
            fileBtn.addEventListener('click', function(event) {
                event.preventDefault();
                fileInput.click();
            });
        }

        if (fileInput) {
            fileInput.addEventListener('change', function(event) {
                selectedFile = event.target.files && event.target.files[0] ? event.target.files[0] : null;

                if (fileNameDisplay) {
                    fileNameDisplay.textContent = selectedFile ? 'Attached: ' + selectedFile.name : '';
                }

                if (clearFileBtn) {
                    clearFileBtn.classList.toggle('show', !!selectedFile);
                }
            });
        }

        if (clearFileBtn) {
            clearFileBtn.addEventListener('click', function(event) {
                event.preventDefault();
                resetFileInput();
            });
        }

        chatForm.addEventListener('submit', function(event) {
            event.preventDefault();

            var text = messageInput ? messageInput.value.trim() : '';
            var file = selectedFile || (fileInput && fileInput.files && fileInput.files[0]) || null;

            if (!text && !file) return;
            if (!state.conversationId) {
                alert('Conversation ID missing.');
                return;
            }

            var tempId = 'temp-' + Date.now();
            var formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('message_text', text);

            if (file) {
                formData.append('file', file);
            }

            function sendMessage() {
                fetch('/communication/' + state.conversationId + '/send', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(function(response) {
                        if (!response.ok) {
                            return response.text().then(function(textResponse) {
                                var errorMessage = 'Failed to send message.';

                                try {
                                    var json = JSON.parse(textResponse);
                                    if (json.errors) {
                                        errorMessage = Object.values(json.errors).flat().join(', ');
                                    } else {
                                        errorMessage = json.message || json.error || errorMessage;
                                    }
                                } catch (error) {
                                    if (textResponse) {
                                        errorMessage = textResponse;
                                    }
                                }

                                throw new Error(errorMessage);
                            });
                        }

                        return response.json();
                    })
                    .then(function(message) {
                        var tempMessage = messagesBox.querySelector('[data-temp-id="' + tempId + '"]');
                        if (tempMessage) {
                            tempMessage.remove();
                        }

                        appendMessage(message);
                        if (messageInput) {
                            messageInput.value = '';
                        }
                        resetFileInput();
                    })
                    .catch(function(error) {
                        var tempMessage = messagesBox.querySelector('[data-temp-id="' + tempId + '"]');
                        if (!tempMessage) {
                            alert(error.message || 'Failed to send message.');
                            return;
                        }

                        var status = tempMessage.querySelector('.chat-sending-status');
                        if (status) {
                            status.textContent = 'Failed: ' + (error.message || 'Failed to send message.');
                            status.style.color = '#ff4444';
                        }
                    });
            }

            if (file && file.type && file.type.indexOf('image/') === 0) {
                var reader = new FileReader();
                reader.onload = function(loadEvent) {
                    appendTempMessage(tempId, text, loadEvent.target.result);
                    sendMessage();
                };
                reader.readAsDataURL(file);
            } else {
                appendTempMessage(tempId, text, null);
                sendMessage();
            }
        });

        scrollToBottomAfterRender();
        syncMessagesFromServer();
        subscribeToRealtimeMessages();
        pollTimer = setInterval(fetchNewMessages, 3000);
    }

    function bindModalContent() {
        var links = content.querySelectorAll('.open-chat-link');
        var rows = content.querySelectorAll('.conversation-card-row[data-chat-url]');
        var createForm = content.querySelector('.new-conversation-container form');

        links.forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                loadConversation(this.getAttribute('data-chat-url') || this.getAttribute('href'));
            });
        });

        rows.forEach(function(row) {
            row.addEventListener('click', function(event) {
                if (event.target.closest('a, button, form')) return;
                loadConversation(this.getAttribute('data-chat-url'));
            });
        });

        if (createForm) {
            createForm.addEventListener('submit', function(event) {
                event.preventDefault();

                fetch(createForm.action, {
                    method: 'POST',
                    body: new FormData(createForm),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                    .then(function(response) {
                        return response.text();
                    })
                    .then(function(html) {
                        var conversationUrl = extractConversationUrlFromHtml(html);
                        if (conversationUrl) {
                            loadConversation(conversationUrl);
                            return;
                        }

                        renderConversation(html);
                    });
            });
        }
    }

    function renderConversation(html) {
        clearPolling();
        leaveActiveConversationChannel();

        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = html.replace(/<script[\s\S]*?<\/script>/gi, '');

        var chatForm = tempDiv.querySelector('#chat-form');
        if (chatForm) {
            chatForm.removeAttribute('action');
        }

        var conversationHeading = tempDiv.querySelector('.chat-header');
        setChatModalHeader(conversationHeading ? conversationHeading.textContent.trim() : 'Conversation', true);
        if (conversationHeading) {
            conversationHeading.remove();
        }
        content.innerHTML = tempDiv.innerHTML;

        var backButton = document.getElementById('backToChatList');
        if (backButton) {
            backButton.onclick = function() {
                loadChatList();
            };
        }

        initializeModalChatForm();
    }

    function extractConversationUrlFromHtml(html) {
        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = html.replace(/<script[\s\S]*?<\/script>/gi, '');

        var chatForm = tempDiv.querySelector('#chat-form');
        if (chatForm && chatForm.dataset.conversationId) {
            return '/communication/' + chatForm.dataset.conversationId;
        }

        var hiddenConversationInput = tempDiv.querySelector('input[name="conversation_id"]');
        if (hiddenConversationInput && hiddenConversationInput.value) {
            return '/communication/' + hiddenConversationInput.value;
        }

        return null;
    }

    function loadConversation(url) {
        if (!url) return;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
            .then(function(response) {
                return response.text();
            })
            .then(function(html) {
                renderConversation(html);
            });
    }

    function loadChatList() {
        clearPolling();
        leaveActiveConversationChannel();
        setChatModalHeader('Messages', false);

        fetch("{{ route('communication.chat-list') }}", {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
            .then(function(response) {
                return response.text();
            })
            .then(function(html) {
                content.innerHTML = html;
                bindModalContent();
            });
    }

    btn && btn.addEventListener('click', function() {
        modal.style.display = 'block';
        loadChatList();
    });

    closeBtn && closeBtn.addEventListener('click', function() {
        clearPolling();
        leaveActiveConversationChannel();
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            modal.style.display = 'none';
            clearPolling();
            leaveActiveConversationChannel();
        }
    });
});
</script>
