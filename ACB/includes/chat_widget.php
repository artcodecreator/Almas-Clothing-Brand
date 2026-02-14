<?php if(isset($_SESSION['user_id'])): ?>
<style>
  #chat-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
  }
  #chat-button {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: #0d6efd;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    transition: transform 0.2s;
  }
  #chat-button:hover {
    transform: scale(1.1);
  }
  #chat-window {
    display: none;
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 350px;
    height: 450px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    flex-direction: column;
    overflow: hidden;
    z-index: 1001;
  }
  .chat-header {
    background: #0d6efd;
    color: white;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .chat-body {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  .chat-footer {
    padding: 10px;
    border-top: 1px solid #dee2e6;
    background: white;
    display: flex;
    gap: 10px;
  }
  .message {
    max-width: 80%;
    padding: 8px 12px;
    border-radius: 15px;
    font-size: 0.9rem;
    line-height: 1.4;
  }
  .message.user {
    align-self: flex-end;
    background: #0d6efd;
    color: white;
    border-bottom-right-radius: 2px;
  }
  .message.admin {
    align-self: flex-start;
    background: #e9ecef;
    color: #212529;
    border-bottom-left-radius: 2px;
  }
  .chat-timestamp {
    font-size: 0.7rem;
    opacity: 0.7;
    margin-top: 4px;
    text-align: right;
  }
</style>

<div id="chat-widget">
  <div id="chat-button" onclick="toggleChat()">
    <i class="bi bi-chat-dots-fill fs-3"></i>
  </div>
</div>

<div id="chat-window">
  <div class="chat-header">
    <h6 class="mb-0">Support Chat</h6>
    <button type="button" class="btn-close btn-close-white" onclick="toggleChat()"></button>
  </div>
  <div class="chat-body" id="chat-messages">
    <!-- Messages will appear here -->
  </div>
  <div class="chat-footer">
    <input type="text" id="chat-input" class="form-control" placeholder="Type a message..." onkeypress="handleEnter(event)">
    <button class="btn btn-primary" onclick="sendMessage()"><i class="bi bi-send"></i></button>
  </div>
</div>

<script>
let isChatOpen = false;
let lastMessageId = 0;
let chatPollInterval;

function toggleChat() {
    const window = document.getElementById('chat-window');
    isChatOpen = !isChatOpen;
    window.style.display = isChatOpen ? 'flex' : 'none';
    
    if (isChatOpen) {
        scrollToBottom();
        fetchMessages();
        chatPollInterval = setInterval(fetchMessages, 3000);
    } else {
        clearInterval(chatPollInterval);
    }
}

function handleEnter(e) {
    if (e.key === 'Enter') sendMessage();
}

function scrollToBottom() {
    const body = document.getElementById('chat-messages');
    body.scrollTop = body.scrollHeight;
}

function sendMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    if (!message) return;

    // Optimistic UI update
    appendMessage(message, 'user');
    input.value = '';
    scrollToBottom();

    const formData = new FormData();
    formData.append('action', 'send');
    formData.append('message', message);

    fetch('/ACB/ajax/chat_api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status !== 'success') {
            console.error('Error sending message:', data.message);
        }
    });
}

function fetchMessages() {
    fetch(`/ACB/ajax/chat_api.php?action=fetch&last_id=${lastMessageId}`)
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success' && data.messages.length > 0) {
            data.messages.forEach(msg => {
                // Check if we already have this message (in case of overlap or optimistic UI)
                // Actually, optimistic UI only adds to DOM, so we should be careful not to duplicate.
                // But optimistic UI is only for 'user' sender.
                // So if msg.sender is 'user', we might have added it.
                // For simplicity, we can rely on ID.
                
                if (msg.id > lastMessageId) {
                    // Only append if it's not a user message we just sent (to avoid dupes if we did optimistic)
                    // Or just clear optimistic logic and rely on poll for simplicity?
                    // Let's stick to standard poll for now, but maybe too slow.
                    // Better: Optimistic UI adds it without ID. Real fetch adds it with ID.
                    // For now, let's just append everything from server that is > lastId.
                    // Since optimistic UI doesn't update lastMessageId, we need to handle it.
                    // A simple way is to NOT use optimistic UI for simplicity, or clear chat and reload.
                    // Let's use simple append for incoming 'admin' messages, and for 'user' messages, 
                    // we assume they are ours.
                    
                    if (msg.sender === 'admin' || (msg.sender === 'user' && !document.getElementById('msg-'+msg.id))) {
                         appendMessage(msg.message, msg.sender, msg.id);
                    }
                    lastMessageId = msg.id;
                }
            });
            scrollToBottom();
        }
    });
}

function appendMessage(text, sender, id = null) {
    // If id is null, it's a temp optimistic message
    // If id is present, check if we already showed it
    
    const div = document.createElement('div');
    div.className = `message ${sender}`;
    if (id) div.id = 'msg-' + id;
    div.innerHTML = `
        ${text}
        <div class="chat-timestamp">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
    `;
    document.getElementById('chat-messages').appendChild(div);
}
</script>
<?php endif; ?>
