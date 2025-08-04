<?php
include 'lib/Database.php';

echo "<h2>Database Structure Check</h2>";

try {
    $db = new Database();
    
    echo "<h3>tbl_user structure:</h3>";
    $result = $db->select('DESCRIBE tbl_user');
    if ($result) {
        echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td></tr>";
        }
        echo "</table>";
    }

    echo "<h3>tbl_user_verification structure:</h3>";
    $result2 = $db->select('DESCRIBE tbl_user_verification');
    if ($result2) {
        echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
        while($row = $result2->fetch_assoc()) {
            echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td></tr>";
        }
        echo "</table>";
    }

    echo "<h3>Sample user data:</h3>";
    $result3 = $db->select('SELECT * FROM tbl_user LIMIT 2');
    if ($result3) {
        echo "<table border='1'>";
        $first = true;
        while($row = $result3->fetch_assoc()) {
            if ($first) {
                echo "<tr>";
                foreach(array_keys($row) as $key) {
                    echo "<th>$key</th>";
                }
                echo "</tr>";
                $first = false;
            }
            echo "<tr>";
            foreach($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
