<?php
// Simple test of admin query without authentication
include "config/config.php";
include "lib/Database.php";

$db = new Database();

echo "<h1>Admin Query Test (No Auth)</h1>";

// Test the exact query from verify_users.php
$pendingQuery = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.cellNo, u.userAddress, u.userLevel, u.created_at as user_created
                FROM tbl_user_verification uv 
                JOIN tbl_user u ON uv.user_id = u.userId 
                WHERE uv.verification_status = 'pending' 
                ORDER BY uv.submitted_at ASC";

echo "<h2>Executing Admin Query:</h2>";
echo "<pre>" . htmlspecialchars($pendingQuery) . "</pre>";

$pendingUsers = $db->select($pendingQuery);

echo "<h2>Query Result:</h2>";
if ($pendingUsers) {
    echo "<p>✅ Query executed successfully</p>";
    echo "<p>Number of rows: <strong>{$pendingUsers->num_rows}</strong></p>";
    
    if ($pendingUsers->num_rows > 0) {
        echo "<h3>Pending Users Found:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Level</th><th>Type</th><th>Citizenship ID</th><th>Status</th></tr>";
        
        while ($user = $pendingUsers->fetch_assoc()) {
            $levelText = '';
            if ($user['userLevel'] == 1) $levelText = 'Property Seeker';
            else if ($user['userLevel'] == 2) $levelText = 'Property Owner';
            else if ($user['userLevel'] == 3) $levelText = 'Real Estate Agent';
            
            echo "<tr>";
            echo "<td>{$user['verification_id']}</td>";
            echo "<td>{$user['firstName']} {$user['lastName']}</td>";
            echo "<td>{$user['userEmail']}</td>";
            echo "<td>{$user['cellNo']}</td>";
            echo "<td>{$user['userLevel']} ({$levelText})</td>";
            echo "<td>{$user['user_type']}</td>";
            echo "<td>{$user['citizenship_id']}</td>";
            echo "<td>{$user['verification_status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No pending verification records found</p>";
        
        // Check if there are any verification records at all
        $allVerificationQuery = "SELECT COUNT(*) as total FROM tbl_user_verification";
        $totalResult = $db->select($allVerificationQuery);
        if ($totalResult) {
            $total = $totalResult->fetch_assoc();
            echo "<p>Total verification records in database: {$total['total']}</p>";
        }
        
        // Check if there are any with different statuses
        $statusQuery = "SELECT verification_status, COUNT(*) as count FROM tbl_user_verification GROUP BY verification_status";
        $statusResult = $db->select($statusQuery);
        if ($statusResult && $statusResult->num_rows > 0) {
            echo "<p>Verification status breakdown:</p>";
            echo "<ul>";
            while ($status = $statusResult->fetch_assoc()) {
                echo "<li>{$status['verification_status']}: {$status['count']}</li>";
            }
            echo "</ul>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Query failed</p>";
    echo "<p>Database error: " . $db->link->error . "</p>";
}

echo "<h2>Test Registration</h2>";
echo "<p>If no pending users are found, let's test the registration process:</p>";
echo "<a href='check_pending_verification.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none;'>Test Registration</a>";
echo " | ";
echo "<a href='signup_enhanced.php?account_type=agent' style='background: #28a745; color: white; padding: 10px; text-decoration: none;'>Sign Up as Agent</a>";
?>
