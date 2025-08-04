<?php
// Test OTP Verification and Admin Flow
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 OTP Verification and Admin Flow Test</h2>";
echo "<div style='font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5;'>";

try {
    include 'lib/Database.php';
    $db = new Database();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Test 1: Check if all required tables exist
    echo "<h3>1. 📋 Database Tables Check</h3>";
    $tables = ['tbl_user', 'tbl_otp', 'tbl_pending_verification', 'tbl_admin_users', 'tbl_admin_logs'];
    
    foreach ($tables as $table) {
        $checkTable = "SHOW TABLES LIKE '$table'";
        $tableExists = $db->select($checkTable);
        
        if ($tableExists && $tableExists->num_rows > 0) {
            echo "<p style='color: green;'>✅ $table exists</p>";
        } else {
            echo "<p style='color: red;'>❌ $table missing</p>";
        }
    }
    
    // Test 2: Check columns in tbl_user
    echo "<h3>2. 👤 User Table Structure</h3>";
    $userColumns = "SHOW COLUMNS FROM tbl_user";
    $columns = $db->select($userColumns);
    $columnNames = [];
    
    if ($columns) {
        while ($col = $columns->fetch_assoc()) {
            $columnNames[] = $col['Field'];
        }
    }
    
    $requiredColumns = ['verification_status', 'requires_verification', 'email_verified', 'document_verified', 'status'];
    foreach ($requiredColumns as $reqCol) {
        if (in_array($reqCol, $columnNames)) {
            echo "<p style='color: green;'>✅ $reqCol column exists</p>";
        } else {
            echo "<p style='color: red;'>❌ $reqCol column missing</p>";
        }
    }
    
    // Test 3: Check admin user
    echo "<h3>3. 🔐 Admin User Check</h3>";
    $adminQuery = "SELECT * FROM tbl_admin_users WHERE status = 'active'";
    $adminResult = $db->select($adminQuery);
    
    if ($adminResult && $adminResult->num_rows > 0) {
        $admin = $adminResult->fetch_assoc();
        echo "<p style='color: green;'>✅ Active admin user found: " . $admin['full_name'] . " (" . $admin['email'] . ")</p>";
        
        echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>🔑 Admin Login Credentials</h4>";
        echo "<strong>Login URL:</strong> <a href='admin/login.php' target='_blank'>admin/login.php</a><br>";
        echo "<strong>Username:</strong> " . $admin['username'] . "<br>";
        echo "<strong>Email:</strong> " . $admin['email'] . "<br>";
        echo "<strong>Default Password:</strong> admin123 (if using default)<br>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ No active admin user found</p>";
    }
    
    // Test 4: Check user verification flow
    echo "<h3>4. 📧 User Verification Flow Check</h3>";
    
    // Check for pending verifications
    $pendingQuery = "SELECT COUNT(*) as count FROM tbl_user WHERE verification_status = 'pending'";
    $pendingResult = $db->select($pendingQuery);
    
    if ($pendingResult) {
        $pending = $pendingResult->fetch_assoc();
        echo "<p style='color: orange;'>⏳ Users pending verification: " . $pending['count'] . "</p>";
    }
    
    // Check for users requiring verification
    $requiresVerQuery = "SELECT COUNT(*) as count FROM tbl_user WHERE requires_verification = 1";
    $requiresVerResult = $db->select($requiresVerQuery);
    
    if ($requiresVerResult) {
        $requiresVer = $requiresVerResult->fetch_assoc();
        echo "<p style='color: blue;'>📋 Users requiring verification: " . $requiresVer['count'] . "</p>";
    }
    
    // Test 5: Recent registrations
    echo "<h3>5. 📝 Recent Registrations</h3>";
    $recentQuery = "SELECT firstName, lastName, userEmail, userLevel, verification_status, requires_verification, created_at 
                   FROM tbl_user 
                   ORDER BY userId DESC 
                   LIMIT 5";
    $recentResult = $db->select($recentQuery);
    
    if ($recentResult && $recentResult->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr style='background: #ddd;'><th>Name</th><th>Email</th><th>Level</th><th>Verification Status</th><th>Requires Verification</th><th>Created</th></tr>";
        
        while ($user = $recentResult->fetch_assoc()) {
            $levelText = '';
            switch($user['userLevel']) {
                case 1: $levelText = 'Property Seeker'; break;
                case 2: $levelText = 'Property Owner'; break;
                case 3: $levelText = 'Real Estate Agent'; break;
                default: $levelText = 'Unknown'; break;
            }
            
            $statusColor = '';
            switch($user['verification_status']) {
                case 'pending': $statusColor = 'orange'; break;
                case 'verified': $statusColor = 'green'; break;
                case 'rejected': $statusColor = 'red'; break;
                default: $statusColor = 'black'; break;
            }
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) . "</td>";
            echo "<td>" . htmlspecialchars($user['userEmail']) . "</td>";
            echo "<td>" . $levelText . "</td>";
            echo "<td style='color: $statusColor;'>" . ucfirst($user['verification_status']) . "</td>";
            echo "<td>" . ($user['requires_verification'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . ($user['created_at'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: gray;'>📝 No recent registrations found</p>";
    }
    
    // Test 6: System recommendations
    echo "<h3>6. 💡 System Recommendations</h3>";
    
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>🔄 Testing Steps</h4>";
    echo "<ol>";
    echo "<li><strong>Test Property Seeker Registration:</strong><br>";
    echo "   - Go to <a href='signup_enhanced.php' target='_blank'>signup_enhanced.php</a><br>";
    echo "   - Select 'Property Seeker' and complete registration<br>";
    echo "   - Should work without admin verification</li>";
    echo "<li><strong>Test Owner/Agent Registration:</strong><br>";
    echo "   - Go to <a href='signup_enhanced.php' target='_blank'>signup_enhanced.php</a><br>";
    echo "   - Select 'Property Owner' or 'Real Estate Agent'<br>";
    echo "   - Upload citizenship documents<br>";
    echo "   - Should require admin verification</li>";
    echo "<li><strong>Test Admin Verification:</strong><br>";
    echo "   - Login to <a href='admin/login.php' target='_blank'>admin dashboard</a><br>";
    echo "   - Go to user verification section<br>";
    echo "   - Approve/reject pending users</li>";
    echo "<li><strong>Test Sign In Restrictions:</strong><br>";
    echo "   - Try signing in with pending owner/agent account<br>";
    echo "   - Should show verification pending message</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>⚠️ Important Notes</h4>";
    echo "<ul>";
    echo "<li>Owner and Agent accounts require admin verification after OTP verification</li>";
    echo "<li>Property Seeker accounts can sign in immediately after OTP verification</li>";
    echo "<li>Rejected accounts cannot sign in until re-verified</li>";
    echo "<li>All accounts require email OTP verification first</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='margin: 20px 0; padding: 15px; background: #d4edda; border-radius: 5px;'>";
    echo "<h4>🚀 Quick Actions</h4>";
    echo "<a href='signup_enhanced.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📝 Test Signup</a>";
    echo "<a href='signin.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔓 Test Sign In</a>";
    echo "<a href='admin/login.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔐 Admin Login</a>";
    echo "<a href='admin/dashboard.php' style='background: #fd7e14; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📊 Admin Dashboard</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";
?>
