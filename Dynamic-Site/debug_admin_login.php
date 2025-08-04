<?php
// Debug admin login issues
include "lib/Database.php";

$db = new Database();

echo "<h2>🔍 Admin Login Debug Information</h2>";
echo "<div style='font-family: Arial; padding: 20px; background: #f5f5f5;'>";

// Check if tbl_admin_users table exists and has data
echo "<h3>1. Checking tbl_admin_users table</h3>";
$checkTable = "SHOW TABLES LIKE 'tbl_admin_users'";
$tableExists = $db->select($checkTable);

if ($tableExists && $tableExists->num_rows > 0) {
    echo "<p style='color: green;'>✅ tbl_admin_users table exists</p>";
    
    // Check admin users
    $adminQuery = "SELECT * FROM tbl_admin_users";
    $adminUsers = $db->select($adminQuery);
    
    if ($adminUsers && $adminUsers->num_rows > 0) {
        echo "<p style='color: green;'>✅ Admin users found: " . $adminUsers->num_rows . "</p>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #ddd;'><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Status</th><th>Created</th></tr>";
        while ($admin = $adminUsers->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $admin['id'] . "</td>";
            echo "<td>" . $admin['username'] . "</td>";
            echo "<td>" . $admin['email'] . "</td>";
            echo "<td>" . $admin['full_name'] . "</td>";
            echo "<td>" . $admin['status'] . "</td>";
            echo "<td>" . $admin['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No admin users found</p>";
        
        // Create default admin user
        echo "<p>🔄 Creating default admin user...</p>";
        $createAdmin = "
            INSERT INTO tbl_admin_users (username, email, password, full_name, role, status) 
            VALUES ('admin', 'admin@propertynepal.com', '" . md5('admin123') . "', 'System Administrator', 'super_admin', 'active')
        ";
        if ($db->insert($createAdmin)) {
            echo "<p style='color: green;'>✅ Default admin user created successfully</p>";
        } else {
            echo "<p style='color: red;'>❌ Error creating admin user: " . $db->link->error . "</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ tbl_admin_users table does not exist</p>";
    echo "<p>🔄 Please run the complete database fix script first</p>";
}

// Test login credentials
echo "<h3>2. Testing Login Credentials</h3>";
$testUsername = 'admin@propertynepal.com';
$testPassword = md5('admin123');

echo "<p><strong>Testing credentials:</strong></p>";
echo "<p>Username: $testUsername</p>";
echo "<p>Password Hash: $testPassword</p>";

$loginQuery = "SELECT * FROM tbl_admin_users WHERE (username = '$testUsername' OR email = '$testUsername') AND password = '$testPassword' AND status = 'active'";
echo "<p><strong>Login Query:</strong><br><code>$loginQuery</code></p>";

$loginResult = $db->select($loginQuery);
if ($loginResult && $loginResult->num_rows > 0) {
    echo "<p style='color: green;'>✅ Login credentials are valid</p>";
    $admin = $loginResult->fetch_assoc();
    echo "<p><strong>Found admin:</strong> " . $admin['full_name'] . " (" . $admin['email'] . ")</p>";
} else {
    echo "<p style='color: red;'>❌ Login credentials are invalid</p>";
    echo "<p>Error: " . $db->link->error . "</p>";
}

// Check PHP sessions
echo "<h3>3. Checking PHP Session Configuration</h3>";
session_start();
echo "<p><strong>Session Status:</strong> " . (session_status() == PHP_SESSION_ACTIVE ? "✅ Active" : "❌ Inactive") . "</p>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Save Path:</strong> " . session_save_path() . "</p>";

// Check current session data
if (!empty($_SESSION)) {
    echo "<p><strong>Current Session Data:</strong></p>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
} else {
    echo "<p>No session data found</p>";
}

// Manual login test
echo "<h3>4. Manual Login Test</h3>";
if (isset($_GET['test_login'])) {
    $username = 'admin@propertynepal.com';
    $password = md5('admin123');
    
    $query = "SELECT * FROM tbl_admin_users WHERE (username = '$username' OR email = '$username') AND password = '$password' AND status = 'active'";
    $result = $db->select($query);
    
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        // Set session variables
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_name'] = $admin['full_name'];
        
        echo "<p style='color: green;'>✅ Manual login successful!</p>";
        echo "<p><a href='admin/dashboard.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Go to Dashboard</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Manual login failed</p>";
    }
} else {
    echo "<p><a href='?test_login=1' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test Manual Login</a></p>";
}

// Check login.php file
echo "<h3>5. Login Page Issues</h3>";
if (file_exists('admin/login.php')) {
    echo "<p style='color: green;'>✅ admin/login.php file exists</p>";
} else {
    echo "<p style='color: red;'>❌ admin/login.php file not found</p>";
}

echo "<h3>6. Quick Fix Options</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='complete_database_fix.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔧 Fix Database</a>";
echo "<a href='admin/login.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔐 Try Login Again</a>";
echo "<a href='?test_login=1' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Force Login</a>";
echo "</div>";

echo "</div>";
?>
