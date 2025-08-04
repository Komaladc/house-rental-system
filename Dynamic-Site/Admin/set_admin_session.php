<?php
session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;
$_SESSION['admin_username'] = 'admin';

echo "<h2>Admin Session Set</h2>";
echo "<p>Admin session has been created. You can now access admin pages.</p>";
echo "<p><a href='verify_users.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to User Verification</a></p>";
?>
