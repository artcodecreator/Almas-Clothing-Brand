<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

include '../includes/header.php';
?>

<style>
.chat-container {
    height: calc(100vh - 200px);
    min-height: 500px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    overflow: hidden;
}
.user-list {
    background: #f8f9fa;
    border-right: 1px solid #dee2e6;
    height: 100%;
    overflow-y: auto;
}
.user-item {
    padding: 15px;
    cursor: pointer;
    border-bottom: 1px solid #e9ecef;
    transition: background 0.2s;
}
.user-item:hover, .user-item.active {
    background: #e2e6ea;
}
.chat-area {
    display: flex;
    flex-direction: column;
    height: 100%;
}
.messages-box {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #fff;
}
.input-area {
    padding: 20px;
    border-top: 1px solid #dee2e6;
    background: #f8f9fa;
}
.message {
    max-width: 70%;
    margin-bottom: 15px;
    padding: 10px 15px;
    border-radius: 15px;
    position: relative;
}
.message.admin {
    margin-left: auto;
    background: #0d6efd;
    color: white;
    border-bottom-right-radius: 2px;
}
.message.user {
    margin-right: auto;
    background: #e9ecef;
    color: #212529;
    border-bottom-left-radius: 2px;
}
.message-time {
    font-size: 0.75rem;
    opacity: 0.7;
    margin-top: 5px;
    text-align: right;
}
.unread-badge {
    background: #dc3545;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.75rem;
    margin-left: auto;
}
</style>

<div class="container py-5">
    <h2 class="mb-4">Live Support Chat</h2>
    
    <div class="row chat-container g-0">
        <!-- User List -->
        <div class="col-md-4 user-list" id="userList">
            <!-- Populated via JS -->
            <div class="text-center p-4 text-muted">Loading users...</div>
        </div>
        
        <!-- Chat Area -->
        <div class="col-md-8 chat-area">
            <div class="d-flex align-items-center p-3 border-bottom bg-light" id="chatHeader" style="display: none !important;">
                <h5 class="mb-0" id="currentChatUser">Select a user to start chatting</h5>
            </div>
            
            <div class="messages-box" id="messagesBox">
                <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                    <i class="bi bi-chat-square-text fs-1 me-3"></i>
                    <div>Select a conversation from the left</div>
                </div>
            </div>
            
            <div class="input-area" id="inputArea" style="display: none;">
                <div class="input-group">
                    <input type="text" id="adminMessageInput" class="form-control" placeholder="Type your reply..." onkeypress="handleAdminEnter(event)">
                    <button class="btn btn-primary" onclick="sendAdminMessage()">
                        <i class="bi bi-send"></i> Send
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let activeUserId = null;
let lastMsgId = 0;
let pollInterval;

// Load users initially and every 10 seconds
loadUsers();
setInterval(loadUsers, 10000);

function loadUsers() {
    fetch('/ACB/ajax/chat_api.php?action=list_users')
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            const list = document.getElementById('userList');
            list.innerHTML = '';
            
            if (data.users.length === 0) {
                list.innerHTML = '<div class="text-center p-4 text-muted">No active chats</div>';
                return;
            }
            
            data.users.forEach(u => {
                const isActive = activeUserId == u.id ? 'active' : '';
                const unread = u.unread_count > 0 ? `<span class="unread-badge">${u.unread_count}</span>` : '';
                const lastMsg = u.last_message ? (u.last_message.length > 30 ? u.last_message.substring(0,30)+'...' : u.last_message) : 'No messages';
                
                list.innerHTML += `
                    <div class="user-item d-flex align-items-center ${isActive}" onclick="openChat(${u.id}, '${u.name}')">
                        <div class="bg-secondary rounded-circle text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            ${u.profile_image ? `<img src="${u.profile_image}" class="rounded-circle w-100 h-100" style="object-fit:cover">` : u.name.charAt(0)}
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 text-truncate">${u.name}</h6>
                                ${unread}
                            </div>
                            <small class="text-muted text-truncate d-block">${lastMsg}</small>
                        </div>
                    </div>
                `;
            });
        }
    });
}

function openChat(userId, userName) {
    activeUserId = userId;
    lastMsgId = 0; // Reset to fetch all
    document.getElementById('currentChatUser').innerText = 'Chat with ' + userName;
    document.getElementById('chatHeader').style.display = 'flex';
    document.getElementById('inputArea').style.display = 'block';
    document.getElementById('messagesBox').innerHTML = ''; // Clear previous
    
    // Start polling for this chat
    if (pollInterval) clearInterval(pollInterval);
    fetchChatMessages();
    pollInterval = setInterval(fetchChatMessages, 3000);
    
    // Refresh user list to clear unread badge locally (will be cleared on server by fetch)
    // Actually fetchChatMessages calls fetch which clears unread on server.
    // So next loadUsers() will reflect that.
}

function fetchChatMessages() {
    if (!activeUserId) return;
    
    fetch(`/ACB/ajax/chat_api.php?action=fetch&user_id=${activeUserId}&last_id=${lastMsgId}`)
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success' && data.messages.length > 0) {
            const box = document.getElementById('messagesBox');
            data.messages.forEach(msg => {
                if (msg.id > lastMsgId) {
                    const div = document.createElement('div');
                    div.className = `message ${msg.sender}`;
                    div.innerHTML = `
                        ${msg.message}
                        <div class="message-time">${new Date(msg.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</div>
                    `;
                    box.appendChild(div);
                    lastMsgId = msg.id;
                }
            });
            box.scrollTop = box.scrollHeight;
        }
    });
}

function handleAdminEnter(e) {
    if (e.key === 'Enter') sendAdminMessage();
}

function sendAdminMessage() {
    const input = document.getElementById('adminMessageInput');
    const msg = input.value.trim();
    if (!msg || !activeUserId) return;
    
    const formData = new FormData();
    formData.append('action', 'send');
    formData.append('user_id', activeUserId);
    formData.append('message', msg);
    
    fetch('/ACB/ajax/chat_api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            input.value = '';
            fetchChatMessages(); // Immediate refresh
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
