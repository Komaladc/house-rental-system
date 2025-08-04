<?php
$pdo = new PDO('mysql:host=localhost;dbname=db_rental', 'root', '');

echo "<h2>Database Table Structure Check</h2>";

echo "<h3>tbl_otp Table Structure:</h3>";
$stmt = $pdo->query('DESCRIBE tbl_otp');
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr><td><strong>{$row['Field']}</strong></td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
}
echo "</table>";

echo "<h3>Sample Records:</h3>";
$stmt = $pdo->query('SELECT * FROM tbl_otp LIMIT 3');
if ($stmt->rowCount() > 0) {
    $firstRow = true;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($firstRow) {
            echo "<table border='1'><tr>";
            foreach (array_keys($row) as $column) {
                echo "<th>$column</th>";
            }
            echo "</tr>";
            $firstRow = false;
        }
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>$value</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No records found.";
}
?>
