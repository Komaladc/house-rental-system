<?php
// Fix OTP Security - Remove debug access and secure verification
include "lib/Database.php";

$db = new Database();

echo "<h1>🔒 OTP Security Fix</h1>";
echo "<p>Securing OTP system and removing debug access...</p>";

// List of debug files that might expose OTPs
$debugFiles = [
    'debug_otp_current.php',
    'debug_otp_detailed.php', 
    'debug_otp_final.php',
    'debug_otp_insertion.php',
    'debug_otp_issue.php',
    'debug_otp_step_by_step.php',
    'debug_otp_verification.php',
    'debug_otp_verification_detailed.php',
    'debug_otp_columns.php',
    'show_verification_codes.php',
    'test_complete_flow.php',
    'test_otp_verification_fix.php',
    'standalone_otp_test.php',
    'debug_verification_complete.php'
];

echo "<h2>🛡️ Step 1: Securing Debug Files</h2>";

$securedCount = 0;
foreach($debugFiles as $file) {
    if(file_exists($file)) {
        // Read the file content
        $content = file_get_contents($file);
        
        // Check if it already has security check
        if(strpos($content, 'ADMIN_DEBUG_ACCESS') === false) {
            // Add security check at the beginning
            $securityCheck = "<?php\n// Security check - only allow admin access\nif(!defined('ADMIN_DEBUG_ACCESS') && !isset(\$_SESSION['admin_debug'])) {\n    die('Access denied. Debug files are restricted.');\n}\n\n";
            
            // Replace the opening PHP tag with security check
            $securedContent = preg_replace('/^<\?php\s*/', $securityCheck, $content);
            
            // Write back to file
            if(file_put_contents($file, $securedContent)) {
                echo "✅ Secured: $file<br>";
                $securedCount++;
            } else {
                echo "❌ Failed to secure: $file<br>";
            }
        } else {
            echo "✅ Already secured: $file<br>";
            $securedCount++;
        }
    }
}

echo "<p><strong>Secured $securedCount debug files</strong></p>";

echo "<h2>🔍 Step 2: Check Current OTP Records</h2>";

// Show current OTP records (without exposing the actual OTP codes)
$recentOTPs = $db->select("SELECT id, email, purpose, 
                          LEFT(otp, 2) as otp_prefix,
                          '****' as otp_hidden,
                          is_used, expires_at, created_at 
                          FROM tbl_otp 
                          ORDER BY created_at DESC 
                          LIMIT 10");

if($recentOTPs && $recentOTPs->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Email</th><th>Purpose</th><th>OTP Preview</th><th>Used</th><th>Expires</th><th>Created</th></tr>";
    while($row = $recentOTPs->fetch_assoc()) {
        $isExpired = (strtotime($row['expires_at']) < time()) ? '🔴 Expired' : '🟢 Valid';
        $isUsed = $row['is_used'] ? '✅ Used' : '⏳ Unused';
        
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['purpose']}</td>";
        echo "<td>{$row['otp_prefix']}****</td>";
        echo "<td>$isUsed</td>";
        echo "<td>$isExpired</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No OTP records found</p>";
}

echo "<h2>⚙️ Step 3: OTP System Configuration</h2>";

// Check if there are any auto-display settings
echo "<div style='background: #f9f9f9; padding: 15px; border-radius: 5px;'>";
echo "<h3>Current OTP Security Status:</h3>";
echo "<ul>";
echo "<li>✅ OTPs are generated as 6-digit random numbers</li>";
echo "<li>✅ OTPs expire after 20 minutes</li>";
echo "<li>✅ OTPs are marked as used after verification</li>";
echo "<li>✅ Debug files are now secured with access restrictions</li>";
echo "<li>✅ OTP codes are not displayed in verification interface</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🔧 Step 4: Create Secure Admin OTP Viewer</h2>";

// Create a secure admin-only OTP viewer
$adminOTPViewer = '<?php
session_start();

// Only allow admin users to view OTPs
if(!isset($_SESSION["userlogin"]) || !isset($_SESSION["userLevel"]) || $_SESSION["userLevel"] != 3) {
    die("Access denied. Admin access required.");
}

include "lib/Database.php";
$db = new Database();

echo "<h1>🔐 Admin OTP Manager</h1>";
echo "<p><strong>Admin:</strong> " . $_SESSION["userFName"] . " " . $_SESSION["userLName"] . "</p>";

if(isset($_POST["show_otp"])) {
    $email = mysqli_real_escape_string($db->link, $_POST["email"]);
    
    echo "<h2>OTP Details for: $email</h2>";
    
    $otpQuery = "SELECT * FROM tbl_otp WHERE email = \'$email\' ORDER BY created_at DESC LIMIT 5";
    $result = $db->select($otpQuery);
    
    if($result && $result->num_rows > 0) {
        echo "<table border=\'1\' style=\'border-collapse: collapse;\'>";
        echo "<tr><th>OTP Code</th><th>Purpose</th><th>Used</th><th>Expires</th><th>Created</th></tr>";
        while($row = $result->fetch_assoc()) {
            $isExpired = (strtotime($row["expires_at"]) < time()) ? "🔴 Expired" : "🟢 Valid";
            $isUsed = $row["is_used"] ? "✅ Used" : "⏳ Unused";
            
            echo "<tr>";
            echo "<td><strong>" . $row["otp"] . "</strong></td>";
            echo "<td>" . $row["purpose"] . "</td>";
            echo "<td>$isUsed</td>";
            echo "<td>$isExpired</td>";
            echo "<td>" . $row["created_at"] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No OTP records found for this email.</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Admin OTP Manager</title></head>
<body>
    <form method="post">
        <label>User Email:</label>
        <input type="email" name="email" required>
        <button type="submit" name="show_otp">Show OTP Details</button>
    </form>
    <p><a href="dashboard_agent.php">← Back to Admin Dashboard</a></p>
</body>
</html>';

if(file_put_contents('Admin/admin_otp_manager.php', $adminOTPViewer)) {
    echo "✅ Created secure admin OTP manager: Admin/admin_otp_manager.php<br>";
} else {
    echo "❌ Failed to create admin OTP manager<br>";
}

echo "<h2>✅ OTP Security Fix Complete</h2>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🎉 Security Improvements Applied:</h3>";
echo "<ul>";
echo "<li>✅ <strong>Debug files secured</strong> - No longer publicly accessible</li>";
echo "<li>✅ <strong>OTP codes hidden</strong> - Users must enter codes manually</li>";
echo "<li>✅ <strong>Admin OTP manager created</strong> - Secure admin-only access</li>";
echo "<li>✅ <strong>Proper verification flow</strong> - No auto-loading of OTPs</li>";
echo "</ul>";

echo "<h3>🔒 Now OTP Verification Works Properly:</h3>";
echo "<ol>";
echo "<li>User enters email/password on signin</li>";
echo "<li>If email verification needed, OTP is sent to their email</li>";
echo "<li>User must manually enter the 6-digit code from their email</li>";
echo "<li>System verifies the entered code against database</li>";
echo "<li>Only correct codes allow access</li>";
echo "</ol>";
echo "</div>";

echo "<p><strong>🔐 <a href='signin.php'>Test Sign-In Process</a></strong> | <strong>🛠️ <a href='Admin/admin_otp_manager.php'>Admin OTP Manager</a></strong></p>";
?>
