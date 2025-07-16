<?php
// Server diagnostic file - upload this first to check everything
echo "<h1>🔍 Server Diagnostic Tool</h1>";
echo "<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
.success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
.error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
.warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
.info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>";

// 1. Check PHP
echo "<h2>1. ✅ PHP Status</h2>";
echo "<div class='success'>PHP is working! Version: " . phpversion() . "</div>";

// 2. Check current directory and files
echo "<h2>2. 📁 File System Check</h2>";
echo "<div class='info'><strong>Current Directory:</strong> " . getcwd() . "</div>";
echo "<div class='info'><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</div>";

// List all files in current directory
$files = scandir('.');
echo "<h3>Files in current directory:</h3>";
echo "<table>";
echo "<tr><th>File Name</th><th>Type</th><th>Size</th><th>Permissions</th></tr>";

$required_files = ['index.php', 'login.php', 'signin.php', 'chat.php', 'db.php', 'config.php'];
$found_files = [];

foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $is_required = in_array($file, $required_files);
        $color = $is_required ? 'green' : 'black';
        
        if ($is_required) {
            $found_files[] = $file;
        }
        
        echo "<tr style='color: $color;'>";
        echo "<td>" . $file . ($is_required ? ' ⭐' : '') . "</td>";
        echo "<td>" . (is_dir($file) ? 'Directory' : 'File') . "</td>";
        echo "<td>" . (is_file($file) ? filesize($file) . ' bytes' : '-') . "</td>";
        echo "<td>" . substr(sprintf('%o', fileperms($file)), -4) . "</td>";
        echo "</tr>";
    }
}
echo "</table>";

// Check missing files
$missing_files = array_diff($required_files, $found_files);
if (!empty($missing_files)) {
    echo "<div class='error'><strong>❌ Missing Required Files:</strong><br>";
    foreach ($missing_files as $missing) {
        echo "• $missing<br>";
    }
    echo "</div>";
} else {
    echo "<div class='success'>✅ All required files found!</div>";
}

// 3. Check URL and access
echo "<h2>3. 🌐 URL and Access Check</h2>";
echo "<div class='info'><strong>Current URL:</strong> " . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</div>";
echo "<div class='info'><strong>Server Name:</strong> " . $_SERVER['SERVER_NAME'] . "</div>";
echo "<div class='info'><strong>Request Method:</strong> " . $_SERVER['REQUEST_METHOD'] . "</div>";

// 4. Test database connection
echo "<h2>4. 🗄️ Database Connection Test</h2>";
try {
    $host = 'localhost';
    $db_name = 'dbedepbijkslrz';
    $username = 'ulnrcogla9a1t';
    $password = 'yolpwow1mwr2';
    
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    echo "<div class='success'>✅ Database connection successful!</div>";
    
    // Check if tables exist
    $tables = ['users', 'chats', 'messages', 'chat_participants'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✅ Table '$table' exists</div>";
        } else {
            echo "<div class='error'>❌ Table '$table' missing</div>";
        }
    }
    
} catch(PDOException $e) {
    echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
}

// 5. Check server configuration
echo "<h2>5. ⚙️ Server Configuration</h2>";
echo "<table>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>Server Software</td><td>" . $_SERVER['SERVER_SOFTWARE'] . "</td></tr>";
echo "<tr><td>PHP SAPI</td><td>" . php_sapi_name() . "</td></tr>";
echo "<tr><td>Memory Limit</td><td>" . ini_get('memory_limit') . "</td></tr>";
echo "<tr><td>Upload Max Size</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
echo "<tr><td>Post Max Size</td><td>" . ini_get('post_max_size') . "</td></tr>";
echo "<tr><td>Max Execution Time</td><td>" . ini_get('max_execution_time') . " seconds</td></tr>";
echo "</table>";

// 6. Quick links to test other files
echo "<h2>6. 🔗 Quick File Tests</h2>";
$test_files = [
    'index.php' => 'Main Welcome Page',
    'login.php' => 'Login Page', 
    'signin.php' => 'Registration Page',
    'chat.php' => 'Chat Interface',
    'setup.php' => 'Database Setup'
];

foreach ($test_files as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='success'>";
        echo "✅ <a href='$file' target='_blank'>$description ($file)</a>";
        echo "</div>";
    } else {
        echo "<div class='error'>❌ $description ($file) - File not found</div>";
    }
}

// 7. .htaccess check
echo "<h2>7. 📄 .htaccess Status</h2>";
if (file_exists('.htaccess')) {
    echo "<div class='success'>✅ .htaccess file exists</div>";
    echo "<div class='info'><strong>Content:</strong><br><pre>" . htmlspecialchars(file_get_contents('.htaccess')) . "</pre></div>";
} else {
    echo "<div class='warning'>⚠️ .htaccess file not found (this might be okay)</div>";
}

// 8. Session test
echo "<h2>8. 🔐 Session Test</h2>";
session_start();
if (session_status() == PHP_SESSION_ACTIVE) {
    echo "<div class='success'>✅ Sessions are working</div>";
    echo "<div class='info'>Session ID: " . session_id() . "</div>";
} else {
    echo "<div class='error'>❌ Sessions not working</div>";
}

// 9. Recommendations
echo "<h2>9. 💡 Recommendations</h2>";

if (!empty($missing_files)) {
    echo "<div class='error'><strong>Action Required:</strong> Upload missing files: " . implode(', ', $missing_files) . "</div>";
}

if (!file_exists('setup.php')) {
    echo "<div class='warning'><strong>Suggestion:</strong> Run setup.php to create database tables</div>";
}

echo "<div class='info'><strong>Next Steps:</strong><br>";
echo "1. If files are missing, upload them<br>";
echo "2. Run setup.php to create database<br>";
echo "3. Try accessing index.php<br>";
echo "4. Check file permissions if still having issues</div>";

echo "<hr>";
echo "<p><strong>🕒 Generated:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
