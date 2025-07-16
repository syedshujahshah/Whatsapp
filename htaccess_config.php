<?php
// PHP equivalent of .htaccess configurations
// This file handles server configurations that would normally be in .htaccess

// Enable error display for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Set cache headers for static content
$file_extension = pathinfo($_SERVER['REQUEST_URI'], PATHINFO_EXTENSION);
$static_extensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'woff', 'woff2'];

if (in_array(strtolower($file_extension), $static_extensions)) {
    // Cache static files for 1 month
    header('Cache-Control: public, max-age=2592000');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 2592000) . ' GMT');
}

// Simple URL routing function
function handleRouting() {
    $request_uri = $_SERVER['REQUEST_URI'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    
    // Remove query string
    $request_uri = strtok($request_uri, '?');
    
    // Remove script name from request URI to get the path
    $path = str_replace(dirname($script_name), '', $request_uri);
    $path = trim($path, '/');
    
    // Default routes
    $routes = [
        '' => 'index.php',
        'login' => 'index.php',
        'chat' => 'chat.php',
        'logout' => 'logout.php',
        'ajax' => 'ajax_handler.php',
        'test' => 'test.php',
        'setup' => 'setup.php'
    ];
    
    // Check if route exists
    if (array_key_exists($path, $routes)) {
        $file = $routes[$path];
        if (file_exists($file)) {
            include $file;
            return true;
        }
    }
    
    // Check if file exists directly
    if (file_exists($path . '.php')) {
        include $path . '.php';
        return true;
    }
    
    return false;
}

// Function to check server requirements
function checkServerRequirements() {
    $requirements = [
        'PHP Version >= 7.0' => version_compare(PHP_VERSION, '7.0.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
        'Session Support' => function_exists('session_start'),
        'JSON Support' => function_exists('json_encode'),
        'cURL Support' => function_exists('curl_init')
    ];
    
    return $requirements;
}

// Function to set PHP configurations
function setPhpConfig() {
    // Set timezone
    if (function_exists('date_default_timezone_set')) {
        date_default_timezone_set('UTC');
    }
    
    // Set memory limit
    ini_set('memory_limit', '128M');
    
    // Set execution time
    ini_set('max_execution_time', 30);
    
    // Set upload limits
    ini_set('upload_max_filesize', '10M');
    ini_set('post_max_size', '10M');
    
    // Set session configurations
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    // Enable output buffering
    if (!ob_get_level()) {
        ob_start();
    }
}

// Initialize configurations
setPhpConfig();

// Handle CORS for AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400');
    exit(0);
}

// Add CORS headers for actual requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Function to sanitize input
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Function to validate URL
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// Function to generate CSRF token
function generateCSRFToken() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

// Function to verify CSRF token
function verifyCSRFToken($token) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Function to log errors
function logError($message, $file = 'error.log') {
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message" . PHP_EOL;
    error_log($log_message, 3, $file);
}

// Function to redirect with JavaScript (as requested)
function redirectJS($url, $delay = 0) {
    echo "<script>";
    if ($delay > 0) {
        echo "setTimeout(function() { window.location.href = '$url'; }, $delay);";
    } else {
        echo "window.location.href = '$url';";
    }
    echo "</script>";
}

// Function to show system info (for debugging)
function showSystemInfo() {
    if (isset($_GET['debug']) && $_GET['debug'] === 'info') {
        echo "<div style='background: #f0f0f0; padding: 20px; margin: 20px; border-radius: 10px;'>";
        echo "<h3>System Information</h3>";
        echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
        echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
        echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
        echo "<p><strong>Current Script:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
        echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
        
        echo "<h4>Server Requirements Check:</h4>";
        $requirements = checkServerRequirements();
        foreach ($requirements as $req => $status) {
            $color = $status ? 'green' : 'red';
            $icon = $status ? '✅' : '❌';
            echo "<p style='color: $color;'>$icon $req</p>";
        }
        
        echo "<h4>Loaded Extensions:</h4>";
        $extensions = get_loaded_extensions();
        sort($extensions);
        echo "<p>" . implode(', ', $extensions) . "</p>";
        echo "</div>";
    }
}

// Error handler function
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_message = "Error [$errno]: $errstr in $errfile on line $errline";
    logError($error_message);
    
    // Don't show errors in production
    if (defined('PRODUCTION') && PRODUCTION) {
        return true;
    }
    
    return false;
}

// Set custom error handler
set_error_handler('customErrorHandler');

// Exception handler
function customExceptionHandler($exception) {
    $error_message = "Uncaught exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    logError($error_message);
    
    if (defined('PRODUCTION') && PRODUCTION) {
        echo "An error occurred. Please try again later.";
    } else {
        echo "<div style='background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin: 10px;'>";
        echo "<strong>Exception:</strong> " . $exception->getMessage() . "<br>";
        echo "<strong>File:</strong> " . $exception->getFile() . "<br>";
        echo "<strong>Line:</strong> " . $exception->getLine();
        echo "</div>";
    }
}

// Set custom exception handler
set_exception_handler('customExceptionHandler');

// Function to compress output
function compressOutput() {
    if (extension_loaded('zlib') && !ob_get_level()) {
        ob_start('ob_gzhandler');
    }
}

// Enable output compression
compressOutput();

// Function to minify HTML output
function minifyHTML($html) {
    // Remove comments
    $html = preg_replace('/<!--(?!<!)[^\[>].*?-->/s', '', $html);
    
    // Remove extra whitespace
    $html = preg_replace('/\s+/', ' ', $html);
    
    // Remove whitespace around tags
    $html = preg_replace('/>\s+</', '><', $html);
    
    return trim($html);
}

// Auto-minify HTML output (optional)
if (defined('MINIFY_HTML') && MINIFY_HTML) {
    ob_start(function($html) {
        return minifyHTML($html);
    });
}

// Show system info if requested
showSystemInfo();

// Global constants
define('APP_NAME', 'WhatsApp Clone');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']));

// Database configuration check
function checkDatabaseConfig() {
    $config_file = 'db.php';
    if (!file_exists($config_file)) {
        echo "<div style='background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin: 10px;'>";
        echo "<strong>Configuration Error:</strong> Database configuration file (db.php) not found.<br>";
        echo "Please make sure all files are uploaded correctly.";
        echo "</div>";
        return false;
    }
    return true;
}

// File existence checker
function checkRequiredFiles() {
    $required_files = [
        'index.php' => 'Main login page',
        'chat.php' => 'Chat interface',
        'db.php' => 'Database configuration',
        'ajax_handler.php' => 'AJAX handler for real-time features'
    ];
    
    $missing_files = [];
    foreach ($required_files as $file => $description) {
        if (!file_exists($file)) {
            $missing_files[] = "$file ($description)";
        }
    }
    
    if (!empty($missing_files)) {
        echo "<div style='background: #fff3e0; color: #ef6c00; padding: 15px; border-radius: 5px; margin: 10px;'>";
        echo "<strong>Missing Files:</strong><br>";
        foreach ($missing_files as $file) {
            echo "• $file<br>";
        }
        echo "Please upload all required files.";
        echo "</div>";
        return false;
    }
    
    return true;
}

// Run file checks
checkDatabaseConfig();
checkRequiredFiles();

// Clean up function to run at script end
function cleanup() {
    // Close any open database connections
    if (isset($GLOBALS['db'])) {
        $GLOBALS['db'] = null;
    }
    
    // Flush output buffer
    if (ob_get_level()) {
        ob_end_flush();
    }
}

// Register cleanup function
register_shutdown_function('cleanup');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Configuration</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .config-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #25D366;
            margin-bottom: 10px;
        }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .status-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #25D366;
        }
        
        .status-card h3 {
            margin-top: 0;
            color: #333;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .status-item:last-child {
            border-bottom: none;
        }
        
        .status-ok {
            color: #28a745;
        }
        
        .status-error {
            color: #dc3545;
        }
        
        .nav-links {
            text-align: center;
            margin-top: 30px;
        }
        
        .nav-links a {
            display: inline-block;
            margin: 0 10px;
            padding: 12px 24px;
            background: #25D366;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
        }
        
        .nav-links a:hover {
            background: #128C7E;
        }
        
        .info-box {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .info-box h4 {
            margin-top: 0;
            color: #1976d2;
        }
    </style>
</head>
<body>
    <div class="config-container">
        <div class="header">
            <h1><?php echo APP_NAME; ?></h1>
            <p>Server Configuration & Status</p>
        </div>
        
        <div class="status-grid">
            <div class="status-card">
                <h3>Server Requirements</h3>
                <?php
                $requirements = checkServerRequirements();
                foreach ($requirements as $req => $status) {
                    $class = $status ? 'status-ok' : 'status-error';
                    $icon = $status ? '✅' : '❌';
                    echo "<div class='status-item'>";
                    echo "<span>$req</span>";
                    echo "<span class='$class'>$icon</span>";
                    echo "</div>";
                }
                ?>
            </div>
            
            <div class="status-card">
                <h3>System Information</h3>
                <div class="status-item">
                    <span>PHP Version</span>
                    <span class="status-ok"><?php echo PHP_VERSION; ?></span>
                </div>
                <div class="status-item">
                    <span>Server Software</span>
                    <span><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                </div>
                <div class="status-item">
                    <span>Memory Limit</span>
                    <span><?php echo ini_get('memory_limit'); ?></span>
                </div>
                <div class="status-item">
                    <span>Upload Max Size</span>
                    <span><?php echo ini_get('upload_max_filesize'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="info-box">
            <h4>Quick Setup Guide:</h4>
            <ol>
                <li>Run <strong>setup.php</strong> to create database tables</li>
                <li>Access <strong>index.php</strong> to start using the app</li>
                <li>Use test account: <strong>john_doe</strong> / <strong>password</strong></li>
            </ol>
        </div>
        
        <div class="nav-links">
            <a href="setup.php">Run Setup</a>
            <a href="test.php">Test Connection</a>
            <a href="index.php">Go to App</a>
        </div>
        
        <div style="text-align: center; margin-top: 20px; color: #666; font-size: 14px;">
            <p>App Version: <?php echo APP_VERSION; ?> | 
            <a href="?debug=info" style="color: #25D366;">Show Debug Info</a></p>
        </div>
    </div>
</body>
</html>
