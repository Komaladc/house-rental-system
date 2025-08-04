<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
include_once 'lib/Database.php';
include_once 'classes/RealEmailService.php';
include_once 'config/gmail_config.php';

$db = new Database();
$realEmailService = new RealEmailService();
$status = $realEmailService->getConfigurationStatus();

// Handle form submission to update configuration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_config'])) {
    $gmailUser = $_POST['gmail_user'];
    $gmailPass = $_POST['gmail_pass'];
    $testMode = isset($_POST['test_mode']) ? 'true' : 'false';
    
    // Update the config file with real sender
    $configContent = '<?php
/**
 * Gmail SMTP Configuration for Nepal House Rental System
 * Using bistak297@gmail.com as sender for OTP emails
 */

// ==== SMTP AUTHENTICATION (Your actual Gmail for sending) ====
define(\'GMAIL_SMTP_USER\', \'' . addslashes($gmailUser) . '\'); // Your Gmail for SMTP auth
define(\'GMAIL_SMTP_PASS\', \'' . addslashes($gmailPass) . '\'); // Your Gmail app password

// ==== EMAIL APPEARANCE (What users see) ====
define(\'EMAIL_FROM_NAME\', \'Property Finder Nepal\');
define(\'EMAIL_FROM_ADDRESS\', \'bistak297@gmail.com\'); // Real sender address
define(\'EMAIL_REPLY_TO\', \'bistak297@gmail.com\'); // Reply to same address

// ==== SYSTEM SETTINGS ====
define(\'EMAIL_TEST_MODE\', ' . $testMode . '); // Set to false for real emails
define(\'COMPANY_NAME\', \'Property Finder Nepal\');
define(\'COMPANY_ADDRESS\', \'Thamel Marg, Kathmandu-44600, Nepal\');
define(\'COMPANY_PHONE\', \'+977-1-4567890\');
?>';

    if (file_put_contents('config/gmail_config.php', $configContent)) {
        echo "<script>alert('✅ Configuration updated successfully!'); window.location.reload();</script>";
    } else {
        echo "<script>alert('❌ Failed to update configuration file.');</script>";
    }
}

// Handle test email
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_test'])) {
    $testEmail = $_POST['test_email'];
    
    $result = $realEmailService->sendEmail(
        $testEmail,
        '🧪 Test Email - Property Finder Nepal',
        '<html><body>
            <h2>🎉 Congratulations!</h2>
            <p>Your email configuration is working perfectly!</p>
            <p>You can now receive real emails from Property Finder Nepal.</p>
            <p><strong>Sent at:</strong> ' . date('Y-m-d H:i:s') . '</p>
        </body></html>'
    );
    
    if ($result['success']) {
        echo "<script>alert('✅ Test email sent successfully to $testEmail!');</script>";
    } else {
        echo "<script>alert('❌ Failed to send test email: " . addslashes($result['message']) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📧 Email Configuration - Nepal House Rental</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .config-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .section {
            background: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            border-left: 5px solid #3498db;
        }
        .success { border-left-color: #27ae60; background: #d5f4e6; }
        .error { border-left-color: #e74c3c; background: #f8d7da; }
        .warning { border-left-color: #f39c12; background: #fff3cd; }
        .form-group {
            margin: 20px 0;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            border-color: #3498db;
            outline: none;
        }
        .button {
            background: #e74c3c;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin: 5px;
            display: inline-block;
        }
        .button:hover {
            background: #c0392b;
        }
        .button.success {
            background: #27ae60;
        }
        .button.success:hover {
            background: #229954;
        }
        .button.warning {
            background: #f39c12;
        }
        .button.warning:hover {
            background: #e67e22;
        }
        .status-indicator {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .status-good {
            background: #d5f4e6;
            color: #27ae60;
        }
        .status-bad {
            background: #f8d7da;
            color: #e74c3c;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 2.5em;
        }
        .logo p {
            color: #7f8c8d;
            margin: 10px 0;
        }
        .step-guide {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .step-guide h3 {
            color: #27ae60;
            margin-top: 0;
        }
        .step-guide ol {
            padding-left: 20px;
        }
        .step-guide li {
            margin: 10px 0;
            line-height: 1.6;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="logo">
        <h1>🏠 Nepal House Rental</h1>
        <p>Email Configuration Setup</p>
    </div>

    <div class="config-container">
        <h2>📧 Gmail SMTP Configuration</h2>
        
        <!-- Current Status -->
        <div class="section <?php echo $status['configured'] ? 'success' : 'warning'; ?>">
            <h3>🔍 Current Status</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px;"><strong>Gmail SMTP User:</strong></td>
                    <td style="padding: 8px;"><?php echo htmlspecialchars($status['gmail_user']); ?></td>
                    <td style="padding: 8px;">
                        <span class="status-indicator <?php echo !empty($status['gmail_user']) && $status['gmail_user'] !== 'your-email@gmail.com' ? 'status-good' : 'status-bad'; ?>">
                            <?php echo !empty($status['gmail_user']) && $status['gmail_user'] !== 'your-email@gmail.com' ? '✅ Set' : '❌ Not Set'; ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px;"><strong>Email Sender:</strong></td>
                    <td style="padding: 8px;">noreply@gmail.com</td>
                    <td style="padding: 8px;">
                        <span class="status-indicator status-good">✅ Professional</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px;"><strong>User Receives At:</strong></td>
                    <td style="padding: 8px;">Their provided email address</td>
                    <td style="padding: 8px;">
                        <span class="status-indicator status-good">✅ User's Email</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px;"><strong>App Password:</strong></td>
                    <td style="padding: 8px;"><?php echo $status['gmail_pass_set'] ? '••••••••••••••••' : 'Not configured'; ?></td>
                    <td style="padding: 8px;">
                        <span class="status-indicator <?php echo $status['gmail_pass_set'] ? 'status-good' : 'status-bad'; ?>">
                            <?php echo $status['gmail_pass_set'] ? '✅ Set' : '❌ Not Set'; ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px;"><strong>SMTP Server:</strong></td>
                    <td style="padding: 8px;"><?php echo $status['smtp_host']; ?>:<?php echo $status['smtp_port']; ?></td>
                    <td style="padding: 8px;">
                        <span class="status-indicator status-good">✅ Ready</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px;"><strong>Test Mode:</strong></td>
                    <td style="padding: 8px;"><?php echo defined('EMAIL_TEST_MODE') && EMAIL_TEST_MODE ? 'Enabled (Mock emails)' : 'Disabled (Real emails)'; ?></td>
                    <td style="padding: 8px;">
                        <span class="status-indicator <?php echo defined('EMAIL_TEST_MODE') && EMAIL_TEST_MODE ? 'status-bad' : 'status-good'; ?>">
                            <?php echo defined('EMAIL_TEST_MODE') && EMAIL_TEST_MODE ? '⚠️ Test Mode' : '🚀 Live Mode'; ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Setup Instructions -->
        <div class="step-guide">
            <h3>🔧 How to Set Up Gmail App Password</h3>
            <ol>
                <li><strong>Enable 2-Step Verification:</strong> Go to <a href="https://myaccount.google.com/security" target="_blank">Google Account Security</a></li>
                <li><strong>Create App Password:</strong> Under "Signing in to Google" → "App passwords"</li>
                <li><strong>Select App:</strong> Choose "Mail" and "Other (Custom name)"</li>
                <li><strong>Name It:</strong> Enter "Property Finder Nepal"</li>
                <li><strong>Copy Password:</strong> Google will generate a 16-character password</li>
                <li><strong>Enter Below:</strong> Paste the app password in the form below</li>
            </ol>
            <p><strong>🔒 Security Note:</strong> App passwords are more secure than your regular Gmail password and can be revoked anytime.</p>
        </div>

        <!-- Configuration Form -->
        <div class="section">
            <h3>⚙️ Update Configuration</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="gmail_user">📧 Gmail Address:</label>
                    <input type="email" 
                           id="gmail_user" 
                           name="gmail_user" 
                           value="<?php echo htmlspecialchars($status['gmail_user']); ?>" 
                           placeholder="your-email@gmail.com"
                           required>
                </div>

                <div class="form-group">
                    <label for="gmail_pass">🔐 Gmail App Password (16 characters):</label>
                    <input type="password" 
                           id="gmail_pass" 
                           name="gmail_pass" 
                           placeholder="Enter your Gmail app password"
                           maxlength="16"
                           required>
                    <small style="color: #7f8c8d;">⚠️ This is NOT your regular Gmail password. Use the 16-character app password from Google.</small>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" 
                           id="test_mode" 
                           name="test_mode" 
                           <?php echo (defined('EMAIL_TEST_MODE') && EMAIL_TEST_MODE) ? 'checked' : ''; ?>>
                    <label for="test_mode">🧪 Enable Test Mode (emails will be logged instead of sent)</label>
                </div>

                <button type="submit" name="update_config" class="button success">
                    💾 Save Configuration
                </button>
            </form>
        </div>

        <!-- Test Email -->
        <?php if ($status['configured'] && $status['gmail_pass_set']) { ?>
        <div class="section success">
            <h3>🧪 Send Test Email</h3>
            <p>Your email is configured! Send a test email to verify it's working:</p>
            
            <form method="POST">
                <div class="form-group">
                    <label for="test_email">📧 Test Email Address:</label>
                    <input type="email" 
                           id="test_email" 
                           name="test_email" 
                           value="<?php echo htmlspecialchars($status['gmail_user']); ?>" 
                           placeholder="Enter email to test"
                           required>
                </div>

                <button type="submit" name="send_test" class="button warning">
                    🚀 Send Test Email
                </button>
            </form>
        </div>
        <?php } ?>

        <!-- Quick Links -->
        <div class="section">
            <h3>🔗 Quick Links</h3>
            <a href="signup_with_verification.php" class="button">📝 Test Signup Page</a>
            <a href="email_debug.php" class="button">🔍 Email Debug Page</a>
            <a href="?" class="button">🔄 Refresh Status</a>
        </div>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => alert.style.display = 'none');
        }, 5000);
        
        console.log('📧 Email configuration page loaded');
    </script>
</body>
</html>
