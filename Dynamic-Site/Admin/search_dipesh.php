<?php
include '../lib/Database.php';

echo "<h2>🔍 Search for Dipesh Tamang Agent</h2>";

$db = new Database();

// 1. Check if Dipesh Tamang exists in tbl_user
echo "<h3>1. Check tbl_user for Dipesh Tamang</h3>";
$query1 = "SELECT * FROM tbl_user WHERE firstName LIKE '%Dipesh%' OR lastName LIKE '%Tamang%' OR userEmail LIKE '%dipesh%'";
$result1 = $db->select($query1);

if ($result1 && $result1->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>User ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>User Type</th><th>User Level</th><th>Status</th><th>Created</th></tr>";
    while ($row = $result1->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['userId']) . "</td>";
        echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['lastName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userEmail']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userType']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userLevel']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No user found with name Dipesh Tamang</p>";
}

// 2. Check if there's a verification record for Dipesh
echo "<h3>2. Check tbl_user_verification for Dipesh</h3>";
$query2 = "SELECT v.*, u.firstName, u.lastName, u.userEmail 
           FROM tbl_user_verification v 
           LEFT JOIN tbl_user u ON v.user_id = u.userId 
           WHERE u.firstName LIKE '%Dipesh%' OR u.lastName LIKE '%Tamang%' OR v.email LIKE '%dipesh%'";
$result2 = $db->select($query2);

if ($result2 && $result2->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Verification ID</th><th>User ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Status</th><th>Submitted</th></tr>";
    while ($row = $result2->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['verification_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['lastName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userEmail'] ?? $row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['verification_status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['submitted_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No verification record found for Dipesh Tamang</p>";
}

// 3. Show all agent users
echo "<h3>3. All Agent Users (userType = 'agent')</h3>";
$query3 = "SELECT * FROM tbl_user WHERE userType = 'agent' ORDER BY created_at DESC";
$result3 = $db->select($query3);

if ($result3 && $result3->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>User ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>User Level</th><th>Status</th><th>Created</th></tr>";
    while ($row = $result3->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['userId']) . "</td>";
        echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['lastName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userEmail']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userLevel']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No agent users found</p>";
}

// 4. Show all pending verifications
echo "<h3>4. All Pending Verifications</h3>";
$query4 = "SELECT v.*, u.firstName, u.lastName, u.userEmail, u.userType, u.userLevel
           FROM tbl_user_verification v 
           LEFT JOIN tbl_user u ON v.user_id = u.userId 
           WHERE v.verification_status = 'pending'
           ORDER BY v.submitted_at DESC";
$result4 = $db->select($query4);

if ($result4 && $result4->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Ver ID</th><th>User ID</th><th>Name</th><th>Email</th><th>Type</th><th>Level</th><th>Status</th><th>Submitted</th></tr>";
    while ($row = $result4->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['verification_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
        echo "<td>" . htmlspecialchars(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? '')) . "</td>";
        echo "<td>" . htmlspecialchars($row['userEmail'] ?? $row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userType'] ?? $row['user_type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userLevel'] ?? $row['user_level']) . "</td>";
        echo "<td>" . htmlspecialchars($row['verification_status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['submitted_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ No pending verifications found</p>";
}

echo "<h3>🔗 Quick Actions</h3>";
echo "<p><a href='../signup_enhanced.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Create New Agent Test</a></p>";
?>
