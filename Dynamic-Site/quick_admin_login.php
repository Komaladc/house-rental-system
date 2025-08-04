<?php
session_start();

if (isset($_POST['login'])) {
    // Simple admin login for testing
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_name'] = 'Test Admin';
    
    echo "<p style='color: green;'>✅ Admin session created</p>";
    echo "<p><a href='Admin/verify_users.php'>Go to Verify Users</a></p>";
} else {
    echo "<h1>Quick Admin Login</h1>";
    echo "<form method='POST'>";
    echo "<input type='hidden' name='login' value='1'>";
    echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none;'>Login as Admin</button>";
    echo "</form>";
}

echo "<h2>Current Session:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>
