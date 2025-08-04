<?php
include '../lib/Database.php';
$db = new Database();

echo "<h2>Checking tbl_user_verification structure:</h2>";
$result = $db->select('DESCRIBE tbl_user_verification');
if($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "Table does not exist or error occurred";
}

echo "<h2>Adding missing columns:</h2>";

// Add admin_comments column
$addAdminComments = "ALTER TABLE tbl_user_verification ADD COLUMN IF NOT EXISTS admin_comments TEXT DEFAULT NULL";
if($db->link->query($addAdminComments)) {
    echo "✅ admin_comments column added/verified<br>";
} else {
    echo "❌ Error adding admin_comments: " . $db->link->error . "<br>";
}

// Add reviewed_at column
$addReviewedAt = "ALTER TABLE tbl_user_verification ADD COLUMN IF NOT EXISTS reviewed_at TIMESTAMP NULL DEFAULT NULL";
if($db->link->query($addReviewedAt)) {
    echo "✅ reviewed_at column added/verified<br>";
} else {
    echo "❌ Error adding reviewed_at: " . $db->link->error . "<br>";
}

// Add reviewed_by column
$addReviewedBy = "ALTER TABLE tbl_user_verification ADD COLUMN IF NOT EXISTS reviewed_by INT DEFAULT NULL";
if($db->link->query($addReviewedBy)) {
    echo "✅ reviewed_by column added/verified<br>";
} else {
    echo "❌ Error adding reviewed_by: " . $db->link->error . "<br>";
}

echo "<h2>Updated table structure:</h2>";
$result2 = $db->select('DESCRIBE tbl_user_verification');
if($result2) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
    while($row = $result2->fetch_assoc()) {
        echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td></tr>";
    }
    echo "</table>";
}
?>
