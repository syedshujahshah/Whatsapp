<?php
// Main configuration file - include this instead of .htaccess
require_once 'htaccess_config.php';

// Application-specific configurations
define('PRODUCTION', false); // Set to true in production
define('MINIFY_HTML', false); // Set to true to minify HTML output
define('ENABLE_LOGGING', true); // Enable error logging

// Database configuration (you can override db.php settings here)
define('DB_HOST', 'localhost');
define('DB_NAME', 'dbedepbijkslrz');
define('DB_USER', 'ulnrcogla9a1t');
define('DB_PASS', 'yolpwow1mwr2');

// Application settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Security settings
define('CSRF_PROTECTION', true);
define('XSS_PROTECTION', true);
define('SQL_INJECTION_PROTECTION', true);

// Feature flags
define('ENABLE_FILE_UPLOAD', true);
define('ENABLE_GROUP_CHAT', true);
define('ENABLE_VOICE_MESSAGES', false);
define('ENABLE_VIDEO_CALLS', false);

// Notification settings
define('ENABLE_EMAIL_NOTIFICATIONS', false);
define('ENABLE_PUSH_NOTIFICATIONS', false);

// Rate limiting
define('RATE_LIMIT_MESSAGES', 100); // Messages per minute
define('RATE_LIMIT_LOGIN', 10); // Login attempts per minute

// Cache settings
define('ENABLE_CACHE', false);
define('CACHE_DURATION', 300); // 5 minutes

// Debug settings (only for development)
if (!PRODUCTION) {
    define('DEBUG_MODE', true);
    define('SHOW_SQL_QUERIES', false);
    define('LOG_ALL_REQUESTS', false);
} else {
    define('DEBUG_MODE', false);
    define('SHOW_SQL_QUERIES', false);
    define('LOG_ALL_REQUESTS', false);
}

// Function to get configuration value
function getConfig($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

// Function to check if feature is enabled
function isFeatureEnabled($feature) {
    return getConfig($feature, false) === true;
}

// Initialize session with security settings
function initSecureSession() {
    if (session_status() == PHP_SESSION_NONE) {
        // Set secure session parameters
        session_set_cookie_params([
            'lifetime' => SESSION_TIMEOUT,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        session_start();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// Initialize secure session
initSecureSession();

// Rate limiting function
function checkRateLimit($action, $limit, $window = 60) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    $now = time();
    $key = $action . '_' . $_SERVER['REMOTE_ADDR'];
    
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = [];
    }
    
    // Clean old entries
    $_SESSION['rate_limit'][$key] = array_filter(
        $_SESSION['rate_limit'][$key],
        function($timestamp) use ($now, $window) {
            return ($now - $timestamp) < $window;
        }
    );
    
    // Check if limit exceeded
    if (count($_SESSION['rate_limit'][$key]) >= $limit) {
        return false;
    }
    
    // Add current request
    $_SESSION['rate_limit'][$key][] = $now;
    return true;
}

// Input validation and sanitization
function validateAndSanitize($data, $type = 'string') {
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_VALIDATE_EMAIL);
        case 'int':
            return filter_var($data, FILTER_VALIDATE_INT);
        case 'float':
            return filter_var($data, FILTER_VALIDATE_FLOAT);
        case 'url':
            return filter_var($data, FILTER_VALIDATE_URL);
        case 'string':
        default:
            return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

// File upload validation
function validateFileUpload($file) {
    if (!isFeatureEnabled('ENABLE_FILE_UPLOAD')) {
        return ['success' => false, 'message' => 'File upload is disabled'];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error occurred'];
    }
    
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return ['success' => false, 'message' => 'File too large'];
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_FILE_TYPES)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }
    
    return ['success' => true];
}

// Logging function
function logActivity($message, $level = 'INFO') {
    if (!ENABLE_LOGGING) return;
    
    $timestamp = date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'] ?? 'anonymous';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $log_entry = "[$timestamp] [$level] [User: $user_id] [IP: $ip] $message" . PHP_EOL;
    
    $log_file = 'logs/activity_' . date('Y-m-d') . '.log';
    
    // Create logs directory if it doesn't exist
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    error_log($log_entry, 3, $log_file);
}

// Performance monitoring
function startTimer($name) {
    if (!DEBUG_MODE) return;
    
    if (!isset($GLOBALS['timers'])) {
        $GLOBALS['timers'] = [];
    }
    
    $GLOBALS['timers'][$name] = microtime(true);
}

function endTimer($name) {
    if (!DEBUG_MODE) return;
    
    if (isset($GLOBALS['timers'][$name])) {
        $duration = microtime(true) - $GLOBALS['timers'][$name];
        logActivity("Timer '$name': " . number_format($duration * 1000, 2) . "ms", 'DEBUG');
    }
}

// Memory usage tracking
function logMemoryUsage($checkpoint = '') {
    if (!DEBUG_MODE) return;
    
    $memory = memory_get_usage(true);
    $peak = memory_get_peak_usage(true);
    
    logActivity("Memory usage $checkpoint: " . formatBytes($memory) . " (Peak: " . formatBytes($peak) . ")", 'DEBUG');
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

// Initialize performance monitoring
if (DEBUG_MODE) {
    startTimer('total_execution');
    logMemoryUsage('start');
}

// Cleanup function
function performCleanup() {
    if (DEBUG_MODE) {
        endTimer('total_execution');
        logMemoryUsage('end');
    }
    
    // Clean old session data
    if (isset($_SESSION['rate_limit'])) {
        $now = time();
        foreach ($_SESSION['rate_limit'] as $key => $timestamps) {
            $_SESSION['rate_limit'][$key] = array_filter($timestamps, function($ts) use ($now) {
                return ($now - $ts) < 3600; // Keep last hour
            });
        }
    }
}

// Register cleanup function
register_shutdown_function('performCleanup');

// Set global error handler for production
if (PRODUCTION) {
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        logActivity("Error [$errno]: $errstr in $errfile:$errline", 'ERROR');
        return true; // Don't display errors in production
    });
}

?>
