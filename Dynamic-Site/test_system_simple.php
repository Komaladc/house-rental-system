<!DOCTYPE html>
<html>
<head>
    <title>🧪 Email Verification System Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .test-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .success { color: #27ae60; background: #d5f4e6; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #e74c3c; background: #fdeaea; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #f39c12; background: #fef9e7; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #3498db; background: #e8f4fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .test-links { text-align: center; margin: 30px 0; }
        .test-links a {
            display: inline-block;
            padding: 15px 25px;
            margin: 10px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }
        .test-links a:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🧪 Email Verification System Test</h1>
        
        <div class="test-section">
            <h3>📋 System Status</h3>
            <?php
            try {
                // Test basic PHP functions
                if (function_exists('mail')) {
                    echo '<div class="success">✅ PHP mail() function available</div>';
                } else {
                    echo '<div class="error">❌ PHP mail() function not available</div>';
                }
                
                // Test database connection
                include_once 'config/config.php';
                include_once 'lib/Database.php';
                $db = new Database();
                
                if ($db && $db->link) {
                    echo '<div class="success">✅ Database connection successful</div>';
                } else {
                    echo '<div class="error">❌ Database connection failed</div>';
                }
                
                // Test required tables
                $tables = ['tbl_user', 'tbl_otp', 'tbl_pending_verification'];
                foreach ($tables as $table) {
                    $result = mysqli_query($db->link, "SHOW TABLES LIKE '$table'");
                    if ($result && mysqli_num_rows($result) > 0) {
                        echo '<div class="success">✅ Table ' . $table . ' exists</div>';
                    } else {
                        echo '<div class="error">❌ Table ' . $table . ' missing</div>';
                    }
                }
                
                // Test classes
                if (class_exists('EmailOTP')) {
                    echo '<div class="warning">⚠️ EmailOTP class already loaded (might cause conflicts)</div>';
                }
                
                include_once 'classes/EmailOTP.php';
                include_once 'classes/PreRegistrationVerification.php';
                
                echo '<div class="success">✅ All required classes loaded successfully</div>';
                
            } catch (Exception $e) {
                echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
            }
            ?>
        </div>
        
        <div class="test-section">
            <h3>📊 Database Statistics</h3>
            <?php
            try {
                // Count existing data
                $userCount = mysqli_fetch_assoc(mysqli_query($db->link, "SELECT COUNT(*) as count FROM tbl_user"))['count'];
                $otpCount = mysqli_fetch_assoc(mysqli_query($db->link, "SELECT COUNT(*) as count FROM tbl_otp"))['count'];
                $pendingCount = mysqli_fetch_assoc(mysqli_query($db->link, "SELECT COUNT(*) as count FROM tbl_pending_verification"))['count'];
                
                echo '<div class="info">📈 Total Users: ' . $userCount . '</div>';
                echo '<div class="info">📱 Total OTPs Generated: ' . $otpCount . '</div>';
                echo '<div class="info">⏳ Pending Verifications: ' . $pendingCount . '</div>';
                
            } catch (Exception $e) {
                echo '<div class="error">❌ Could not retrieve statistics: ' . $e->getMessage() . '</div>';
            }
            ?>
        </div>
        
        <div class="test-section">
            <h3>🎯 Test Registration Flow</h3>
            <div class="info">
                <strong>Testing Steps:</strong><br>
                1. Click "Test Registration" below<br>
                2. Fill out the form with a real email address<br>
                3. Check your email for the verification code<br>
                4. Enter the OTP to complete registration
            </div>
        </div>
        
        <div class="test-links">
            <a href="signup_with_verification.php">🆕 Test Registration</a>
            <a href="signin.php">🔑 Test Sign In</a>
            <a href="verify_registration.php">📧 Test Email Verification</a>
            <a href="test_email_system.php">🔧 Advanced System Test</a>
        </div>
        
        <div class="test-section">
            <h3>🔧 Quick Actions</h3>
            <p><strong>Clean up test data:</strong></p>
            <div class="info">
                To clean up test registrations, you can run:<br>
                <code>DELETE FROM tbl_pending_verification WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR);</code><br>
                <code>DELETE FROM tbl_otp WHERE expires_at < NOW();</code>
            </div>
        </div>
        
        <div class="test-section">
            <h3>📝 Recent Activity</h3>
            <?php
            try {
                // Show recent pending verifications
                $recentPending = mysqli_query($db->link, "SELECT email, created_at FROM tbl_pending_verification ORDER BY created_at DESC LIMIT 5");
                if ($recentPending && mysqli_num_rows($recentPending) > 0) {
                    echo '<strong>Recent Pending Verifications:</strong><br>';
                    while ($row = mysqli_fetch_assoc($recentPending)) {
                        echo '<div class="info">📧 ' . htmlspecialchars($row['email']) . ' - ' . $row['created_at'] . '</div>';
                    }
                } else {
                    echo '<div class="info">No pending verifications found</div>';
                }
                
                // Show recent OTPs
                $recentOTPs = mysqli_query($db->link, "SELECT email, purpose, created_at FROM tbl_otp ORDER BY created_at DESC LIMIT 5");
                if ($recentOTPs && mysqli_num_rows($recentOTPs) > 0) {
                    echo '<br><strong>Recent OTPs:</strong><br>';
                    while ($row = mysqli_fetch_assoc($recentOTPs)) {
                        echo '<div class="info">🔢 ' . htmlspecialchars($row['email']) . ' (' . $row['purpose'] . ') - ' . $row['created_at'] . '</div>';
                    }
                } else {
                    echo '<div class="info">No recent OTPs found</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="error">❌ Could not retrieve recent activity: ' . $e->getMessage() . '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
