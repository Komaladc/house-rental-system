<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
include_once 'lib/Database.php';
include_once 'classes/RealEmailService.php';
include_once 'classes/EmailOTP.php';
include_once 'config/gmail_config.php';

$db = new Database();
?>
<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📧 Email Flow Test - Nepal House Rental</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .test-container {
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
        .warning { border-left-color: #f39c12; background: #fff3cd; }
        .info { border-left-color: #17a2b8; background: #d1ecf1; }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 2.5em;
        }
        .flow-step {
            display: flex;
            align-items: center;
            margin: 15px 0;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        .step-number {
            background: #3498db;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        .step-content h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }
        .step-content p {
            margin: 0;
            color: #7f8c8d;
        }
        .email-preview {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .email-header {
            background: #2c3e50;
            color: white;
            padding: 10px 15px;
            border-radius: 5px 5px 0 0;
            margin: -20px -20px 15px -20px;
        }
        .button {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="logo">
        <h1>🏠 Nepal House Rental</h1>
        <p>Email Flow Testing</p>
    </div>

    <div class="test-container">
        <h2>📧 New Professional Email System</h2>
        
        <div class="section success">
            <h3>✅ Email Flow Overview</h3>
            <p>The system now uses a professional dummy sender address while ensuring users receive emails at their provided addresses.</p>
            
            <div class="flow-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4>User Registration</h4>
                    <p>User provides their email address during signup</p>
                </div>
            </div>
            
            <div class="flow-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4>Email Generation</h4>
                    <p>System generates OTP and verification email</p>
                </div>
            </div>
            
            <div class="flow-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4>Professional Sending</h4>
                    <p><strong>From:</strong> bistak297@gmail.com (Property Finder Nepal)</p>
                    <p><strong>To:</strong> User's provided email address</p>
                    <p><strong>SMTP:</strong> bistak297@gmail.com (same as sender)</p>
                </div>
            </div>
            
            <div class="flow-step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h4>User Verification</h4>
                    <p>User receives email and verifies with OTP or link</p>
                </div>
            </div>
        </div>

        <div class="section info">
            <h3>📨 Email Preview</h3>
            <p>Here's how the email will appear to users:</p>
            
            <div class="email-preview">
                <div class="email-header">
                    <strong>From:</strong> Property Finder Nepal &lt;bistak297@gmail.com&gt;<br>
                    <strong>To:</strong> user@example.com<br>
                    <strong>Subject:</strong> Email Verification Required - Property Finder Nepal
                </div>
                
                <h3>नमस्कार! Welcome to Property Finder Nepal!</h3>
                
                <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
                    <h4>📧 Email Verification Required</h4>
                    <p><strong>We need to verify that you have access to this email address:</strong></p>
                    <p style="font-size: 18px; font-weight: bold; color: #2c3e50;">📧 user@example.com</p>
                    <p>This email was sent from <strong>bistak297@gmail.com</strong> (Property Finder Nepal) to verify your ownership of the above email address.</p>
                </div>
                
                <p>Your verification code: <strong style="font-size: 24px; color: #e74c3c;">123456</strong></p>
            </div>
        </div>

        <div class="section warning">
            <h3>⚙️ Configuration Status</h3>
            
            <?php
            $realEmailService = new RealEmailService();
            $status = $realEmailService->getConfigurationStatus();
            ?>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Email Sender (User Sees):</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">bistak297@gmail.com</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>SMTP Authentication:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($status['gmail_user']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>App Password:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><?php echo $status['gmail_pass_set'] ? '✅ Configured' : '❌ Not Set'; ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Test Mode:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><?php echo (defined('EMAIL_TEST_MODE') && EMAIL_TEST_MODE) ? '🧪 Enabled (Mock)' : '🚀 Disabled (Real)'; ?></td>
                </tr>
            </table>
        </div>

        <div class="section info">
            <h3>🔧 How It Works</h3>
            <ul>
                <li><strong>Real Sender:</strong> Users see emails from "bistak297@gmail.com" (Property Finder Nepal)</li>
                <li><strong>Direct SMTP:</strong> Your Gmail account is used for both authentication and sending</li>
                <li><strong>User's Email as Recipient:</strong> OTP is sent to whatever email address the user provides</li>
                <li><strong>Secure Authentication:</strong> Uses Gmail's app password system for security</li>
                <li><strong>Fallback System:</strong> If real email fails, automatically logs to file for debugging</li>
            </ul>
        </div>

        <div class="section success">
            <h3>🚀 Test the System</h3>
            <p>Ready to test the new email system:</p>
            
            <a href="email_setup.php" class="button">⚙️ Configure Gmail SMTP</a>
            <a href="signup_with_verification.php" class="button">📝 Test Signup</a>
            <a href="email_debug.php" class="button">🔍 View Debug Info</a>
        </div>
    </div>

    <script>
        console.log('📧 Email flow test page loaded');
        console.log('Configuration: Professional dummy sender (noreply@gmail.com) → User email verification');
    </script>
</body>
</html>
