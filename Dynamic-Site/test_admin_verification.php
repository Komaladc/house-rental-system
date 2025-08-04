<?php
session_start();
require_once 'config/config.php';
require_once 'lib/Database.php';

// Simulate admin login for testing
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;
$_SESSION['admin_username'] = 'admin';

echo "<h2>Admin Verification Test Page</h2>";

try {
    $db = new Database();
    
    echo "<h3>1. Direct Query - Pending Verifications</h3>";
    $query = "SELECT v.*, u.username, u.email, u.full_name, u.phone 
              FROM tbl_user_verification v 
              LEFT JOIN tbl_user u ON v.user_id = u.id 
              WHERE v.verification_status = 'pending'
              ORDER BY v.created_at DESC";
    
    $result = $db->select($query);
    
    if (!$result || $result->num_rows == 0) {
        echo "<p style='color: red;'>No pending verifications found.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Verification ID</th><th>User ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>User Type</th><th>Created At</th><th>Status</th></tr>";
        while ($user = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['verification_id'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($user['user_id'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($user['username'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($user['email'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($user['full_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($user['user_type'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($user['created_at'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($user['verification_status'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>2. All Verification Records</h3>";
    $query_all = "SELECT v.*, u.username, u.email, u.full_name 
                  FROM tbl_user_verification v 
                  LEFT JOIN tbl_user u ON v.user_id = u.id 
                  ORDER BY v.created_at DESC LIMIT 10";
    
    $result_all = $db->select($query_all);
    
    if (!$result_all || $result_all->num_rows == 0) {
        echo "<p style='color: red;'>No verification records found.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Verification ID</th><th>User ID</th><th>Username</th><th>Email</th><th>User Type</th><th>Status</th><th>Created</th></tr>";
        while ($ver = $result_all->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($ver['verification_id'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($ver['user_id'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($ver['username'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($ver['email'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($ver['user_type'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($ver['verification_status'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($ver['created_at'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>3. Link to Official Admin Verification Page</h3>";
    echo "<p><a href='Admin/verify_users.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Verification Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
