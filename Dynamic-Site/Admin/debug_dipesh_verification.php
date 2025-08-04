<?php
include '../lib/Database.php';

echo "<h2>🔍 Debug Dipesh Tamang Verification Issue</h2>";

$db = new Database();

// 1. Check Dipesh in tbl_user
echo "<h3>1. Dipesh Tamang in tbl_user</h3>";
$query1 = "SELECT * FROM tbl_user WHERE firstName LIKE '%Dipesh%' AND lastName LIKE '%Tamang%'";
$result1 = $db->select($query1);

if ($result1 && $result1->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>User ID</th><th>Name</th><th>Email</th><th>Type</th><th>Level</th><th>Status</th><th>Verification Status</th></tr>";
    while ($row = $result1->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['userId']) . "</td>";
        echo "<td>" . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userEmail']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userType']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userLevel']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['verification_status'] ?? 'NULL') . "</td>";
        echo "</tr>";
        $dipeshUserId = $row['userId'];
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Dipesh Tamang not found in tbl_user</p>";
    exit;
}

// 2. Check Dipesh in tbl_user_verification
echo "<h3>2. Dipesh Tamang in tbl_user_verification</h3>";
$query2 = "SELECT * FROM tbl_user_verification WHERE user_id = $dipeshUserId";
$result2 = $db->select($query2);

if ($result2 && $result2->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Verification ID</th><th>User ID</th><th>Email</th><th>Username</th><th>User Type</th><th>User Level</th><th>Status</th><th>Submitted At</th></tr>";
    while ($row = $result2->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['verification_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_level']) . "</td>";
        echo "<td>" . htmlspecialchars($row['verification_status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['submitted_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No verification record found for Dipesh Tamang (User ID: $dipeshUserId)</p>";
    echo "<p>Let me create the verification record...</p>";
    
    // Create verification record
    $insertVerification = "INSERT INTO tbl_user_verification 
        (user_id, email, username, user_level, user_type, verification_status, submitted_at) 
        VALUES 
        ($dipeshUserId, 'dipesh.tamang@example.com', 'dipesh_tamang', 3, 'agent', 'pending', NOW())";
    
    if ($db->insert($insertVerification)) {
        echo "<p style='color: green;'>✅ Verification record created!</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create verification record</p>";
    }
}

// 3. Test the exact query used in verify_users.php
echo "<h3>3. Admin Panel Query Test</h3>";
$adminQuery = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.cellNo, u.userAddress, u.userLevel, u.created_at as user_created
                FROM tbl_user_verification uv 
                JOIN tbl_user u ON uv.user_id = u.userId 
                WHERE uv.verification_status = 'pending' 
                ORDER BY uv.submitted_at ASC";

echo "<p><strong>Query:</strong> <code style='background: #f8f9fa; padding: 5px;'>$adminQuery</code></p>";

$result3 = $db->select($adminQuery);

if ($result3 && $result3->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Verification ID</th><th>User ID</th><th>Name</th><th>Email</th><th>User Level</th><th>Status</th><th>Submitted</th></tr>";
    $dipeshFound = false;
    while ($row = $result3->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['verification_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userEmail']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userLevel']) . "</td>";
        echo "<td>" . htmlspecialchars($row['verification_status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['submitted_at']) . "</td>";
        echo "</tr>";
        
        if (stripos($row['firstName'] . ' ' . $row['lastName'], 'Dipesh Tamang') !== false) {
            $dipeshFound = true;
        }
    }
    echo "</table>";
    
    if ($dipeshFound) {
        echo "<p style='color: green;'>✅ Dipesh Tamang found in admin query results!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Dipesh Tamang not found in admin query results</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No pending verifications found</p>";
}

// 4. Check all verification records
echo "<h3>4. All Verification Records</h3>";
$allQuery = "SELECT v.*, u.firstName, u.lastName FROM tbl_user_verification v LEFT JOIN tbl_user u ON v.user_id = u.userId ORDER BY v.submitted_at DESC";
$result4 = $db->select($allQuery);

if ($result4) {
    echo "<p>Total verification records: " . $result4->num_rows . "</p>";
}

echo "<h3>🔗 Actions</h3>";
echo "<p>";
echo "<a href='verify_users.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔄 Refresh Admin Panel</a>";
echo "<a href='../create_dipesh_agent.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 Recreate Agent</a>";
echo "</p>";
?>
