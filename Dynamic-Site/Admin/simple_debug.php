<?php
// Simple debug without using Database class
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "db_rental";

try {
    $mysqli = new mysqli($host, $user, $pass, $dbname);
    
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "<h2>🔍 Simple Table Structure Debug</h2>";
    
    // Check tbl_user_verification structure
    echo "<h3>1. tbl_user_verification Table Structure</h3>";
    $result = $mysqli->query("DESCRIBE tbl_user_verification");
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
    
    // Check if table exists and has data
    echo "<h3>2. Table Data Count</h3>";
    $count = $mysqli->query("SELECT COUNT(*) as count FROM tbl_user_verification");
    if ($count) {
        $row = $count->fetch_assoc();
        echo "<p>Total records in tbl_user_verification: " . $row['count'] . "</p>";
    }
    
    // Check for Dipesh in tbl_user
    echo "<h3>3. Search for Dipesh in tbl_user</h3>";
    $dipeshSearch = $mysqli->query("SELECT * FROM tbl_user WHERE firstName LIKE '%Dipesh%' OR lastName LIKE '%Tamang%' OR userEmail LIKE '%dipesh%'");
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
    
    // Check verification records for Dipesh's user ID if found
    echo "<h3>4. Check for Dipesh's Verification Records</h3>";
    $dipeshSearch = $mysqli->query("SELECT userId FROM tbl_user WHERE firstName LIKE '%Dipesh%' OR lastName LIKE '%Tamang%'");
    if ($dipeshSearch && $dipeshSearch->num_rows > 0) {
        while ($userRow = $dipeshSearch->fetch_assoc()) {
            $userId = $userRow['userId'];
            echo "<p>Checking verification records for User ID: $userId</p>";
            $verificationCheck = $mysqli->query("SELECT * FROM tbl_user_verification WHERE user_id = $userId");
            if ($verificationCheck && $verificationCheck->num_rows > 0) {
                echo "<p>✅ Found verification records:</p>";
                while ($verRow = $verificationCheck->fetch_assoc()) {
                    echo "<pre>" . print_r($verRow, true) . "</pre>";
                }
            } else {
                echo "<p>❌ No verification records found for User ID: $userId</p>";
            }
        }
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<p><a href='verify_users.php'>👨‍💼 Admin Panel</a> | <a href='complete_debug.php'>🔧 Complete Debug</a></p>";
?>
