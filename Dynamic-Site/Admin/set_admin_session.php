<?php
include "../inc/config.php";
include "../classes/Session.php";

// Set admin session variables that match the login system
Session::set("userlogin", true);  // Critical: This prevents redirect to signin
Session::set("userId", 1);
Session::set("userLevel", 3);
Session::set("userName", "admin");
Session::set("userFName", "Admin");
Session::set("userLName", "User");
Session::set("userEmail", "admin@houserental.com");

echo "<h2>✅ Admin Session Set Successfully</h2>";
echo "<p>Admin session has been created with proper session variables.</p>";
echo "<p>Session Variables Set:</p>";
echo "<ul>";
echo "<li>userlogin: " . (Session::get("userlogin") ? 'true' : 'false') . "</li>";
echo "<li>userId: " . Session::get("userId") . "</li>";
echo "<li>userLevel: " . Session::get("userLevel") . "</li>";
echo "<li>userName: " . Session::get("userName") . "</li>";
echo "</ul>";
echo "<p><a href='dashboard_agent.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Go to Dashboard</a></p>";
echo "<p><a href='user_verification.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Go to User Verification</a></p>";
?>
