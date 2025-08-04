<?php
include "config/config.php";

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "<h1>Debug JOIN Issue</h1>";

echo "<h2>1. All Users (Recent 10)</h2>";
$result = $mysqli->query("SELECT userId, firstName, lastName, userEmail, userLevel FROM tbl_user ORDER BY userId DESC LIMIT 10");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>userId</th><th>Name</th><th>Email</th><th>Level</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['userId']}</td>";
        echo "<td>{$row['firstName']} {$row['lastName']}</td>";
        echo "<td>{$row['userEmail']}</td>";
        echo "<td>{$row['userLevel']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>2. All Verification Records</h2>";
$result = $mysqli->query("SELECT * FROM tbl_user_verification ORDER BY verification_id DESC LIMIT 10");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>verification_id</th><th>user_id</th><th>email</th><th>user_type</th><th>citizenship_id</th><th>verification_status</th><th>submitted_at</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['verification_id']}</td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['user_type']}</td>";
        echo "<td>{$row['citizenship_id']}</td>";
        echo "<td>{$row['verification_status']}</td>";
        echo "<td>{$row['submitted_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>3. Testing JOIN with LEFT JOIN</h2>";
$query = "SELECT v.*, u.firstName, u.lastName, u.userEmail 
          FROM tbl_user_verification v 
          LEFT JOIN tbl_user u ON v.user_id = u.userId 
          ORDER BY v.verification_id DESC";
$result = $mysqli->query($query);
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>verification_id</th><th>user_id</th><th>verification_email</th><th>user_firstName</th><th>user_lastName</th><th>user_userEmail</th><th>Match?</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $match = ($row['firstName'] !== null) ? "✅" : "❌";
        echo "<tr>";
        echo "<td>{$row['verification_id']}</td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>" . ($row['firstName'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['lastName'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['userEmail'] ?? 'NULL') . "</td>";
        echo "<td>{$match}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>4. Check for Orphaned Verification Records</h2>";
$query = "SELECT v.* FROM tbl_user_verification v 
          LEFT JOIN tbl_user u ON v.user_id = u.userId 
          WHERE u.userId IS NULL";
$result = $mysqli->query($query);
if ($result && $result->num_rows > 0) {
    echo "<p style='color: red;'>Found verification records without matching users:</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>verification_id</th><th>user_id</th><th>email</th><th>user_type</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['verification_id']}</td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['user_type']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: green;'>✅ All verification records have matching users</p>";
}

echo "<h2>5. Check Pending Status Records</h2>";
$query = "SELECT v.*, u.firstName, u.lastName, u.userEmail 
          FROM tbl_user_verification v 
          LEFT JOIN tbl_user u ON v.user_id = u.userId 
          WHERE v.verification_status = 'pending'
          ORDER BY v.verification_id DESC";
$result = $mysqli->query($query);
if ($result && $result->num_rows > 0) {
    echo "<p>Found {$result->num_rows} pending verification(s):</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>verification_id</th><th>user_id</th><th>Name</th><th>Email</th><th>Type</th><th>Status</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['verification_id']}</td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>" . ($row['firstName'] ?? 'NULL') . " " . ($row['lastName'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['userEmail'] ?? 'NULL') . "</td>";
        echo "<td>{$row['user_type']}</td>";
        echo "<td>{$row['verification_status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ No pending verification records found</p>";
}

echo "<h2>6. Test the Exact Admin Query</h2>";
$adminQuery = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.cellNo, u.userAddress, u.userLevel, u.created_at as user_created
                FROM tbl_user_verification uv 
                JOIN tbl_user u ON uv.user_id = u.userId 
                WHERE uv.verification_status = 'pending' 
                ORDER BY uv.submitted_at ASC";

echo "<p><strong>Admin Query:</strong></p>";
echo "<pre>" . htmlspecialchars($adminQuery) . "</pre>";

$result = $mysqli->query($adminQuery);
if ($result) {
    echo "<p>Query executed successfully. Rows returned: <strong>{$result->num_rows}</strong></p>";
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>verification_id</th><th>Name</th><th>Email</th><th>Level</th><th>Status</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['verification_id']}</td>";
            echo "<td>{$row['firstName']} {$row['lastName']}</td>";
            echo "<td>{$row['userEmail']}</td>";
            echo "<td>{$row['userLevel']}</td>";
            echo "<td>{$row['verification_status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: red;'>Query failed: " . $mysqli->error . "</p>";
}

$mysqli->close();
?>
