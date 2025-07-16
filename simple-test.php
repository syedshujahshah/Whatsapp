<?php
// Simple test file to verify PHP is working
echo "🎉 PHP is working!<br>";
echo "📅 Current time: " . date('Y-m-d H:i:s') . "<br>";
echo "🖥️ Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "🔗 Your URL: " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "<br>";

// Test if other files exist
$files = ['index.php', 'login.php', 'signin.php', 'chat.php', 'db.php'];
echo "<br><h3>📁 File Check:</h3>";
foreach($files as $file) {
    if(file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file missing<br>";
    }
}

echo "<br><h3>🔗 Quick Links:</h3>";
echo "<a href='index.php'>Home Page</a> | ";
echo "<a href='login.php'>Login</a> | ";
echo "<a href='signin.php'>Sign Up</a> | ";
echo "<a href='setup.php'>Setup Database</a>";
?>
