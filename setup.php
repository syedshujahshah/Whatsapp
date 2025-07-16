<?php
// Setup file to create database tables automatically
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>WhatsApp Clone Setup</h1>";

try {
    // Database connection
    $host = 'localhost';
    $db_name = 'dbedepbijkslrz';
    $username = 'ulnrcogla9a1t';
    $password = 'yolpwow1mwr2';
    
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Create tables
    $sql = "
    -- Users table
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        profile_picture VARCHAR(255) DEFAULT 'default-avatar.png',
        status VARCHAR(255) DEFAULT 'Hey there! I am using WhatsApp Clone',
        last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_online BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- Chats table
    CREATE TABLE IF NOT EXISTS chats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chat_name VARCHAR(100),
        chat_type ENUM('private', 'group') DEFAULT 'private',
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
    );

    -- Chat participants table
    CREATE TABLE IF NOT EXISTS chat_participants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chat_id INT NOT NULL,
        user_id INT NOT NULL,
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        role ENUM('admin', 'member') DEFAULT 'member',
        FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_chat_user (chat_id, user_id)
    );

    -- Messages table
    CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chat_id INT NOT NULL,
        sender_id INT NOT NULL,
        message_text TEXT NOT NULL,
        message_type ENUM('text', 'image', 'file') DEFAULT 'text',
        file_path VARCHAR(255) NULL,
        is_edited BOOLEAN DEFAULT FALSE,
        edited_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
    );

    -- Message status table
    CREATE TABLE IF NOT EXISTS message_status (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message_id INT NOT NULL,
        user_id INT NOT NULL,
        status ENUM('sent', 'delivered', 'read') DEFAULT 'sent',
        status_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_message_user_status (message_id, user_id)
    );
    ";
    
    // Execute SQL
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Database tables created successfully!</p>";
    
    // Insert sample users
    $sample_users = [
        ['john_doe', 'john@example.com', 'password', 'John Doe'],
        ['jane_smith', 'jane@example.com', 'password', 'Jane Smith'],
        ['mike_wilson', 'mike@example.com', 'password', 'Mike Wilson']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
    
    foreach ($sample_users as $user) {
        $hashed_password = password_hash($user[2], PASSWORD_DEFAULT);
        $stmt->execute([$user[0], $user[1], $hashed_password, $user[3]]);
    }
    
    echo "<p style='color: green;'>✅ Sample users created!</p>";
    echo "<p><strong>Test Accounts:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <strong>john_doe</strong>, Password: <strong>password</strong></li>";
    echo "<li>Username: <strong>jane_smith</strong>, Password: <strong>password</strong></li>";
    echo "<li>Username: <strong>mike_wilson</strong>, Password: <strong>password</strong></li>";
    echo "</ul>";
    
    echo "<p style='color: blue; margin-top: 30px;'>";
    echo "🎉 Setup completed successfully! <br>";
    echo "<a href='index.php' style='color: #25D366; text-decoration: none; font-weight: bold;'>→ Go to WhatsApp Clone</a>";
    echo "</p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Setup failed: " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: #f5f5f5;
}

h1 {
    color: #25D366;
    text-align: center;
    margin-bottom: 30px;
}

p, li {
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 10px;
}

ul {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
</style>
