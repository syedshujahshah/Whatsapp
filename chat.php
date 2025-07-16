<?php
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'];

// Get user contacts and chats
$contacts = $userManager->getUserContacts($user_id);
$chats = $chatManager->getUserChats($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Clone - Chat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            height: 100vh;
            overflow: hidden;
        }

        .chat-container {
            display: flex;
            height: 100vh;
            background: white;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 350px;
            background: white;
            border-right: 1px solid #e9edef;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            background: #f0f2f5;
            padding: 15px 20px;
            border-bottom: 1px solid #e9edef;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 5px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .user-info:hover {
            background: rgba(0,0,0,0.05);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #25D366, #128C7E);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .user-avatar:hover {
            transform: scale(1.05);
        }

        .user-details h3 {
            font-size: 16px;
            color: #111b21;
            margin-bottom: 2px;
        }

        .user-details p {
            font-size: 13px;
            color: #667781;
        }

        .header-actions {
            display: flex;
            gap: 5px;
        }

        .header-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #54656f;
            transition: all 0.2s;
            font-size: 18px;
            position: relative;
        }

        .header-btn:hover {
            background: #f5f6f6;
            transform: scale(1.1);
        }

        .header-btn:active {
            transform: scale(0.95);
        }

        .search-container {
            padding: 12px 16px;
            background: white;
            position: relative;
        }

        .search-box {
            width: 100%;
            padding: 8px 16px 8px 40px;
            border: none;
            background: #f0f2f5;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
        }

        .search-box:focus {
            outline: none;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        }

        .search-icon {
            position: absolute;
            left: 28px;
            top: 50%;
            transform: translateY(-50%);
            color: #667781;
            font-size: 16px;
            pointer-events: none;
        }

        .chats-list {
            flex: 1;
            overflow-y: auto;
        }

        .chat-item {
            padding: 12px 16px;
            border-bottom: 1px solid #f7f8fa;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
        }

        .chat-item:hover {
            background: #f5f6f6;
        }

        .chat-item.active {
            background: #e7f3ff;
        }

        .chat-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(45deg, #25D366, #128C7E);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
            flex-shrink: 0;
            position: relative;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .chat-avatar:hover {
            transform: scale(1.05);
        }

        .chat-info {
            flex: 1;
            min-width: 0;
        }

        .chat-name {
            font-size: 16px;
            color: #111b21;
            margin-bottom: 2px;
            font-weight: 500;
        }

        .chat-last-message {
            font-size: 14px;
            color: #667781;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-time {
            font-size: 12px;
            color: #667781;
            flex-shrink: 0;
        }

        /* Main Chat Area */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #efeae2;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4d4d4' fill-opacity='0.1'%3E%3Cpath d='m36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .chat-header {
            background: #f0f2f5;
            padding: 15px 20px;
            border-bottom: 1px solid #e9edef;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chat-header-info {
            flex: 1;
            cursor: pointer;
            padding: 5px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .chat-header-info:hover {
            background: rgba(0,0,0,0.05);
        }

        .chat-header-name {
            font-size: 16px;
            color: #111b21;
            margin-bottom: 2px;
        }

        .chat-header-status {
            font-size: 13px;
            color: #667781;
        }

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .message {
            max-width: 65%;
            padding: 8px 12px;
            border-radius: 8px;
            position: relative;
            word-wrap: break-word;
            animation: messageSlide 0.3s ease;
        }

        @keyframes messageSlide {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.sent {
            align-self: flex-end;
            background: #d9fdd3;
            margin-left: auto;
        }

        .message.received {
            align-self: flex-start;
            background: white;
            margin-right: auto;
        }

        .message-text {
            font-size: 14px;
            line-height: 1.4;
            color: #111b21;
            margin-bottom: 4px;
        }

        .message-time {
            font-size: 11px;
            color: #667781;
            text-align: right;
        }

        .message-input-container {
            background: #f0f2f5;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message-input {
            flex: 1;
            padding: 10px 16px;
            border: none;
            border-radius: 20px;
            font-size: 15px;
            background: white;
            resize: none;
            max-height: 100px;
            transition: all 0.2s;
        }

        .message-input:focus {
            outline: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        }

        .send-btn {
            width: 45px;
            height: 45px;
            border: none;
            background: #25D366;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.2s;
        }

        .send-btn:hover {
            background: #128C7E;
            transform: scale(1.05);
        }

        .send-btn:active {
            transform: scale(0.95);
        }

        .send-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .welcome-screen {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            color: #667781;
        }

        .welcome-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(45deg, #25D366, #128C7E);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: white;
            margin-bottom: 30px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .welcome-title {
            font-size: 32px;
            color: #41525d;
            margin-bottom: 15px;
        }

        .welcome-text {
            font-size: 16px;
            line-height: 1.5;
            max-width: 400px;
        }

        .no-chats {
            text-align: center;
            padding: 40px 20px;
            color: #667781;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        /* Dropdown Menus */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            border-radius: 8px;
            z-index: 1000;
            padding: 8px 0;
        }

        .dropdown-content.show {
            display: block;
            animation: dropdownSlide 0.2s ease;
        }

        @keyframes dropdownSlide {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dropdown-item {
            padding: 12px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: background 0.2s;
            color: #333;
            text-decoration: none;
        }

        .dropdown-item:hover {
            background: #f5f6f6;
        }

        .dropdown-item.danger {
            color: #dc3545;
        }

        .dropdown-item.danger:hover {
            background: #ffebee;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: modalFade 0.3s ease;
        }

        @keyframes modalFade {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            position: relative;
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-close {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: #f5f6f6;
            color: #333;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: absolute;
                z-index: 1000;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .chat-main {
                width: 100%;
            }

            .mobile-back-btn {
                display: flex;
            }
        }

        /* Tooltip */
        .tooltip {
            position: relative;
        }

        .tooltip::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: white;
            padding: 5px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .tooltip:hover::after {
            opacity: 1;
        }

        /* File upload styles */
        .file-input {
            display: none;
        }

        .emoji-picker {
            display: none;
            position: absolute;
            bottom: 60px;
            right: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            padding: 15px;
            width: 300px;
            max-height: 200px;
            overflow-y: auto;
        }

        .emoji-picker.show {
            display: block;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .emoji-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 5px;
        }

        .emoji-item {
            padding: 8px;
            text-align: center;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.2s;
            font-size: 18px;
        }

        .emoji-item:hover {
            background: #f5f6f6;
        }

        .debug-info {
            position: fixed;
            bottom: 10px;
            left: 10px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            z-index: 9999;
            max-width: 300px;
            display: none;
        }

        .debug-info.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="debug-info" id="debugInfo">
        Debug: Ready
    </div>

    <div class="chat-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="user-info" onclick="showUserProfile()">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($full_name, 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <h3><?php echo htmlspecialchars($full_name); ?></h3>
                        <p>@<?php echo htmlspecialchars($username); ?></p>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="header-btn" onclick="showNewChatModal()">
                        <span>💬</span>
                    </button>
                    <div class="dropdown">
                        <button class="header-btn" onclick="toggleDropdown('headerDropdown')">
                            <span>⋮</span>
                        </button>
                        <div class="dropdown-content" id="headerDropdown">
                            <a href="#" class="dropdown-item" onclick="showNewChatModal()">
                                <span>💬</span> New Chat
                            </a>
                            <a href="#" class="dropdown-item" onclick="showProfile()">
                                <span>👤</span> Profile
                            </a>
                            <a href="#" class="dropdown-item danger" onclick="logout()">
                                <span>🚪</span> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="search-container">
                <span class="search-icon">🔍</span>
                <input type="text" class="search-box" placeholder="Search chats..." id="searchBox">
            </div>

            <div class="chats-list" id="chatsList">
                <?php if (empty($chats)): ?>
                    <div class="no-chats">
                        <p>No chats yet. Start a conversation!</p>
                        <button class="header-btn" onclick="showNewChatModal()" style="margin-top: 15px; background: #25D366; color: white;">
                            <span>💬</span>
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($chats as $chat): ?>
                        <div class="chat-item" onclick="openChat(<?php echo $chat['id']; ?>, '<?php echo htmlspecialchars($chat['chat_name']); ?>')">
                            <div class="chat-avatar">
                                <?php echo strtoupper(substr($chat['chat_name'], 0, 1)); ?>
                            </div>
                            <div class="chat-info">
                                <div class="chat-name"><?php echo htmlspecialchars($chat['chat_name']); ?></div>
                                <div class="chat-last-message">
                                    <?php echo $chat['last_message'] ? htmlspecialchars(substr($chat['last_message'], 0, 50)) . '...' : 'No messages yet'; ?>
                                </div>
                            </div>
                            <div class="chat-time">
                                <?php 
                                if ($chat['last_message_time']) {
                                    echo date('H:i', strtotime($chat['last_message_time']));
                                }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-main" id="chatMain">
            <div class="welcome-screen" id="welcomeScreen">
                <div class="welcome-icon">💬</div>
                <h2 class="welcome-title">WhatsApp Clone</h2>
                <p class="welcome-text">
                    Send and receive messages without keeping your phone online.<br>
                    Use WhatsApp Clone on up to 4 linked devices and 1 phone at the same time.
                </p>
            </div>

            <div class="chat-area" id="chatArea" style="display: none;">
                <div class="chat-header">
                    <div class="chat-avatar" id="chatAvatar">A</div>
                    <div class="chat-header-info">
                        <div class="chat-header-name" id="chatHeaderName">Select a chat</div>
                        <div class="chat-header-status" id="chatHeaderStatus">Online</div>
                    </div>
                    <div class="header-actions">
                        <div class="dropdown">
                            <button class="header-btn" onclick="toggleDropdown('chatDropdown')">
                                <span>⋮</span>
                            </button>
                            <div class="dropdown-content" id="chatDropdown">
                                <a href="#" class="dropdown-item" onclick="clearChat()">
                                    <span>🗑️</span> Clear Chat
                                </a>
                                <a href="#" class="dropdown-item danger" onclick="deleteChat()">
                                    <span>❌</span> Delete Chat
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="messages-container" id="messagesContainer">
                    <!-- Messages will be loaded here -->
                </div>

                <div class="message-input-container">
                    <textarea class="message-input" id="messageInput" placeholder="Type a message..." rows="1"></textarea>
                    <button class="send-btn" id="sendBtn" onclick="sendMessage()">
                        <span>➤</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Chat Modal -->
    <div class="modal" id="newChatModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('newChatModal')">&times;</span>
            <h2>Start New Chat</h2>
            <div style="margin: 20px 0;">
                <input type="text" placeholder="Search users..." id="userSearch" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            </div>
            <div id="usersList" style="max-height: 300px; overflow-y: auto;">
                <!-- Users will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div class="modal" id="profileModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('profileModal')">&times;</span>
            <h2>Profile</h2>
            <div style="text-align: center; margin: 20px 0;">
                <div class="user-avatar" style="width: 80px; height: 80px; font-size: 32px; margin: 0 auto 15px;">
                    <?php echo strtoupper(substr($full_name, 0, 1)); ?>
                </div>
                <h3><?php echo htmlspecialchars($full_name); ?></h3>
                <p style="color: #666;">@<?php echo htmlspecialchars($username); ?></p>
            </div>
        </div>
    </div>

    <script>
        let currentChatId = null;
        let currentUserId = <?php echo $user_id; ?>;
        let messagePolling = null;
        let debugMode = true; // Enable debug mode

        function debugLog(message) {
            if (debugMode) {
                console.log('DEBUG:', message);
                const debugInfo = document.getElementById('debugInfo');
                debugInfo.textContent = 'Debug: ' + message;
                debugInfo.classList.add('show');
                setTimeout(() => {
                    debugInfo.classList.remove('show');
                }, 3000);
            }
        }

        // Initialize app
        document.addEventListener('DOMContentLoaded', function() {
            debugLog('App initialized');
            setupEventListeners();
        });

        // Auto-resize textarea
        document.getElementById('messageInput').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });

        // Send message on Enter key
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        function setupEventListeners() {
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                        dropdown.classList.remove('show');
                    });
                }
            });

            // Close modals when clicking outside
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });
        }

        function toggleDropdown(dropdownId) {
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                if (dropdown.id !== dropdownId) {
                    dropdown.classList.remove('show');
                }
            });
            
            const dropdown = document.getElementById(dropdownId);
            dropdown.classList.toggle('show');
        }

        function showNewChatModal() {
            document.getElementById('newChatModal').style.display = 'block';
            loadUsers();
        }

        function showUserProfile() {
            document.getElementById('profileModal').style.display = 'block';
        }

        function showProfile() {
            showUserProfile();
            toggleDropdown('headerDropdown');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function clearChat() {
            if (confirm('Are you sure you want to clear this chat?')) {
                // Implement clear chat functionality
                alert('Clear chat feature coming soon!');
            }
            toggleDropdown('chatDropdown');
        }

        function deleteChat() {
            if (confirm('Are you sure you want to delete this chat?')) {
                // Implement delete chat functionality
                alert('Delete chat feature coming soon!');
            }
            toggleDropdown('chatDropdown');
        }

        function openChat(chatId, chatName) {
            debugLog(`Opening chat: ${chatId} - ${chatName}`);
            currentChatId = chatId;
            
            // Update UI
            document.getElementById('welcomeScreen').style.display = 'none';
            document.getElementById('chatArea').style.display = 'flex';
            document.getElementById('chatHeaderName').textContent = chatName;
            document.getElementById('chatAvatar').textContent = chatName.charAt(0).toUpperCase();
            
            // Mark chat as active
            document.querySelectorAll('.chat-item').forEach(item => item.classList.remove('active'));
            event.currentTarget.classList.add('active');
            
            // Load messages immediately
            loadMessages();
            
            // Start polling for new messages
            if (messagePolling) clearInterval(messagePolling);
            messagePolling = setInterval(loadMessages, 3000);
            
            debugLog(`Chat ${chatId} opened successfully`);
        }

        function loadMessages() {
            if (!currentChatId) {
                debugLog('No chat selected');
                return;
            }
            
            debugLog(`Loading messages for chat ${currentChatId}`);
            
            fetch('ajax_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_messages&chat_id=${currentChatId}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                debugLog(`Messages loaded: ${data.success ? 'success' : 'failed'}`);
                if (data.success) {
                    displayMessages(data.messages);
                } else {
                    console.error('Failed to load messages:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading messages:', error);
                debugLog('Error loading messages: ' + error.message);
            });
        }

        function displayMessages(messages) {
            const container = document.getElementById('messagesContainer');
            const scrollAtBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 10;
            
            container.innerHTML = '';
            
            if (messages && messages.length > 0) {
                messages.forEach(message => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `message ${message.sender_id == currentUserId ? 'sent' : 'received'}`;
                    
                    messageDiv.innerHTML = `
                        <div class="message-text">${escapeHtml(message.message_text)}</div>
                        <div class="message-time">${formatTime(message.created_at)}</div>
                    `;
                    
                    container.appendChild(messageDiv);
                });
                
                debugLog(`Displayed ${messages.length} messages`);
            } else {
                container.innerHTML = '<div style="text-align: center; color: #667781; padding: 20px;">No messages yet. Start the conversation!</div>';
                debugLog('No messages to display');
            }
            
            if (scrollAtBottom) {
                container.scrollTop = container.scrollHeight;
            }
        }

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message) {
                debugLog('Empty message, not sending');
                return;
            }
            
            if (!currentChatId) {
                debugLog('No chat selected');
                alert('Please select a chat first');
                return;
            }
            
            debugLog(`Sending message: "${message}" to chat ${currentChatId}`);
            
            const sendBtn = document.getElementById('sendBtn');
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span>⏳</span>';
            
            fetch('ajax_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_message&chat_id=${currentChatId}&message=${encodeURIComponent(message)}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                debugLog(`Message send result: ${data.success ? 'success' : 'failed'}`);
                if (data.success) {
                    input.value = '';
                    input.style.height = 'auto';
                    
                    // Add message to display immediately
                    if (data.message) {
                        addMessageToDisplay(data.message);
                    } else {
                        // Reload all messages if no message data returned
                        loadMessages();
                    }
                    
                    debugLog('Message sent successfully');
                } else {
                    console.error('Failed to send message:', data.message);
                    alert('Failed to send message: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                debugLog('Error sending message: ' + error.message);
                alert('Failed to send message. Please try again.');
            })
            .finally(() => {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<span>➤</span>';
            });
        }

        function addMessageToDisplay(message) {
            const container = document.getElementById('messagesContainer');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${message.sender_id == currentUserId ? 'sent' : 'received'}`;
            
            messageDiv.innerHTML = `
                <div class="message-text">${escapeHtml(message.message_text)}</div>
                <div class="message-time">${formatTime(message.created_at)}</div>
            `;
            
            container.appendChild(messageDiv);
            container.scrollTop = container.scrollHeight;
            
            debugLog('Message added to display');
        }

        function loadUsers() {
            fetch('ajax_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_contacts'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayUsers(data.contacts);
                }
            })
            .catch(error => console.error('Error loading users:', error));
        }

        function displayUsers(users) {
            const usersList = document.getElementById('usersList');
            usersList.innerHTML = '';
            
            users.forEach(user => {
                const userDiv = document.createElement('div');
                userDiv.className = 'chat-item';
                userDiv.onclick = () => startChatWithUser(user.id, user.full_name);
                
                userDiv.innerHTML = `
                    <div class="chat-avatar">
                        ${user.full_name.charAt(0).toUpperCase()}
                    </div>
                    <div class="chat-info">
                        <div class="chat-name">${escapeHtml(user.full_name)}</div>
                        <div class="chat-last-message">@${escapeHtml(user.username)}</div>
                    </div>
                `;
                
                usersList.appendChild(userDiv);
            });
        }

        function startChatWithUser(userId, userName) {
            fetch('ajax_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=create_chat&contact_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('newChatModal');
                    // Refresh the page to show new chat
                    location.reload();
                } else {
                    alert('Failed to create chat');
                }
            })
            .catch(error => {
                console.error('Error creating chat:', error);
                alert('Failed to create chat');
            });
        }

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: false 
            });
        }

        // Update user online status
        setInterval(() => {
            fetch('ajax_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=update_status'
            });
        }, 30000); // Update every 30 seconds

        // Debug: Show current chat ID
        setInterval(() => {
            if (debugMode && currentChatId) {
                console.log('Current Chat ID:', currentChatId);
            }
        }, 10000);
    </script>
</body>
</html>
