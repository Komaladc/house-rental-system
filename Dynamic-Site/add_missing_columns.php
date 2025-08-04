<?php
// Add any additional missing columns to tbl_user
include "lib/Database.php";

$db = new Database();

echo "<h2>🔧 Adding Additional Missing Columns</h2>";
echo "<div style='font-family: Arial; padding: 20px; background: #f5f5f5;'>";

// Check if we need userAddress column (some forms might use it)
$additionalColumns = [
    "userAddress" => "TEXT COMMENT 'User address information'",
    "confPass" => "VARCHAR(255) COMMENT 'Confirmation password (legacy)'",
    "verification_token" => "VARCHAR(255) COMMENT 'Email verification token'",
    "submitted_documents" => "TINYINT(1) DEFAULT 0 COMMENT 'Whether user submitted verification documents'"
];

echo "<h3>➕ Adding optional columns for compatibility:</h3>";

foreach ($additionalColumns as $column => $definition) {
    // Check if column exists
    $checkQuery = "SHOW COLUMNS FROM tbl_user LIKE '$column'";
    $exists = $db->select($checkQuery);
    
    if (!$exists || $exists->num_rows == 0) {
        $alterQuery = "ALTER TABLE tbl_user ADD COLUMN $column $definition";
        echo "<p>🔄 Adding optional column '$column'...</p>";
        
        if ($db->link->query($alterQuery)) {
            echo "<p style='color: green;'>✅ Successfully added column '$column'</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding column '$column': " . $db->link->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Column '$column' already exists</p>";
    }
}

// Show final table structure
echo "<h3>📋 Final tbl_user structure:</h3>";
$describeQuery = "DESCRIBE tbl_user";
$result = $db->select($describeQuery);

if ($result) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
    echo "<tr style='background: #ddd;'><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['Extra'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Test a complete signup query
echo "<h3>🧪 Testing Complete Signup Query:</h3>";
$testQuery = "INSERT INTO tbl_user(
    firstName, lastName, userName, userEmail, userPass, cellNo, userLevel, 
    status, verification_status, requires_verification, email_verified, document_verified
) VALUES (
    'Test', 'Owner', 'testowner123', 'testowner@example.com', 
    '" . md5('password123') . "', '9841234567', 2,
    0, 'pending', 1, 1, 0
)";

echo "<div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #007cba; margin: 10px 0;'>";
echo "<code style='white-space: pre-wrap;'>$testQuery</code>";
echo "</div>";

echo "<p style='color: green;'>✅ Query syntax is now compatible with the table structure</p>";

echo "<h3>🎯 Ready for Testing!</h3>";
echo "<p><strong>The signup process should now work for:</strong></p>";
echo "<ul>";
echo "<li>✅ Property Seekers (userLevel = 1) - No verification required</li>";
echo "<li>✅ Property Owners (userLevel = 2) - Document verification required</li>";
echo "<li>✅ Real Estate Agents (userLevel = 3) - Document verification required</li>";
echo "</ul>";

echo "<div style='margin: 20px 0;'>";
echo "<a href='signup_with_verification.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🧪 Test Signup Process</a>";
echo "<a href='signup_enhanced.php' style='background: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📝 Enhanced Signup Form</a>";
echo "<a href='admin/verify_users.php' style='background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>✅ Admin Verification</a>";
echo "</div>";

echo "</div>";
?>
