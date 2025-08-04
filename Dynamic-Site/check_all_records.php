<?php
include "config/config.php";

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "<h1>All Verification Records</h1>";

$result = $mysqli->query("SELECT v.*, u.firstName, u.lastName, u.userEmail, u.userLevel 
                         FROM tbl_user_verification v 
                         LEFT JOIN tbl_user u ON v.user_id = u.userId 
                         ORDER BY v.verification_id DESC");

if ($result && $result->num_rows > 0) {
    echo "<p>Found {$result->num_rows} verification record(s):</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Name</th><th>Email (Verification)</th><th>Email (User)</th><th>Level</th><th>Type</th><th>Status</th><th>Submitted</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $levelText = '';
        if ($row['userLevel'] == 1) $levelText = 'Seeker';
        else if ($row['userLevel'] == 2) $levelText = 'Owner';
        else if ($row['userLevel'] == 3) $levelText = 'Agent';
        
        echo "<tr>";
        echo "<td>{$row['verification_id']}</td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>" . ($row['firstName'] ?? 'NULL') . " " . ($row['lastName'] ?? 'NULL') . "</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>" . ($row['userEmail'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['userLevel'] ?? 'NULL') . " ({$levelText})</td>";
        echo "<td>{$row['user_type']}</td>";
        echo "<td><strong>{$row['verification_status']}</strong></td>";
        echo "<td>{$row['submitted_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ No verification records found in database</p>";
}

echo "<h2>All Users (Recent 10)</h2>";
$result = $mysqli->query("SELECT * FROM tbl_user ORDER BY userId DESC LIMIT 10");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Level</th><th>Status</th><th>Verification Status</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $levelText = '';
        if ($row['userLevel'] == 1) $levelText = 'Seeker';
        else if ($row['userLevel'] == 2) $levelText = 'Owner';
        else if ($row['userLevel'] == 3) $levelText = 'Agent';
        
        echo "<tr>";
        echo "<td>{$row['userId']}</td>";
        echo "<td>{$row['firstName']} {$row['lastName']}</td>";
        echo "<td>{$row['userName']}</td>";
        echo "<td>{$row['userEmail']}</td>";
        echo "<td>{$row['userLevel']} ({$levelText})</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>" . ($row['verification_status'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found</p>";
}

$mysqli->close();
?>
