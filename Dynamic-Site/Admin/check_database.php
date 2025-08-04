<?php
// Database Structure Checker
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🗄️ Database Structure Checker</h2>";

try {
    include_once('../config/config.php');
    include_once('../lib/Database.php');
    
    $db = new Database();
    echo "✅ Database connected<br>";
    
    // Check if tbl_ad table exists
    echo "<h3>📋 Table: tbl_ad</h3>";
    $query = "DESCRIBE tbl_ad";
    $result = $db->select($query);
    
    if($result) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check if all required columns exist
        echo "<h3>🔍 Column Check</h3>";
        $required_columns = [
            'adTitle', 'adImg', 'catId', 'adDate', 'builtYear', 'adDetails', 'adArea', 'adAddress', 
            'adSize', 'totalFloor', 'totalUnit', 'totalRoom', 'totalBed', 'totalBath', 'attachBath', 
            'commonBath', 'totalBelcony', 'floorNo', 'floorType', 'prefferedRenter', 'liftElevetor', 
            'adGenerator', 'adWifi', 'carParking', 'openSpace', 'playGround', 'ccTV', 'sGuard', 
            'rentType', 'adRent', 'gasBill', 'electricBill', 'eBillType', 'sCharge', 'adNegotiable', 'userId'
        ];
        
        // Get actual columns
        $result = $db->select("DESCRIBE tbl_ad");
        $actual_columns = [];
        while($row = $result->fetch_assoc()) {
            $actual_columns[] = $row['Field'];
        }
        
        echo "<h4>✅ Columns that exist:</h4>";
        foreach($required_columns as $col) {
            if(in_array($col, $actual_columns)) {
                echo "<span style='color: green;'>✓ $col</span><br>";
            } else {
                echo "<span style='color: red;'>✗ $col (MISSING)</span><br>";
            }
        }
        
        echo "<h4>📝 Extra columns in database:</h4>";
        foreach($actual_columns as $col) {
            if(!in_array($col, $required_columns)) {
                echo "<span style='color: blue;'>+ $col</span><br>";
            }
        }
        
    } else {
        echo "❌ tbl_ad table does not exist<br>";
        
        // Show available tables
        echo "<h3>Available tables:</h3>";
        $tablesResult = $db->select("SHOW TABLES");
        if($tablesResult) {
            while($row = $tablesResult->fetch_array()) {
                echo "- " . $row[0] . "<br>";
            }
        }
    }
    
    // Test a simple insert to see what error we get
    echo "<h3>🧪 Test Insert</h3>";
    echo "<form method='post'>";
    echo "<button type='submit' name='test_insert'>Test Simple Insert</button>";
    echo "</form>";
    
    if(isset($_POST['test_insert'])) {
        try {
            // Try to insert minimal data
            $testQuery = "INSERT INTO tbl_ad (adTitle, catId, userId) VALUES ('Test Property', 1, 1)";
            $result = $db->insert($testQuery);
            if($result) {
                echo "✅ Simple insert successful<br>";
            } else {
                echo "❌ Simple insert failed<br>";
            }
        } catch (Exception $e) {
            echo "❌ Insert error: " . $e->getMessage() . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>

<p><a href="standalone_test.php">🧪 Standalone Test</a> | <a href="debug_form_test.php">🔧 Debug Form</a></p>
