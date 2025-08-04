<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📧 Gmail App Password Setup Guide</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
            line-height: 1.6;
        }
        .guide-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .step {
            background: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            border-left: 5px solid #3498db;
        }
        .step h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        .important {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
            margin: 15px 0;
        }
        .success {
            background: #d5f4e6;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #27ae60;
            margin: 15px 0;
        }
        .password-box {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            text-align: center;
            margin: 15px 0;
        }
        .button {
            background: #3498db;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 5px;
            font-weight: bold;
        }
        .button:hover {
            background: #2980b9;
        }
        .button.primary {
            background: #e74c3c;
        }
        .button.primary:hover {
            background: #c0392b;
        }
        .checklist {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .checklist ul {
            list-style: none;
            padding: 0;
        }
        .checklist li {
            margin: 10px 0;
            padding: 8px;
            background: white;
            border-radius: 5px;
        }
        .checklist li:before {
            content: "☐ ";
            font-size: 18px;
            margin-right: 10px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #2c3e50;
            margin: 0;
        }
        .screenshot-placeholder {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            padding: 40px;
            text-align: center;
            border-radius: 8px;
            margin: 15px 0;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="logo">
        <h1>🏠 Property Finder Nepal</h1>
        <h2>📧 Gmail App Password Setup Guide</h2>
        <p>For: <strong>bistak297@gmail.com</strong></p>
    </div>

    <div class="guide-container">
        <div class="important">
            <h3>⚠️ Important Notes</h3>
            <ul>
                <li><strong>App Password ≠ Gmail Password:</strong> This is a special 16-character password just for this app</li>
                <li><strong>More Secure:</strong> You can revoke app passwords anytime without changing your main password</li>
                <li><strong>One-Time Setup:</strong> You only need to do this once</li>
                <li><strong>2-Step Verification Required:</strong> Must be enabled first</li>
            </ul>
        </div>

        <div class="step">
            <h3>🔐 Step 1: Enable 2-Step Verification</h3>
            <p><strong>Open Google Account Security:</strong></p>
            <a href="https://myaccount.google.com/security" target="_blank" class="button">🔗 Open Google Security Settings</a>
            
            <ol>
                <li>Sign in to <strong>bistak297@gmail.com</strong></li>
                <li>Find "2-Step Verification" under "Signing in to Google"</li>
                <li>Click "2-Step Verification" and follow setup</li>
                <li>Use your phone number for verification</li>
                <li>Complete the setup process</li>
            </ol>

            <div class="screenshot-placeholder">
                📱 You'll need to verify with your phone number
            </div>
        </div>

        <div class="step">
            <h3>🔑 Step 2: Generate App Password</h3>
            <p><strong>After 2-Step Verification is enabled:</strong></p>
            
            <ol>
                <li>Go back to <a href="https://myaccount.google.com/security" target="_blank">Google Security Settings</a></li>
                <li>Find "App passwords" under "Signing in to Google"</li>
                <li>Click "App passwords" (you may need to sign in again)</li>
                <li>Select app: <strong>"Mail"</strong></li>
                <li>Select device: <strong>"Other (Custom name)"</strong></li>
                <li>Enter name: <strong>"Property Finder Nepal"</strong></li>
                <li>Click <strong>"Generate"</strong></li>
            </ol>

            <div class="important">
                <strong>🎯 Google will show a 16-character password like:</strong>
                <div class="password-box">abcd efgh ijkl mnop</div>
                <p><strong>⚠️ Copy this immediately!</strong> You won't see it again.</p>
            </div>
        </div>

        <div class="step">
            <h3>⚙️ Step 3: Configure Your System</h3>
            <p><strong>Now configure your Nepal House Rental system:</strong></p>
            
            <a href="email_setup.php" target="_blank" class="button primary">🔧 Open Email Configuration</a>
            
            <ol>
                <li><strong>Gmail Address:</strong> <code>bistak297@gmail.com</code></li>
                <li><strong>App Password:</strong> Enter the 16-character password (remove spaces)</li>
                <li><strong>Test Mode:</strong> Uncheck to enable real emails</li>
                <li>Click <strong>"Save Configuration"</strong></li>
            </ol>

            <div class="success">
                <strong>✅ Example Configuration:</strong><br>
                <strong>Gmail User:</strong> bistak297@gmail.com<br>
                <strong>App Password:</strong> abcdefghijklmnop<br>
                <strong>Test Mode:</strong> ☐ Unchecked
            </div>
        </div>

        <div class="step">
            <h3>🧪 Step 4: Test Your Setup</h3>
            <p><strong>Verify everything is working:</strong></p>
            
            <a href="email_setup.php" target="_blank" class="button">📧 Send Test Email</a>
            <a href="signup_with_verification.php" target="_blank" class="button">📝 Test Signup Process</a>
            
            <ol>
                <li>Send a test email to your own address</li>
                <li>Try the signup process with a real email</li>
                <li>Check if OTP emails are received from <code>bistak297@gmail.com</code></li>
                <li>Verify OTP codes work correctly</li>
            </ol>
        </div>

        <div class="checklist">
            <h3>✅ Setup Checklist</h3>
            <ul>
                <li>2-Step Verification enabled for bistak297@gmail.com</li>
                <li>App Password generated for "Property Finder Nepal"</li>
                <li>16-character password copied and saved</li>
                <li>Email configuration updated in system</li>
                <li>Test Mode disabled for real emails</li>
                <li>Test email sent successfully</li>
                <li>Signup process tested and working</li>
            </ul>
        </div>

        <div class="important">
            <h3>🔒 Security Best Practices</h3>
            <ul>
                <li><strong>Keep App Password Secret:</strong> Don't share the 16-character password</li>
                <li><strong>Revoke if Needed:</strong> You can revoke app passwords anytime from Google settings</li>
                <li><strong>Monitor Usage:</strong> Check your Gmail "Sent" folder to see emails being sent</li>
                <li><strong>Backup Access:</strong> Keep your phone number updated for 2-Step Verification</li>
            </ul>
        </div>

        <div class="step">
            <h3>🚀 Quick Links</h3>
            <a href="https://myaccount.google.com/security" target="_blank" class="button">🔐 Google Security</a>
            <a href="email_setup.php" class="button primary">⚙️ Email Setup</a>
            <a href="signup_with_verification.php" class="button">📝 Test Signup</a>
            <a href="email_debug.php" class="button">🔍 Debug Emails</a>
        </div>
    </div>

    <script>
        // Auto-update checklist when clicked
        document.querySelectorAll('.checklist li').forEach(item => {
            item.addEventListener('click', function() {
                if (this.style.opacity === '0.6') {
                    this.style.opacity = '1';
                    this.style.textDecoration = 'none';
                    this.innerHTML = this.innerHTML.replace('☑', '☐');
                } else {
                    this.style.opacity = '0.6';
                    this.style.textDecoration = 'line-through';
                    this.innerHTML = this.innerHTML.replace('☐', '☑');
                }
            });
            item.style.cursor = 'pointer';
        });
        
        console.log('📧 Gmail App Password setup guide loaded');
    </script>
</body>
</html>
