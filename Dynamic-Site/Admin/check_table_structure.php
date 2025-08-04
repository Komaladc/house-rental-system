<?php
require_once('../config/config.php');
require_once('../lib/Database.php');

$db = new Database();

echo "<h2>🔍 Table Structure Debug</h2>";

// Check tbl_user_verification structure
echo "<h3>1. tbl_user_verification Table Structure</h3>";
$result = $db->link->query("DESCRIBE tbl_user_verification");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ Could not describe tbl_user_verification table</p>";
}

// Check actual data in tbl_user_verification
echo "<h3>2. Sample Data from tbl_user_verification</h3>";
$sampleData = $db->link->query("SELECT * FROM tbl_user_verification LIMIT 3");
if ($sampleData && $sampleData->num_rows > 0) {
    $firstRow = $sampleData->fetch_assoc();
    echo "<p><strong>Available columns:</strong> " . implode(', ', array_keys($firstRow)) . "</p>";
    
    // Reset pointer and show data
    $sampleData = $db->link->query("SELECT * FROM tbl_user_verification LIMIT 3");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr>";
    foreach (array_keys($firstRow) as $column) {
        echo "<th>" . htmlspecialchars($column) . "</th>";
    }
    echo "</tr>";
    
    while ($row = $sampleData->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ No data found in tbl_user_verification</p>";
}

// Check if Dipesh exists in tbl_user
echo "<h3>3. Search for Dipesh in tbl_user</h3>";
$dipeshSearch = $db->link->query("SELECT * FROM tbl_user WHERE firstName LIKE '%Dipesh%' OR lastName LIKE '%Tamang%' OR userEmail LIKE '%dipesh%'");
if ($dipeshSearch && $dipeshSearch->num_rows > 0) {
    echo "<p>✅ Found Dipesh in tbl_user:</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>User ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>User Level</th><th>Created</th></tr>";
    while ($row = $dipeshSearch->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['userId']) . "</td>";
        echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['lastName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userEmail']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userLevel']) . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ Dipesh not found in tbl_user</p>";
}

echo "<p><a href='complete_debug.php'>🔙 Back to Debug</a> | <a href='verify_users.php'>👨‍💼 Admin Panel</a></p>";
?>
