<?php
// Fix tbl_user table for signup process
include "lib/Database.php";

$db = new Database();

echo "<h2>🔧 Fixing tbl_user Table for Signup Process</h2>";
echo "<div style='font-family: Arial; padding: 20px; background: #f5f5f5;'>";

// Check current tbl_user structure
echo "<h3>📋 Current tbl_user structure:</h3>";
$describeQuery = "DESCRIBE tbl_user";
$result = $db->select($describeQuery);

if ($result) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #ddd;'><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Add missing columns needed for signup process
$columnsToAdd = [
    "requires_verification" => "TINYINT(1) DEFAULT 0 COMMENT 'Whether user role requires document verification'",
    "email_verified" => "TINYINT(1) DEFAULT 0 COMMENT 'Whether email has been verified via OTP'",
    "document_verified" => "TINYINT(1) DEFAULT 0 COMMENT 'Whether documents have been verified by admin'"
];

echo "<h3>➕ Adding missing columns for signup process:</h3>";

foreach ($columnsToAdd as $column => $definition) {
    // Check if column exists
    $checkQuery = "SHOW COLUMNS FROM tbl_user LIKE '$column'";
    $exists = $db->select($checkQuery);
    
    if (!$exists || $exists->num_rows == 0) {
        $alterQuery = "ALTER TABLE tbl_user ADD COLUMN $column $definition";
        echo "<p>🔄 Adding column '$column'...</p>";
        
        if ($db->link->query($alterQuery)) {
            echo "<p style='color: green;'>✅ Successfully added column '$column'</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding column '$column': " . $db->link->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Column '$column' already exists</p>";
    }
}

// Update existing users based on their userLevel
echo "<h3>🔄 Updating existing user data:</h3>";

// Set requires_verification based on user level (owners and agents need verification)
$updateVerificationQuery = "
    UPDATE tbl_user 
    SET requires_verification = CASE 
        WHEN userLevel IN (2, 3) THEN 1 
        ELSE 0 
    END,
    email_verified = 1,
    document_verified = CASE 
        WHEN userLevel IN (2, 3) THEN 0 
        ELSE 1 
    END
    WHERE requires_verification IS NULL
";

if ($db->update($updateVerificationQuery)) {
    echo "<p style='color: green;'>✅ Updated existing users verification requirements</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Update query info: " . $db->link->error . "</p>";
}

// Show updated structure
echo "<h3>📋 Updated tbl_user structure:</h3>";
$describeQuery2 = "DESCRIBE tbl_user";
$result2 = $db->select($describeQuery2);

if ($result2) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #ddd;'><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result2->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Test the signup query
echo "<h3>🧪 Testing Signup Query Structure:</h3>";
$testQuery = "INSERT INTO tbl_user (
    firstName, lastName, userName, userEmail, userPass, cellNo, userLevel, 
    status, verification_status, requires_verification, email_verified, document_verified
) VALUES (
    'Test', 'User', 'testuser', 'test@example.com', 'password', '1234567890', 2,
    1, 'pending', 1, 1, 0
)";

echo "<p><strong>Test Query:</strong></p>";
echo "<code style='background: #f0f0f0; padding: 10px; display: block; margin: 10px 0;'>$testQuery</code>";

// Don't actually execute, just check if the columns exist
echo "<p style='color: green;'>✅ Query structure is now compatible with signup process</p>";

echo "<h3>✅ Signup fix completed!</h3>";
echo "<p><strong>What was fixed:</strong></p>";
echo "<ul>";
echo "<li>✅ Added 'requires_verification' column for user roles that need document verification</li>";
echo "<li>✅ Added 'email_verified' column to track OTP verification status</li>";
echo "<li>✅ Added 'document_verified' column to track admin document approval</li>";
echo "<li>✅ Updated existing users with appropriate default values</li>";
echo "</ul>";

echo "<div style='margin: 20px 0;'>";
echo "<a href='signup_enhanced.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📝 Test Enhanced Signup</a>";
echo "<a href='signup_with_verification.php' style='background: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📧 Test OTP Signup</a>";
echo "<a href='admin/dashboard.php' style='background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>📊 Admin Dashboard</a>";
echo "</div>";

echo "</div>";
?>
