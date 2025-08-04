<?php
// Fix Verification Data Issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Fix Verification Data Issues</h2>";
echo "<div style='font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5;'>";

try {
    include '../lib/Database.php';
    $db = new Database();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    $fixed = 0;
    $issues = [];
    
    // 1. Check if tbl_user_verification table exists and has correct structure
    echo "<h3>1. 📋 Checking tbl_user_verification Table</h3>";
    $checkTable = "SHOW TABLES LIKE 'tbl_user_verification'";
    $tableExists = $db->select($checkTable);
    
    if (!$tableExists || $tableExists->num_rows == 0) {
        echo "<p style='color: orange;'>⚠️ Creating tbl_user_verification table...</p>";
        
        $createTable = "CREATE TABLE IF NOT EXISTS `tbl_user_verification` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `email` varchar(100) NOT NULL,
            `user_type` enum('owner','agent') NOT NULL,
            `citizenship_id` varchar(50) DEFAULT NULL,
            `citizenship_front` varchar(255) DEFAULT NULL,
            `citizenship_back` varchar(255) DEFAULT NULL,
            `business_license` varchar(255) DEFAULT NULL,
            `verification_status` enum('pending','approved','rejected') DEFAULT 'pending',
            `verified_by` int(11) DEFAULT NULL,
            `verified_at` timestamp NULL DEFAULT NULL,
            `rejection_reason` text DEFAULT NULL,
            `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `verification_status` (`verification_status`),
            FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`userId`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($db->insert($createTable)) {
            echo "<p style='color: green;'>✅ tbl_user_verification table created</p>";
            $fixed++;
        } else {
            echo "<p style='color: red;'>❌ Failed to create table: " . mysqli_error($db->link) . "</p>";
            $issues[] = "Failed to create tbl_user_verification table";
        }
    } else {
        echo "<p style='color: green;'>✅ tbl_user_verification table exists</p>";
        
        // Check if citizenship_id column exists
        $checkColumns = "SHOW COLUMNS FROM tbl_user_verification LIKE 'citizenship_id'";
        $columnExists = $db->select($checkColumns);
        
        if (!$columnExists || $columnExists->num_rows == 0) {
            echo "<p style='color: orange;'>⚠️ Adding citizenship_id column...</p>";
            $addColumn = "ALTER TABLE tbl_user_verification ADD COLUMN citizenship_id VARCHAR(50) AFTER user_type";
            if ($db->update($addColumn)) {
                echo "<p style='color: green;'>✅ citizenship_id column added</p>";
                $fixed++;
            } else {
                echo "<p style='color: red;'>❌ Failed to add citizenship_id column</p>";
                $issues[] = "Failed to add citizenship_id column";
            }
        }
    }
    
    // 2. Find users who need verification records but don't have them
    echo "<h3>2. 🔍 Finding Missing Verification Records</h3>";
    $missingQuery = "SELECT u.userId, u.firstName, u.lastName, u.userEmail, u.userLevel, u.verification_status 
                     FROM tbl_user u 
                     LEFT JOIN tbl_user_verification uv ON u.userId = uv.user_id 
                     WHERE u.requires_verification = 1 
                     AND u.userLevel IN (2, 3) 
                     AND uv.id IS NULL";
    $missingUsers = $db->select($missingQuery);
    
    if ($missingUsers && $missingUsers->num_rows > 0) {
        echo "<p style='color: orange;'>⚠️ Found " . $missingUsers->num_rows . " users without verification records</p>";
        
        while ($user = $missingUsers->fetch_assoc()) {
            $userType = ($user['userLevel'] == 2) ? 'owner' : 'agent';
            
            // Create verification record for this user
            $createVerification = "INSERT INTO tbl_user_verification (user_id, email, user_type, verification_status, submitted_at) 
                                  VALUES ('" . $user['userId'] . "', 
                                         '" . mysqli_real_escape_string($db->link, $user['userEmail']) . "', 
                                         '$userType', 
                                         'pending', 
                                         NOW())";
            
            if ($db->insert($createVerification)) {
                echo "<p style='color: green;'>✅ Created verification record for: " . htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) . "</p>";
                $fixed++;
            } else {
                echo "<p style='color: red;'>❌ Failed to create verification record for: " . htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) . "</p>";
                $issues[] = "Failed to create verification record for user " . $user['userId'];
            }
        }
    } else {
        echo "<p style='color: green;'>✅ All users have appropriate verification records</p>";
    }
    
    // 3. Check for orphaned verification records (verification records without corresponding users)
    echo "<h3>3. 🧹 Cleaning Up Orphaned Records</h3>";
    $orphanedQuery = "SELECT uv.id, uv.user_id, uv.email 
                     FROM tbl_user_verification uv 
                     LEFT JOIN tbl_user u ON uv.user_id = u.userId 
                     WHERE u.userId IS NULL";
    $orphanedRecords = $db->select($orphanedQuery);
    
    if ($orphanedRecords && $orphanedRecords->num_rows > 0) {
        echo "<p style='color: orange;'>⚠️ Found " . $orphanedRecords->num_rows . " orphaned verification records</p>";
        
        while ($orphaned = $orphanedRecords->fetch_assoc()) {
            $deleteOrphaned = "DELETE FROM tbl_user_verification WHERE id = " . $orphaned['id'];
            if ($db->delete($deleteOrphaned)) {
                echo "<p style='color: green;'>✅ Deleted orphaned record for user_id: " . $orphaned['user_id'] . "</p>";
                $fixed++;
            } else {
                echo "<p style='color: red;'>❌ Failed to delete orphaned record</p>";
                $issues[] = "Failed to delete orphaned record " . $orphaned['id'];
            }
        }
    } else {
        echo "<p style='color: green;'>✅ No orphaned verification records found</p>";
    }
    
    // 4. Update verification status consistency
    echo "<h3>4. 🔄 Updating Status Consistency</h3>";
    $updateConsistency = "UPDATE tbl_user u 
                         JOIN tbl_user_verification uv ON u.userId = uv.user_id 
                         SET u.verification_status = uv.verification_status 
                         WHERE u.requires_verification = 1 
                         AND u.verification_status != uv.verification_status";
    
    $updatedRows = $db->update($updateConsistency);
    if ($updatedRows !== false) {
        echo "<p style='color: green;'>✅ Updated status consistency for users</p>";
        $fixed++;
    }
    
    // 5. Show current verification data
    echo "<h3>5. 📊 Current Verification Data</h3>";
    $currentData = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.userLevel 
                   FROM tbl_user_verification uv 
                   JOIN tbl_user u ON uv.user_id = u.userId 
                   ORDER BY uv.submitted_at DESC 
                   LIMIT 10";
    $current = $db->select($currentData);
    
    if ($current && $current->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr style='background: #ddd;'><th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Status</th><th>Submitted</th></tr>";
        while ($row = $current->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . "</td>";
            echo "<td>" . htmlspecialchars($row['userEmail']) . "</td>";
            echo "<td>" . ucfirst($row['user_type']) . "</td>";
            echo "<td>" . ucfirst($row['verification_status']) . "</td>";
            echo "<td>" . $row['submitted_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No verification records found</p>";
    }
    
    // Summary
    echo "<div style='background: " . ($fixed > 0 ? "#d4edda" : "#fff3cd") . "; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>" . ($fixed > 0 ? "✅" : "⚠️") . " Summary</h3>";
    echo "<p><strong>Issues Fixed:</strong> $fixed</p>";
    if (!empty($issues)) {
        echo "<p><strong>Remaining Issues:</strong></p>";
        echo "<ul>";
        foreach ($issues as $issue) {
            echo "<li>" . htmlspecialchars($issue) . "</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
    
    echo "<div style='margin: 20px 0; padding: 15px; background: #d1ecf1; border-radius: 5px;'>";
    echo "<h4>🚀 Next Steps</h4>";
    echo "<a href='verify_users.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📋 View Verify Users</a>";
    echo "<a href='debug_verification.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔍 Debug Again</a>";
    echo "<a href='dashboard.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📊 Dashboard</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";
?>
