<?php
require_once 'config.php';
require_once 'db.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'chat.php';</script>";
    exit();
}

$error_message = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        // Check rate limiting
        if (!checkRateLimit('login', MAX_LOGIN_ATTEMPTS, 300)) {
            $error_message = 'Too many login attempts. Please try again later.';
        } else {
            $result = $userManager->login($username, $password);
            
            if ($result['success']) {
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['username'] = $result['user']['username'];
                $_SESSION['full_name'] = $result['user']['full_name'];
                $_SESSION['profile_picture'] = $result['user']['profile_picture'];
                
                logActivity("User logged in: " . $result['user']['username']);
                echo "<script>window.location.href = 'chat.php';</script>";
                exit();
            } else {
                $error_message = $result['message'];
                logActivity("Failed login attempt for: $username", 'WARNING');
            }
        }
    } else {
        $error_message = 'Please fill in all fields';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Clone - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        .background-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(255,255,255,0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
        }

        .login-header {
            background: linear-gradient(45deg, #25D366, #128C7E);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.3;
        }

        .whatsapp-logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
            position: relative;
            z-index: 1;
        }

        .login-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 300;
            position: relative;
            z-index: 1;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 1rem;
            position: relative;
            z-index: 1;
        }

        .login-form {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            position: relative;
        }

        .form-group input:focus {
            outline: none;
            border-color: #25D366;
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 211, 102, 0.1);
            transform: translateY(-2px);
        }

        .form-group input:valid {
            border-color: #28a745;
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 18px;
            pointer-events: none;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(45deg, #25D366, #128C7E);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(37, 211, 102, 0.4);
        }

        .login-btn:active {
            transform: translateY(-1px);
        }

        .login-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        .alert-error {
            background: linear-gradient(45deg, #fee, #fdd);
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: linear-gradient(45deg, #efe, #dfd);
            color: #363;
            border: 1px solid #cfc;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .form-footer p {
            color: #666;
            margin-bottom: 15px;
        }

        .form-footer a {
            color: #25D366;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 20px;
            border: 2px solid #25D366;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .form-footer a:hover {
            background: #25D366;
            color: white;
            transform: translateY(-2px);
        }

        .loading {
            display: none;
            text-align: center;
            margin-top: 20px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #25D366;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .test-accounts {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            border-left: 4px solid #25D366;
        }

        .test-accounts h4 {
            color: #25D366;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .test-accounts p {
            font-size: 13px;
            color: #666;
            margin: 5px 0;
        }

        .test-accounts strong {
            color: #333;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
                border-radius: 20px;
            }

            .login-header {
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 1.8rem;
            }

            .login-form {
                padding: 30px 20px;
            }

            .whatsapp-logo {
                width: 60px;
                height: 60px;
                font-size: 30px;
            }
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 16px;
            padding: 5px;
        }

        .password-toggle:hover {
            color: #25D366;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 20px 0;
        }

        .remember-me input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .remember-me label {
            margin: 0;
            font-weight: normal;
            text-transform: none;
            letter-spacing: normal;
            font-size: 14px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="background-pattern"></div>
    
    <div class="login-container">
        <div class="login-header">
            <div class="whatsapp-logo">💬</div>
            <h1>Welcome Back</h1>
            <p>Sign in to continue to WhatsApp Clone</p>
        </div>
        
        <div class="login-form">
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <span>⚠️</span>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                    <div class="input-icon">👤</div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                    <button type="button" class="password-toggle" onclick="togglePassword()">👁️</button>
                </div>

                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>

                <button type="submit" class="login-btn" id="loginButton">
                    Sign In
                </button>
            </form>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Signing you in...</p>
            </div>

            <div class="test-accounts">
                <h4>🧪 Test Accounts</h4>
                <p><strong>Username:</strong> john_doe | <strong>Password:</strong> password</p>
                <p><strong>Username:</strong> jane_smith | <strong>Password:</strong> password</p>
                <p><strong>Username:</strong> mike_wilson | <strong>Password:</strong> password</p>
            </div>

            <div class="form-footer">
                <p>Don't have an account?</p>
                <a href="signin.php">Create New Account</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = '👁️';
            }
        }

        // Form submission with loading animation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const button = document.getElementById('loginButton');
            const loading = document.getElementById('loading');
            
            button.disabled = true;
            button.textContent = 'Signing In...';
            loading.style.display = 'block';
            
            // Re-enable button after 5 seconds if no redirect
            setTimeout(() => {
                button.disabled = false;
                button.textContent = 'Sign In';
                loading.style.display = 'none';
            }, 5000);
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Add floating label effect
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });

        // Quick login for test accounts
        function quickLogin(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
            document.getElementById('loginForm').submit();
        }

        // Add click handlers to test accounts
        document.querySelectorAll('.test-accounts p').forEach(p => {
            p.style.cursor = 'pointer';
            p.addEventListener('click', function() {
                const text = this.textContent;
                const username = text.match(/Username:\s*(\w+)/)[1];
                const password = text.match(/Password:\s*(\w+)/)[1];
                quickLogin(username, password);
            });
        });
    </script>
</body>
</html>
