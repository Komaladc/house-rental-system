<?php
include 'lib/Database.php';

$db = new Database();

echo "=== tbl_user structure ===\n";
$result = $db->select('DESCRIBE tbl_user');
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}

echo "\n=== tbl_user_verification structure ===\n";
$result2 = $db->select('DESCRIBE tbl_user_verification');
while($row = $result2->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}

echo "\n=== Sample user data ===\n";
$result3 = $db->select('SELECT * FROM tbl_user LIMIT 3');
while($row = $result3->fetch_assoc()) {
    print_r($row);
}
?>
