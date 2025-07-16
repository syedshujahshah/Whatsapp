<?php
// Simple PHP test file
echo "<h1>PHP is working!</h1>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Test database connection
try {
    $host = 'localhost';
    $db_name = 'dbedepbijkslrz';
    $username = 'ulnrcogla9a1t';
    $password = 'yolpwow1mwr2';
    
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}
?>
