<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
include_once 'lib/Database.php';
include_once 'classes/MockEmailService.php';

$db = new Database();
?>
<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Email Debug - Nepal House Rental</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .debug-container {
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
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #e74c3c;
            text-align: center;
            background: white;
            padding: 20px;
            margin: 20px 0;
            border: 3px solid #e74c3c;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
        }
        .email-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
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
        .button {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 5px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="logo">
        <h1>🏠 Nepal House Rental</h1>
        <p>Email Debug & OTP Viewer</p>
    </div>

    <div class="debug-container">
        <h2>📧 Email Debug Information</h2>
        
        <!-- Show recent OTP codes -->
        <div class="section success">
            <h3>🔑 Recent OTP Codes</h3>
            <p>Since email may not be configured, here are the OTP codes from the database:</p>
            
            <?php
            $otpQuery = "SELECT email, otp, purpose, created_at, expires_at, is_used 
                        FROM tbl_otp 
                        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                        ORDER BY created_at DESC 
                        LIMIT 10";
            $otpResult = $db->select($otpQuery);
            
            if ($otpResult && $otpResult->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Email</th><th>OTP Code</th><th>Purpose</th><th>Created</th><th>Status</th></tr>";
                
                while ($otp = $otpResult->fetch_assoc()) {
                    $status = $otp['is_used'] ? '✅ Used' : '⏳ Pending';
                    $isExpired = strtotime($otp['expires_at']) < time();
                    if ($isExpired && !$otp['is_used']) {
                        $status = '❌ Expired';
                    }
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($otp['email']) . "</td>";
                    echo "<td><span class='otp-code' style='font-size: 18px; padding: 5px; margin: 0;'>" . $otp['otp'] . "</span></td>";
                    echo "<td>" . htmlspecialchars($otp['purpose']) . "</td>";
                    echo "<td>" . $otp['created_at'] . "</td>";
                    echo "<td>" . $status . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='warning'>";
                echo "<p>❌ No recent OTP codes found in database.</p>";
                echo "</div>";
            }
            ?>
        </div>

        <!-- Show pending verifications -->
        <div class="section warning">
            <h3>⏳ Pending Email Verifications</h3>
            
            <?php
            $pendingQuery = "SELECT email, verification_token, created_at, expires_at, is_verified 
                            FROM tbl_pending_verification 
                            WHERE is_verified = 0 AND expires_at > NOW()
                            ORDER BY created_at DESC";
            $pendingResult = $db->select($pendingQuery);
            
            if ($pendingResult && $pendingResult->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Email</th><th>Token (First 20 chars)</th><th>Created</th><th>Expires</th></tr>";
                
                while ($pending = $pendingResult->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($pending['email']) . "</td>";
                    echo "<td>" . substr($pending['verification_token'], 0, 20) . "...</td>";
                    echo "<td>" . $pending['created_at'] . "</td>";
                    echo "<td>" . $pending['expires_at'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='warning'>";
                echo "<p>ℹ️ No pending verifications found.</p>";
                echo "</div>";
            }
            ?>
        </div>

        <!-- Show mock email log -->
        <div class="section">
            <h3>📨 Mock Email Log</h3>
            <p>Since XAMPP email is not configured, emails are logged instead:</p>
            
            <?php
            if (file_exists('email_log.txt')) {
                $emailLog = file_get_contents('email_log.txt');
                if (!empty($emailLog)) {
                    echo "<div class='email-content'>";
                    echo "<pre>" . htmlspecialchars($emailLog) . "</pre>";
                    echo "</div>";
                    
                    echo "<a href='?clear_log=1' class='button' onclick=\"return confirm('Clear email log?')\">🗑️ Clear Log</a>";
                } else {
                    echo "<div class='warning'><p>📝 Email log is empty.</p></div>";
                }
            } else {
                echo "<div class='warning'><p>📝 No email log file found.</p></div>";
            }
            
            // Handle clear log
            if (isset($_GET['clear_log'])) {
                if (file_exists('email_log.txt')) {
                    unlink('email_log.txt');
                    echo "<script>alert('Email log cleared!'); window.location.href='email_debug.php';</script>";
                }
            }
            ?>
        </div>

        <!-- Quick Actions -->
        <div class="section success">
            <h3>🚀 Quick Actions</h3>
            <p>Use these links to test the system:</p>
            
            <a href="signup_with_verification.php" class="button">📧 Test Signup Page</a>
            <a href="test_form_submission.php" class="button success">🧪 Test Form Submission</a>
            <a href="?" class="button">🔄 Refresh Debug Info</a>
        </div>

        <!-- Instructions -->
        <div class="section warning">
            <h3>💡 How to Use</h3>
            <ol>
                <li><strong>Register:</strong> Go to signup page and fill the form</li>
                <li><strong>Get OTP:</strong> Check the "Recent OTP Codes" section above for your OTP</li>
                <li><strong>Verify:</strong> Enter the OTP code in the verification form</li>
                <li><strong>Success:</strong> Your account will be created and you'll be redirected</li>
            </ol>
            
            <div style="background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <strong>✅ Email verification is working with mock service!</strong><br>
                In production, replace MockEmailService with real email configuration.
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);
        
        console.log('📧 Email debug page loaded - Auto-refresh in 30 seconds');
    </script>
</body>
</html>
