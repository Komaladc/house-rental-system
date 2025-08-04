<?php
include "lib/Database.php";

$db = new Database();

echo "<h2>Database Column Check</h2>";

// Check if email_verified column exists
$checkColumn = "SHOW COLUMNS FROM tbl_user LIKE 'email_verified'";
$columnExists = $db->select($checkColumn);

if($columnExists && $columnExists->num_rows > 0) {
    echo "✅ email_verified column EXISTS<br>";
    
    // Check some sample data
    $sampleData = $db->select("SELECT userId, firstName, userEmail, email_verified, userStatus, userLevel FROM tbl_user LIMIT 5");
    
    if($sampleData && $sampleData->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>UserID</th><th>Name</th><th>Email</th><th>Email Verified</th><th>User Status</th><th>User Level</th></tr>";
        while($row = $sampleData->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['userId']}</td>";
            echo "<td>{$row['firstName']}</td>";
            echo "<td>{$row['userEmail']}</td>";
            echo "<td>" . (isset($row['email_verified']) ? $row['email_verified'] : 'NULL') . "</td>";
            echo "<td>{$row['userStatus']}</td>";
            echo "<td>{$row['userLevel']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "❌ email_verified column does NOT exist<br>";
}

// Check table structure
echo "<h3>Current tbl_user structure:</h3>";
$structure = $db->select("DESCRIBE tbl_user");
if($structure && $structure->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
