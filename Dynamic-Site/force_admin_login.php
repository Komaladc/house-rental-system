<?php
session_start();
include "lib/Database.php";

$db = new Database();

echo "<h2>🔐 Force Admin Login</h2>";
echo "<div style='font-family: Arial; padding: 20px; background: #f5f5f5;'>";

// Check if admin user exists
$adminQuery = "SELECT * FROM tbl_admin_users WHERE email = 'admin@propertynepal.com' AND status = 'active'";
$adminResult = $db->select($adminQuery);

if ($adminResult && $adminResult->num_rows > 0) {
    $admin = $adminResult->fetch_assoc();
    
    echo "<p style='color: green;'>✅ Admin user found: " . $admin['full_name'] . "</p>";
    
    // Set session variables for force login
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_name'] = $admin['full_name'];
    
    echo "<p style='color: green; font-weight: bold;'>🎉 Admin session created successfully!</p>";
    
    // Log the forced login
    $logQuery = "INSERT INTO tbl_admin_logs (admin_id, action, description, ip_address, user_agent) 
                 VALUES ('" . $admin['id'] . "', 'force_login', 'Admin force login via script', '" . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "', '" . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . "')";
    $db->insert($logQuery);
    
    echo "<p><strong>Session Data:</strong></p>";
    echo "<ul>";
    echo "<li>Admin ID: " . $_SESSION['admin_id'] . "</li>";
    echo "<li>Username: " . $_SESSION['admin_username'] . "</li>";
    echo "<li>Email: " . $_SESSION['admin_email'] . "</li>";
    echo "<li>Name: " . $_SESSION['admin_name'] . "</li>";
    echo "</ul>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='admin/dashboard.php' style='background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px; font-weight: bold;'>🚀 Go to Dashboard</a>";
    echo "<a href='admin/verify_users.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>✅ Verify Users</a>";
    echo "<a href='admin/manage_users.php' style='background: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>👥 Manage Users</a>";
    echo "</div>";
    
} else {
    echo "<p style='color: red;'>❌ Admin user not found! Please run the database setup first.</p>";
    echo "<a href='setup_complete_database.php' style='background: #dc3545; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>🔧 Setup Database</a>";
}

echo "</div>";
?>
