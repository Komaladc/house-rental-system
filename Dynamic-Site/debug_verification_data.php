<?php
// Debug verification data
include "config/config.php";

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "<h1>Verification Data Debug</h1>";

echo "<h2>1. Users in tbl_user (last 10)</h2>";
$result = $mysqli->query("SELECT user_id, fname, lname, username, email, level, status, created_at FROM tbl_user ORDER BY user_id DESC LIMIT 10");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Level</th><th>Status</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $levelText = ($row['level'] == 1) ? "Seeker" : (($row['level'] == 2) ? "Owner" : "Agent");
        echo "<tr>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['fname']} {$row['lname']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['level']} ({$levelText})</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>2. Verification Records in tbl_user_verification</h2>";
$result = $mysqli->query("SELECT * FROM tbl_user_verification ORDER BY created_at DESC LIMIT 10");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Username</th><th>Email</th><th>Level</th><th>Citizenship ID</th><th>Status</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $levelText = ($row['user_level'] == 1) ? "Seeker" : (($row['user_level'] == 2) ? "Owner" : "Agent");
        echo "<tr>";
        echo "<td>{$row['verification_id']}</td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['user_level']} ({$levelText})</td>";
        echo "<td>{$row['citizenship_id']}</td>";
        echo "<td>{$row['verification_status']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No verification records found or error: " . $mysqli->error . "</p>";
}

echo "<h2>3. JOIN Query Used by verify_users.php</h2>";
$query = "SELECT 
    v.verification_id,
    v.user_id,
    u.firstName,
    u.lastName,
    u.userName,
    u.userEmail,
    u.cellNo,
    u.userLevel,
    v.citizenship_id,
    v.citizenship_front,
    v.citizenship_back,
    v.verification_status,
    v.submitted_at
FROM tbl_user_verification v
LEFT JOIN tbl_user u ON v.user_id = u.userId
WHERE v.verification_status = 'pending'
ORDER BY v.submitted_at DESC";

echo "<p><strong>Query:</strong> " . htmlspecialchars($query) . "</p>";

$result = $mysqli->query($query);
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Verification ID</th><th>User ID</th><th>Name</th><th>Username</th><th>Email</th><th>Phone</th><th>Level</th><th>Citizenship ID</th><th>Status</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $levelText = ($row['userLevel'] == 1) ? "Seeker" : (($row['userLevel'] == 2) ? "Owner" : "Agent");
        echo "<tr>";
        echo "<td>{$row['verification_id']}</td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['firstName']} {$row['lastName']}</td>";
        echo "<td>{$row['userName']}</td>";
        echo "<td>{$row['userEmail']}</td>";
        echo "<td>{$row['cellNo']}</td>";
        echo "<td>{$row['userLevel']} ({$levelText})</td>";
        echo "<td>{$row['citizenship_id']}</td>";
        echo "<td>{$row['verification_status']}</td>";
        echo "<td>{$row['submitted_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Error with JOIN query: " . $mysqli->error . "</p>";
}

echo "<h2>4. Check for Data Inconsistencies</h2>";
// Check if there are verification records without matching users
$result = $mysqli->query("SELECT v.* FROM tbl_user_verification v LEFT JOIN tbl_user u ON v.user_id = u.user_id WHERE u.user_id IS NULL");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: red;'><strong>⚠️ Found verification records without matching users:</strong></p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Verification ID</th><th>User ID</th><th>Username</th><th>Email</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['verification_id']}</td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: green;'>✅ All verification records have matching users</p>";
}

// Check if there are users with duplicate verification records
$result = $mysqli->query("SELECT user_id, COUNT(*) as count FROM tbl_user_verification GROUP BY user_id HAVING COUNT(*) > 1");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: red;'><strong>⚠️ Found users with multiple verification records:</strong></p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>User ID</th><th>Count</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['count']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: green;'>✅ No duplicate verification records found</p>";
}

$mysqli->close();
?>
