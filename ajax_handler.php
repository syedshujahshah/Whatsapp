<?php
require_once 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

header('Content-Type: application/json');

try {
    switch ($action) {
        case 'get_messages':
            $chat_id = intval($_POST['chat_id']);
            
            if ($chat_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid chat ID']);
                break;
            }
            
            // Verify user has access to this chat
            $access_query = "SELECT COUNT(*) FROM chat_participants WHERE chat_id = ? AND user_id = ?";
            $access_stmt = $db->prepare($access_query);
            $access_stmt->execute([$chat_id, $user_id]);
            
            if ($access_stmt->fetchColumn() == 0) {
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                break;
            }
            
            $messages = $chatManager->getChatMessages($chat_id);
            echo json_encode(['success' => true, 'messages' => $messages]);
            break;
            
        case 'send_message':
            $chat_id = intval($_POST['chat_id']);
            $message = trim($_POST['message']);
            
            // Debug logging
            error_log("Send message - Chat ID: $chat_id, User ID: $user_id, Message: $message");
            
            if ($chat_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid chat ID']);
                break;
            }
            
            if (empty($message)) {
                echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
                break;
            }
            
            // Verify user has access to this chat
            $access_query = "SELECT COUNT(*) FROM chat_participants WHERE chat_id = ? AND user_id = ?";
            $access_stmt = $db->prepare($access_query);
            $access_stmt->execute([$chat_id, $user_id]);
            
            if ($access_stmt->fetchColumn() == 0) {
                echo json_encode(['success' => false, 'message' => 'Access denied to this chat']);
                break;
            }
            
            // Insert message directly here for better error handling
            try {
                $insert_query = "INSERT INTO messages (chat_id, sender_id, message_text, created_at) VALUES (?, ?, ?, NOW())";
                $insert_stmt = $db->prepare($insert_query);
                $result = $insert_stmt->execute([$chat_id, $user_id, $message]);
                
                if ($result) {
                    $message_id = $db->lastInsertId();
                    
                    // Get the inserted message with user details
                    $msg_query = "SELECT m.id, m.sender_id, m.message_text, m.created_at, u.username, u.full_name 
                                 FROM messages m 
                                 INNER JOIN users u ON m.sender_id = u.id 
                                 WHERE m.id = ?";
                    $msg_stmt = $db->prepare($msg_query);
                    $msg_stmt->execute([$message_id]);
                    $new_message = $msg_stmt->fetch();
                    
                    echo json_encode([
                        'success' => true, 
                        'message_id' => $message_id,
                        'message' => $new_message
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to insert message']);
                }
            } catch(PDOException $e) {
                error_log("Database error in send_message: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            break;
            
        case 'create_chat':
            $contact_id = intval($_POST['contact_id']);
            
            if ($contact_id <= 0 || $contact_id == $user_id) {
                echo json_encode(['success' => false, 'message' => 'Invalid contact ID']);
                break;
            }
            
            // Check if contact exists
            $contact_query = "SELECT id, full_name FROM users WHERE id = ?";
            $contact_stmt = $db->prepare($contact_query);
            $contact_stmt->execute([$contact_id]);
            $contact = $contact_stmt->fetch();
            
            if (!$contact) {
                echo json_encode(['success' => false, 'message' => 'Contact not found']);
                break;
            }
            
            $chat_id = $chatManager->getOrCreatePrivateChat($user_id, $contact_id);
            
            if ($chat_id) {
                echo json_encode([
                    'success' => true, 
                    'chat_id' => $chat_id,
                    'contact_name' => $contact['full_name']
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create chat']);
            }
            break;
            
        case 'get_contacts':
            $contacts = $userManager->getUserContacts($user_id);
            echo json_encode(['success' => true, 'contacts' => $contacts]);
            break;
            
        case 'update_status':
            $result = $userManager->updateOnlineStatus($user_id, true);
            echo json_encode(['success' => $result]);
            break;
            
        case 'search_users':
            $search_term = trim($_POST['search_term']);
            if (!empty($search_term)) {
                try {
                    $query = "SELECT id, username, full_name, profile_picture, status 
                             FROM users 
                             WHERE (username LIKE ? OR full_name LIKE ?) 
                             AND id != ? 
                             LIMIT 20";
                    $stmt = $db->prepare($query);
                    $search_param = "%{$search_term}%";
                    $stmt->execute([$search_param, $search_param, $user_id]);
                    $users = $stmt->fetchAll();
                    
                    echo json_encode(['success' => true, 'users' => $users]);
                } catch(PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Search failed']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Search term required']);
            }
            break;
            
        case 'get_user_chats':
            $chats = $chatManager->getUserChats($user_id);
            echo json_encode(['success' => true, 'chats' => $chats]);
            break;
            
        case 'update_profile':
            $status = trim($_POST['status']);
            try {
                $query = "UPDATE users SET status = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $result = $stmt->execute([$status, $user_id]);
                
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
                }
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
            break;
            
        case 'clear_chat':
            $chat_id = intval($_POST['chat_id']);
            try {
                $query = "DELETE FROM messages WHERE chat_id = ?";
                $stmt = $db->prepare($query);
                $result = $stmt->execute([$chat_id]);
                
                echo json_encode(['success' => $result, 'message' => $result ? 'Chat cleared' : 'Failed to clear chat']);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
            break;
            
        case 'delete_chat':
            $chat_id = intval($_POST['chat_id']);
            try {
                // Check if user is participant in this chat
                $query = "SELECT COUNT(*) FROM chat_participants WHERE chat_id = ? AND user_id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$chat_id, $user_id]);
                
                if ($stmt->fetchColumn() > 0) {
                    // Remove user from chat participants
                    $query = "DELETE FROM chat_participants WHERE chat_id = ? AND user_id = ?";
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute([$chat_id, $user_id]);
                    
                    echo json_encode(['success' => $result, 'message' => $result ? 'Chat deleted' : 'Failed to delete chat']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Not authorized']);
                }
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch(Exception $e) {
    error_log("AJAX Handler Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
