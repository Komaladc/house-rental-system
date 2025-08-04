<?php
// Complete fix for Dipesh Tamang issue
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "db_rental";

try {
    $mysqli = new mysqli($host, $user, $pass, $dbname);
    
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "<h2>🚨 Complete Fix for Dipesh Tamang</h2>";
    
    // Step 1: Check if Dipesh exists in tbl_user
    echo "<h3>Step 1: Check if Dipesh exists in tbl_user</h3>";
    $dipeshCheck = $mysqli->query("SELECT * FROM tbl_user WHERE firstName LIKE '%Dipesh%' AND lastName LIKE '%Tamang%'");
    
    $dipeshUserId = null;
    
    if ($dipeshCheck && $dipeshCheck->num_rows > 0) {
        echo "<p>✅ Found Dipesh in tbl_user:</p>";
        while ($row = $dipeshCheck->fetch_assoc()) {
            $dipeshUserId = $row['userId'];
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            foreach ($row as $key => $value) {
                echo "<tr><td>$key</td><td>" . htmlspecialchars($value) . "</td></tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p>❌ Dipesh not found in tbl_user. Creating Dipesh now...</p>";
        
        // Create Dipesh Tamang as agent
        $insertUser = "INSERT INTO tbl_user (firstName, lastName, userEmail, userPass, cellNo, userAddress, userLevel, userStatus, otp_verified, created_at) 
                       VALUES ('Dipesh', 'Tamang', 'dipesh.tamang@example.com', MD5('password123'), '9876543210', 'Kathmandu, Nepal', 2, 1, 1, NOW())";
        
        if ($mysqli->query($insertUser)) {
            $dipeshUserId = $mysqli->insert_id;
            echo "<p>✅ Created Dipesh Tamang with User ID: $dipeshUserId</p>";
        } else {
            echo "<p>❌ Failed to create Dipesh: " . $mysqli->error . "</p>";
            exit;
        }
    }
    
    // Step 2: Check if verification record exists
    echo "<h3>Step 2: Check verification record for User ID: $dipeshUserId</h3>";
    $verificationCheck = $mysqli->query("SELECT * FROM tbl_user_verification WHERE user_id = $dipeshUserId");
    
    if ($verificationCheck && $verificationCheck->num_rows > 0) {
        echo "<p>⚠️ Verification record exists. Deleting old records...</p>";
        $deleteOld = $mysqli->query("DELETE FROM tbl_user_verification WHERE user_id = $dipeshUserId");
        if ($deleteOld) {
            echo "<p>✅ Deleted old verification records</p>";
        }
    } else {
        echo "<p>ℹ️ No existing verification record found</p>";
    }
    
    // Step 3: Create new verification record
    echo "<h3>Step 3: Create new verification record</h3>";
    $insertVerification = "INSERT INTO tbl_user_verification (user_id, verification_status, submitted_at, documents_path) 
                           VALUES ($dipeshUserId, 'pending', NOW(), 'uploads/dipesh_documents/')";
    
    if ($mysqli->query($insertVerification)) {
        echo "<p>✅ Created verification record for Dipesh Tamang</p>";
    } else {
        echo "<p>❌ Failed to create verification record: " . $mysqli->error . "</p>";
    }
    
    // Step 4: Verify the admin panel query
    echo "<h3>Step 4: Test Admin Panel Query</h3>";
    $adminQuery = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.cellNo, u.userAddress, u.userLevel, u.created_at as user_created 
                   FROM tbl_user_verification uv 
                   JOIN tbl_user u ON uv.user_id = u.userId 
                   WHERE uv.verification_status = 'pending' 
                   ORDER BY uv.submitted_at ASC";
    
    $adminResult = $mysqli->query($adminQuery);
    
    if ($adminResult && $adminResult->num_rows > 0) {
        echo "<p>✅ Admin panel query successful. Found {$adminResult->num_rows} pending verification(s):</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'><th>User ID</th><th>Name</th><th>Email</th><th>User Level</th><th>Status</th><th>Submitted</th></tr>";
        
        $dipeshFound = false;
        while ($row = $adminResult->fetch_assoc()) {
            $fullName = $row['firstName'] . ' ' . $row['lastName'];
            if (stripos($fullName, 'Dipesh') !== false && stripos($fullName, 'Tamang') !== false) {
                $dipeshFound = true;
                echo "<tr style='background: #d4edda;'>";
            } else {
                echo "<tr>";
            }
            echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
            echo "<td>" . htmlspecialchars($fullName) . "</td>";
            echo "<td>" . htmlspecialchars($row['userEmail']) . "</td>";
            echo "<td>" . htmlspecialchars($row['userLevel']) . "</td>";
            echo "<td>" . htmlspecialchars($row['verification_status']) . "</td>";
            echo "<td>" . htmlspecialchars($row['submitted_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if ($dipeshFound) {
            echo "<p style='color: green; font-weight: bold;'>🎉 SUCCESS: Dipesh Tamang is now visible in the admin verification panel!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ Dipesh Tamang still not visible in admin panel</p>";
        }
    } else {
        echo "<p>❌ Admin panel query failed or no results</p>";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<br><p><a href='verify_users.php'>👨‍💼 Go to Admin Panel</a> | <a href='complete_debug.php'>🔧 Debug Panel</a></p>";
?>
