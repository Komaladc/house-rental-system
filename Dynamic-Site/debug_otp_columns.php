<?php
include_once 'config/config.php';

echo "<h2>OTP Table Column Check</h2>";

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    
    echo "<h3>OTP Table Structure:</h3>";
    $stmt = $pdo->query('DESCRIBE tbl_otp');
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td><strong>{$row['Field']}</strong></td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
    }
    echo "</table>";

    echo "<h3>Sample OTP Record:</h3>";
    $stmt = $pdo->query('SELECT * FROM tbl_otp ORDER BY created_at DESC LIMIT 1');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row) {
        echo "<table border='1'><tr><th>Column</th><th>Value</th></tr>";
        foreach($row as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
    } else {
        echo "No OTP records found.";
    }
    
    echo "<h3>All OTP Records:</h3>";
    $stmt = $pdo->query('SELECT * FROM tbl_otp ORDER BY created_at DESC');
    echo "<table border='1'><tr><th>ID</th><th>Email</th><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Is Used</th><th>Is Verified</th></tr>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['otp_code']}</td>";  // Note: checking if it's otp_code or otp
        echo "<td>" . (isset($row['purpose']) ? $row['purpose'] : 'N/A') . "</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "<td>" . (isset($row['expires_at']) ? $row['expires_at'] : 'N/A') . "</td>";
        echo "<td>" . (isset($row['is_used']) ? ($row['is_used'] ? 'Yes' : 'No') : 'N/A') . "</td>";
        echo "<td>" . (isset($row['is_verified']) ? ($row['is_verified'] ? 'Yes' : 'No') : 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
