<?php
require_once 'config.php';
require_once 'db.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'chat.php';</script>";
    exit();
}

$error_message = '';
$success_message = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($full_name) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Please fill in all fields';
    } elseif (!validateEmail($email)) {
        $error_message = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match';
    } elseif (strlen($username) < 3) {
        $error_message = 'Username must be at least 3 characters long';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error_message = 'Username can only contain letters, numbers, and underscores';
    } else {
        // Check rate limiting
        if (!checkRateLimit('register', 5, 300)) {
            $error_message = 'Too many registration attempts. Please try again later.';
        } else {
            $result = $userManager->register($username, $email, $password, $full_name);
            
            if ($result['success']) {
                $success_message = 'Account created successfully! You can now login.';
                logActivity("New user registered: $username");
                
                // Auto redirect to login after 3 seconds
                echo "<script>setTimeout(() => { window.location.href = 'login.php'; }, 3000);</script>";
            } else {
                $error_message = $result['message'];
                logActivity("Failed registration attempt for: $username", 'WARNING');
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Clone - Sign Up</title>
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
                radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255,255,255,0.05) 0%, transparent 50%);
            pointer-events: none;
        }

        .signin-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 1;
        }

        .signin-header {
            background: linear-gradient(45deg, #25D366, #128C7E);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
        }

        .signin-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M20 20c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10zm10 0c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10z'/%3E%3C/g%3E%3C/svg%3E");
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
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .signin-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 300;
            position: relative;
            z-index: 1;
        }

        .signin-header p {
            opacity: 0.9;
            font-size: 1rem;
            position: relative;
            z-index: 1;
        }

        .signin-form {
            padding: 40px 30px;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
            flex: 1;
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

        .form-group input:invalid:not(:focus):not(:placeholder-shown) {
            border-color: #dc3545;
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

        .password-strength {
            margin-top: 8px;
            font-size: 12px;
        }

        .strength-bar {
            height: 4px;
            background: #eee;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background: #dc3545; width: 25%; }
        .strength-fair { background: #ffc107; width: 50%; }
        .strength-good { background: #28a745; width: 75%; }
        .strength-strong { background: #25D366; width: 100%; }

        .signin-btn {
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

        .signin-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .signin-btn:hover::before {
            left: 100%;
        }

        .signin-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(37, 211, 102, 0.4);
        }

        .signin-btn:active {
            transform: translateY(-1px);
        }

        .signin-btn:disabled {
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

        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 20px 0;
            font-size: 14px;
            color: #666;
        }

        .terms-checkbox input[type="checkbox"] {
            width: auto;
            margin: 0;
            margin-top: 2px;
        }

        .terms-checkbox label {
            margin: 0;
            font-weight: normal;
            text-transform: none;
            letter-spacing: normal;
            cursor: pointer;
            line-height: 1.4;
        }

        .terms-checkbox a {
            color: #25D366;
            text-decoration: none;
        }

        .terms-checkbox a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .signin-container {
                margin: 10px;
                border-radius: 20px;
            }

            .signin-header {
                padding: 30px 20px;
            }

            .signin-header h1 {
                font-size: 1.8rem;
            }

            .signin-form {
                padding: 30px 20px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
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

        .validation-message {
            font-size: 12px;
            margin-top: 5px;
            color: #dc3545;
            display: none;
        }

        .validation-message.show {
            display: block;
        }

        .validation-message.success {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="background-pattern"></div>
    
    <div class="signin-container">
        <div class="signin-header">
            <div class="whatsapp-logo">🚀</div>
            <h1>Join WhatsApp Clone</h1>
            <p>Create your account and start connecting</p>
        </div>
        
        <div class="signin-form">
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <span>⚠️</span>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <span>✅</span>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="signinForm">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required 
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                           autocomplete="name">
                    <div class="input-icon">👤</div>
                    <div class="validation-message" id="name-message"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               autocomplete="username" pattern="[a-zA-Z0-9_]{3,}">
                        <div class="input-icon">@</div>
                        <div class="validation-message" id="username-message"></div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               autocomplete="email">
                        <div class="input-icon">📧</div>
                        <div class="validation-message" id="email-message"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           autocomplete="new-password" minlength="6">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strength-fill"></div>
                        </div>
                        <div id="strength-text">Password strength</div>
                    </div>
                    <div class="validation-message" id="password-message"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           autocomplete="new-password">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">👁️</button>
                    <div class="validation-message" id="confirm-password-message"></div>
                </div>

                <div class="terms-checkbox">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        I agree to the <a href="#" onclick="showTerms()">Terms of Service</a> 
                        and <a href="#" onclick="showPrivacy()">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="signin-btn" id="signinButton">
                    Create Account
                </button>
            </form>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Creating your account...</p>
            </div>

            <div class="form-footer">
                <p>Already have an account?</p>
                <a href="login.php">Sign In</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleButton = passwordInput.nextElementSibling;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = '👁️';
            }
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            let feedback = [];

            if (password.length >= 8) strength += 1;
            else feedback.push('At least 8 characters');

            if (/[a-z]/.test(password)) strength += 1;
            else feedback.push('Lowercase letter');

            if (/[A-Z]/.test(password)) strength += 1;
            else feedback.push('Uppercase letter');

            if (/[0-9]/.test(password)) strength += 1;
            else feedback.push('Number');

            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            else feedback.push('Special character');

            return { strength, feedback };
        }

        // Real-time password strength
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const { strength, feedback } = checkPasswordStrength(password);
            const strengthFill = document.getElementById('strength-fill');
            const strengthText = document.getElementById('strength-text');

            strengthFill.className = 'strength-fill';
            
            if (strength <= 1) {
                strengthFill.classList.add('strength-weak');
                strengthText.textContent = 'Weak password';
            } else if (strength <= 2) {
                strengthFill.classList.add('strength-fair');
                strengthText.textContent = 'Fair password';
            } else if (strength <= 3) {
                strengthFill.classList.add('strength-good');
                strengthText.textContent = 'Good password';
            } else {
                strengthFill.classList.add('strength-strong');
                strengthText.textContent = 'Strong password';
            }
        });

        // Real-time validation
        document.getElementById('username').addEventListener('input', function() {
            const username = this.value;
            const message = document.getElementById('username-message');
            
            if (username.length < 3) {
                message.textContent = 'Username must be at least 3 characters';
                message.className = 'validation-message show';
            } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                message.textContent = 'Only letters, numbers, and underscores allowed';
                message.className = 'validation-message show';
            } else {
                message.textContent = 'Username looks good!';
                message.className = 'validation-message show success';
            }
        });

        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const message = document.getElementById('confirm-password-message');
            
            if (confirmPassword && password !== confirmPassword) {
                message.textContent = 'Passwords do not match';
                message.className = 'validation-message show';
            } else if (confirmPassword && password === confirmPassword) {
                message.textContent = 'Passwords match!';
                message.className = 'validation-message show success';
            } else {
                message.className = 'validation-message';
            }
        });

        // Form submission
        document.getElementById('signinForm').addEventListener('submit', function(e) {
            const button = document.getElementById('signinButton');
            const loading = document.getElementById('loading');
            
            button.disabled = true;
            button.textContent = 'Creating Account...';
            loading.style.display = 'block';
            
            // Re-enable button after 5 seconds if no redirect
            setTimeout(() => {
                button.disabled = false;
                button.textContent = 'Create Account';
                loading.style.display = 'none';
            }, 5000);
        });

        // Auto-hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        function showTerms() {
            alert('Terms of Service:\n\n1. Use this service responsibly\n2. Do not spam or harass other users\n3. Keep your account secure\n4. Report any issues to administrators');
        }

        function showPrivacy() {
            alert('Privacy Policy:\n\n1. We protect your personal information\n2. Messages are stored securely\n3. We do not share your data with third parties\n4. You can delete your account anytime');
        }
    </script>
</body>
</html>
