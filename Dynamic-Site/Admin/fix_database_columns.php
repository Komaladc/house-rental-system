<?php
include '../lib/Database.php';
$db = new Database();

echo "<h2>Checking tbl_user_verification structure:</h2>";
$result = $db->select('DESCRIBE tbl_user_verification');
if($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Key</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td><td>" . ($row['Key'] ?? '') . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "Table does not exist or error occurred";
}

echo "<h2>Adding missing columns:</h2>";

// Add verification_id primary key column if it doesn't exist
$checkVerificationId = $db->select("SHOW COLUMNS FROM tbl_user_verification LIKE 'verification_id'");
if(!$checkVerificationId || $checkVerificationId->num_rows == 0) {
    echo "<p>Adding verification_id primary key column...</p>";
    $addVerificationId = "ALTER TABLE tbl_user_verification ADD COLUMN verification_id INT AUTO_INCREMENT PRIMARY KEY FIRST";
    if($db->link->query($addVerificationId)) {
        echo "✅ verification_id column added successfully<br>";
    } else {
        echo "❌ Error adding verification_id: " . $db->link->error . "<br>";
    }
} else {
    echo "✅ verification_id column already exists<br>";
}

// Add admin_comments column
$checkAdminComments = $db->select("SHOW COLUMNS FROM tbl_user_verification LIKE 'admin_comments'");
if(!$checkAdminComments || $checkAdminComments->num_rows == 0) {
    $addAdminComments = "ALTER TABLE tbl_user_verification ADD COLUMN admin_comments TEXT DEFAULT NULL";
    if($db->link->query($addAdminComments)) {
        echo "✅ admin_comments column added/verified<br>";
    } else {
        echo "❌ Error adding admin_comments: " . $db->link->error . "<br>";
    }
} else {
    echo "✅ admin_comments column already exists<br>";
}

// Add reviewed_at column
$checkReviewedAt = $db->select("SHOW COLUMNS FROM tbl_user_verification LIKE 'reviewed_at'");
if(!$checkReviewedAt || $checkReviewedAt->num_rows == 0) {
    $addReviewedAt = "ALTER TABLE tbl_user_verification ADD COLUMN reviewed_at TIMESTAMP NULL DEFAULT NULL";
    if($db->link->query($addReviewedAt)) {
        echo "✅ reviewed_at column added/verified<br>";
    } else {
        echo "❌ Error adding reviewed_at: " . $db->link->error . "<br>";
    }
} else {
    echo "✅ reviewed_at column already exists<br>";
}

// Add reviewed_by column
$checkReviewedBy = $db->select("SHOW COLUMNS FROM tbl_user_verification LIKE 'reviewed_by'");
if(!$checkReviewedBy || $checkReviewedBy->num_rows == 0) {
    $addReviewedBy = "ALTER TABLE tbl_user_verification ADD COLUMN reviewed_by INT DEFAULT NULL";
    if($db->link->query($addReviewedBy)) {
        echo "✅ reviewed_by column added/verified<br>";
    } else {
        echo "❌ Error adding reviewed_by: " . $db->link->error . "<br>";
    }
} else {
    echo "✅ reviewed_by column already exists<br>";
}

echo "<h2>Updated table structure:</h2>";
$result2 = $db->select('DESCRIBE tbl_user_verification');
if($result2) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Key</th><th>Extra</th></tr>";
    while($row = $result2->fetch_assoc()) {
        echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td><td>" . ($row['Key'] ?? '') . "</td><td>" . ($row['Extra'] ?? '') . "</td></tr>";
    }
    echo "</table>";
}

echo "<h2>✅ Database structure fixed!</h2>";
echo "<p><a href='verify_users.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Verification Page</a></p>";
?>
