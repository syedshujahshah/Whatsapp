<?php
// 404 Fix Helper - This will help identify and fix 404 issues
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Error Fix Helper</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        h1 { color: #25D366; text-align: center; }
        h2 { color: #333; border-bottom: 2px solid #25D366; padding-bottom: 10px; }
        
        .step {
            background: #f8f9fa;
            border-left: 4px solid #25D366;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 10px 10px 0;
        }
        
        .error { background: #ffebee; border-left-color: #f44336; }
        .success { background: #e8f5e9; border-left-color: #4caf50; }
        .warning { background: #fff3e0; border-left-color: #ff9800; }
        
        .code {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #25D366;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            transition: background 0.3s;
        }
        
        .btn:hover { background: #128C7E; }
        
        ul { line-height: 1.8; }
        li { margin: 8px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 404 Error Fix Helper</h1>
        <p style="text-align: center; color: #666;">Let's fix your WhatsApp Clone 404 error step by step!</p>

        <h2>🔍 Step 1: Check File Upload</h2>
        <div class="step">
            <h3>Make sure ALL files are uploaded:</h3>
            <ul>
                <li>✅ <strong>index.php</strong> - Welcome page</li>
                <li>✅ <strong>login.php</strong> - Login page</li>
                <li>✅ <strong>signin.php</strong> - Registration page</li>
                <li>✅ <strong>chat.php</strong> - Chat interface</li>
                <li>✅ <strong>db.php</strong> - Database connection</li>
                <li>✅ <strong>config.php</strong> - Configuration</li>
                <li>✅ <strong>ajax_handler.php</strong> - AJAX handler</li>
                <li>✅ <strong>setup.php</strong> - Database setup</li>
                <li>✅ <strong>logout.php</strong> - Logout handler</li>
            </ul>
        </div>

        <h2>🌐 Step 2: Check Your URL</h2>
        <div class="step warning">
            <h3>Common URL Issues:</h3>
            <ul>
                <li><strong>Wrong:</strong> yoursite.com/whatsapp-clone/</li>
                <li><strong>Right:</strong> yoursite.com/index.php</li>
                <li><strong>Or:</strong> yoursite.com/ (if index.php is default)</li>
            </ul>
            
            <h3>Try these URLs:</h3>
            <div class="code">
                http://yoursite.com/index.php<br>
                http://yoursite.com/login.php<br>
                http://yoursite.com/signin.php<br>
                http://yoursite.com/check-server.php
            </div>
        </div>

        <h2>📁 Step 3: File Permissions</h2>
        <div class="step">
            <h3>Set correct file permissions:</h3>
            <ul>
                <li><strong>PHP files:</strong> 644 or 755</li>
                <li><strong>Directories:</strong> 755</li>
                <li><strong>Config files:</strong> 644</li>
            </ul>
            
            <p><strong>Via FTP/cPanel:</strong> Right-click files → Properties → Set permissions to 644</p>
        </div>

        <h2>⚙️ Step 4: Server Configuration</h2>
        <div class="step">
            <h3>Check if your server supports:</h3>
            <ul>
                <li>✅ <strong>PHP 7.0+</strong> (recommended 7.4+)</li>
                <li>✅ <strong>MySQL/MariaDB</strong></li>
                <li>✅ <strong>PDO extension</strong></li>
                <li>✅ <strong>Sessions enabled</strong></li>
            </ul>
        </div>

        <h2>🗄️ Step 5: Database Setup</h2>
        <div class="step error">
            <h3>IMPORTANT: Run database setup first!</h3>
            <p>Before accessing other pages, you MUST create the database tables:</p>
            
            <ol>
                <li>Upload <strong>setup.php</strong></li>
                <li>Visit: <strong>yoursite.com/setup.php</strong></li>
                <li>Wait for "Setup completed successfully!"</li>
                <li>Then try accessing other pages</li>
            </ol>
        </div>

        <h2>🔧 Step 6: Quick Fixes</h2>
        <div class="step success">
            <h3>Try these solutions:</h3>
            
            <h4>Option 1: Direct File Access</h4>
            <p>Instead of yoursite.com/, try:</p>
            <div class="code">yoursite.com/index.php</div>
            
            <h4>Option 2: Check .htaccess</h4>
            <p>If you have .htaccess file, temporarily rename it to .htaccess-backup and test</p>
            
            <h4>Option 3: Clear Browser Cache</h4>
            <p>Press Ctrl+F5 (or Cmd+Shift+R on Mac) to hard refresh</p>
            
            <h4>Option 4: Check Error Logs</h4>
            <p>Look for error_log file in your directory or check cPanel error logs</p>
        </div>

        <h2>🚀 Step 7: Testing Order</h2>
        <div class="step">
            <h3>Test files in this order:</h3>
            <ol>
                <li><strong>check-server.php</strong> - Verify server status</li>
                <li><strong>setup.php</strong> - Create database tables</li>
                <li><strong>index.php</strong> - Welcome page</li>
                <li><strong>login.php</strong> - Login page</li>
                <li><strong>signin.php</strong> - Registration page</li>
            </ol>
        </div>

        <h2>📞 Step 8: Still Having Issues?</h2>
        <div class="step warning">
            <h3>Contact Information Needed:</h3>
            <ul>
                <li>🌐 <strong>Your website URL</strong></li>
                <li>🖥️ <strong>Hosting provider</strong> (e.g., cPanel, Hostinger, etc.)</li>
                <li>📋 <strong>Error message</strong> (exact text)</li>
                <li>📱 <strong>Browser</strong> you're using</li>
                <li>📊 <strong>Result from check-server.php</strong></li>
            </ul>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <a href="check-server.php" class="btn">🔍 Run Server Check</a>
            <a href="setup.php" class="btn">🗄️ Setup Database</a>
            <a href="index.php" class="btn">🏠 Go to Home</a>
        </div>

        <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; margin-top: 30px;">
            <h3>💡 Pro Tip:</h3>
            <p>Most 404 errors happen because:</p>
            <ol>
                <li><strong>Files not uploaded</strong> - Check FTP/file manager</li>
                <li><strong>Wrong URL</strong> - Add .php extension</li>
                <li><strong>Database not setup</strong> - Run setup.php first</li>
                <li><strong>File permissions</strong> - Set to 644/755</li>
            </ol>
        </div>
    </div>
</body>
</html>
