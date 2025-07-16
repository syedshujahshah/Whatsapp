<?php
// Database configuration for WhatsApp Clone
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Database {
    private $host = 'localhost';
    private $db_name = 'dbedepbijkslrz';
    private $username = 'ulnrcogla9a1t';
    private $password = 'yolpwow1mwr2';
    private $conn;
    
    // Get database connection
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );
        } catch(PDOException $exception) {
            die("Connection error: " . $exception->getMessage());
        }
        
        return $this->conn;
    }
    
    // Close database connection
    public function closeConnection() {
        $this->conn = null;
    }
}

// User authentication and management class
class UserManager {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Register new user
    public function register($username, $email, $password, $full_name) {
        try {
            $query = "INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            if ($stmt->execute([$username, $email, $hashed_password, $full_name])) {
                return ['success' => true, 'message' => 'User registered successfully'];
            }
        } catch(PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
        return ['success' => false, 'message' => 'Registration failed'];
    }
    
    // Login user
    public function login($username, $password) {
        try {
            $query = "SELECT id, username, email, password, full_name, profile_picture, status FROM users WHERE username = ? OR email = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$username, $username]);
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                if (password_verify($password, $user['password'])) {
                    // Update user online status
                    $this->updateOnlineStatus($user['id'], true);
                    return ['success' => true, 'user' => $user];
                }
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
        return ['success' => false, 'message' => 'Invalid credentials'];
    }
    
    // Update user online status
    public function updateOnlineStatus($user_id, $is_online) {
        try {
            $query = "UPDATE users SET is_online = ?, last_seen = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$is_online, $user_id]);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Get user contacts
    public function getUserContacts($user_id) {
        try {
            $query = "SELECT u.id, u.username, u.full_name, u.profile_picture, u.status, u.is_online, u.last_seen 
                     FROM users u 
                     WHERE u.id != ? 
                     ORDER BY u.is_online DESC, u.last_seen DESC
                     LIMIT 20";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }
}

// Chat management class
class ChatManager {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get or create private chat between two users
    public function getOrCreatePrivateChat($user1_id, $user2_id) {
        try {
            // Check if chat already exists
            $query = "SELECT c.id FROM chats c 
                     INNER JOIN chat_participants cp1 ON c.id = cp1.chat_id 
                     INNER JOIN chat_participants cp2 ON c.id = cp2.chat_id 
                     WHERE c.chat_type = 'private' 
                     AND cp1.user_id = ? AND cp2.user_id = ? 
                     AND cp1.user_id != cp2.user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user1_id, $user2_id]);
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch()['id'];
            }
            
            // Create new chat
            $this->conn->beginTransaction();
            
            $query = "INSERT INTO chats (chat_type, created_by) VALUES ('private', ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user1_id]);
            $chat_id = $this->conn->lastInsertId();
            
            // Add participants
            $query = "INSERT INTO chat_participants (chat_id, user_id) VALUES (?, ?), (?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$chat_id, $user1_id, $chat_id, $user2_id]);
            
            $this->conn->commit();
            return $chat_id;
            
        } catch(PDOException $e) {
            $this->conn->rollback();
            return false;
        }
    }
    
    // Send message
    public function sendMessage($chat_id, $sender_id, $message_text) {
        try {
            $query = "INSERT INTO messages (chat_id, sender_id, message_text) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            
            if ($stmt->execute([$chat_id, $sender_id, $message_text])) {
                return $this->conn->lastInsertId();
            }
        } catch(PDOException $e) {
            return false;
        }
        return false;
    }
    
    // Get chat messages
    public function getChatMessages($chat_id, $limit = 50) {
        try {
            $query = "SELECT m.id, m.sender_id, m.message_text, m.created_at, u.username, u.full_name, u.profile_picture 
                     FROM messages m 
                     INNER JOIN users u ON m.sender_id = u.id 
                     WHERE m.chat_id = ? 
                     ORDER BY m.created_at DESC 
                     LIMIT ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$chat_id, $limit]);
            return array_reverse($stmt->fetchAll());
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // Get user chats
    public function getUserChats($user_id) {
        try {
            $query = "SELECT DISTINCT c.id, c.chat_type, c.created_at,
                     CASE 
                         WHEN c.chat_type = 'private' THEN 
                             (SELECT u.full_name FROM users u 
                              INNER JOIN chat_participants cp ON u.id = cp.user_id 
                              WHERE cp.chat_id = c.id AND cp.user_id != ? LIMIT 1)
                         ELSE c.chat_name 
                     END as chat_name,
                     CASE 
                         WHEN c.chat_type = 'private' THEN 
                             (SELECT u.profile_picture FROM users u 
                              INNER JOIN chat_participants cp ON u.id = cp.user_id 
                              WHERE cp.chat_id = c.id AND cp.user_id != ? LIMIT 1)
                         ELSE 'group-avatar.png' 
                     END as chat_avatar,
                     (SELECT m.message_text FROM messages m WHERE m.chat_id = c.id ORDER BY m.created_at DESC LIMIT 1) as last_message,
                     (SELECT m.created_at FROM messages m WHERE m.chat_id = c.id ORDER BY m.created_at DESC LIMIT 1) as last_message_time
                     FROM chats c 
                     INNER JOIN chat_participants cp ON c.id = cp.chat_id 
                     WHERE cp.user_id = ? 
                     ORDER BY last_message_time DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user_id, $user_id, $user_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Create database connection
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Initialize managers
    $userManager = new UserManager($db);
    $chatManager = new ChatManager($db);
} catch(Exception $e) {
    die("Database initialization failed: " . $e->getMessage());
}
?>
