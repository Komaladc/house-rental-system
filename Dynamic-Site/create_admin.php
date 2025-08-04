<?php
// Admin User Creation Script - Run this once to create admin user
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "lib/Database.php";
$db = new Database();

// Admin credentials
$adminUsername = 'admin';
$adminEmail = 'admin@propertynepal.com';
$adminPassword = 'admin123'; // Change this to a secure password

// Check if admin already exists
$checkAdmin = "SELECT * FROM tbl_user WHERE userName = '$adminUsername' OR userEmail = '$adminEmail'";
$existingAdmin = $db->select($checkAdmin);

if ($existingAdmin && $existingAdmin->num_rows > 0) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>⚠️ Admin user already exists!</div>";
    
    // Show existing admin details
    $adminData = $existingAdmin->fetch_assoc();
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Existing Admin Details:</h4>";
    echo "<strong>Username:</strong> " . $adminData['userName'] . "<br>";
    echo "<strong>Email:</strong> " . $adminData['userEmail'] . "<br>";
    echo "<strong>Level:</strong> " . $adminData['userLevel'] . " (1 = Admin)<br>";
    echo "<strong>Status:</strong> " . ($adminData['status'] == 1 ? 'Active' : 'Inactive') . "<br>";
    echo "</div>";
} else {
    // Create admin user
    $hashedPassword = md5($adminPassword);
    
    $createAdmin = "INSERT INTO tbl_user (firstName, lastName, userName, userEmail, email_verified, cellNo, userAddress, userPass, confPass, userLevel, status, created_at) 
                   VALUES ('Admin', 'User', '$adminUsername', '$adminEmail', 1, '9800000000', 'Kathmandu, Nepal', '$hashedPassword', '$hashedPassword', 1, 1, NOW())";
    
    if ($db->insert($createAdmin)) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>✅ Admin User Created Successfully!</h3>";
        echo "<strong>Username:</strong> $adminUsername<br>";
        echo "<strong>Email:</strong> $adminEmail<br>";
        echo "<strong>Password:</strong> $adminPassword<br>";
        echo "<strong>Level:</strong> 1 (Admin)<br>";
        echo "<br><strong>⚠️ Important:</strong> Please change the password after first login!";
        echo "</div>";
        
        echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>📋 Next Steps:</h4>";
        echo "<ol>";
        echo "<li>Login to admin dashboard: <a href='admin/login.php'>admin/login.php</a></li>";
        echo "<li>Change the default password</li>";
        echo "<li>Start verifying pending users</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>❌ Failed to create admin user!</div>";
    }
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    h3, h4 { color: #333; }
    a { color: #007cba; text-decoration: none; }
    a:hover { text-decoration: underline; }
</style>

<h2>🔧 Admin User Setup</h2>
<p>This script creates the main admin user for the Property Nepal system.</p>
