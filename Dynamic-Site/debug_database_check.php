<?php
include_once 'config/config.php';
include_once 'classes/EmailOTP.php';

echo "<h2>Database Integration Check</h2>";

// Check database connection
echo "<h3>Database Connection Test:</h3>";
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    echo "✓ Database connected successfully<br>";
} catch(PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Check OTP table structure
echo "<h3>OTP Table Structure:</h3>";
try {
    $stmt = $pdo->query('DESCRIBE tbl_otp');
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Default']}</td></tr>";
    }
    echo "</table>";
} catch(Exception $e) {
    echo "Error checking tbl_otp: " . $e->getMessage() . "<br>";
}

// Check pending verification table structure
echo "<h3>Pending Verification Table Structure:</h3>";
try {
    $stmt = $pdo->query('DESCRIBE tbl_pending_verification');
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Default']}</td></tr>";
    }
    echo "</table>";
} catch(Exception $e) {
    echo "Error checking tbl_pending_verification: " . $e->getMessage() . "<br>";
}

// Check recent OTP records
echo "<h3>Recent OTP Records (last 10):</h3>";
try {
    $stmt = $pdo->query('SELECT * FROM tbl_otp ORDER BY created_at DESC LIMIT 10');
    echo "<table border='1'><tr><th>ID</th><th>Email</th><th>OTP</th><th>Created</th><th>Expires</th><th>Verified</th><th>Used</th></tr>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $verified = isset($row['is_verified']) ? ($row['is_verified'] ? 'Yes' : 'No') : 'N/A';
        $used = isset($row['is_used']) ? ($row['is_used'] ? 'Yes' : 'No') : 'N/A';
        $expires = isset($row['expires_at']) ? $row['expires_at'] : 'N/A';
        echo "<tr><td>{$row['id']}</td><td>{$row['email']}</td><td>{$row['otp_code']}</td><td>{$row['created_at']}</td><td>{$expires}</td><td>{$verified}</td><td>{$used}</td></tr>";
    }
    echo "</table>";
} catch(Exception $e) {
    echo "Error checking OTP records: " . $e->getMessage() . "<br>";
}

// Check recent pending verification records
echo "<h3>Recent Pending Verification Records (last 10):</h3>";
try {
    $stmt = $pdo->query('SELECT * FROM tbl_pending_verification ORDER BY created_at DESC LIMIT 10');
    echo "<table border='1'><tr><th>ID</th><th>Email</th><th>Status</th><th>User Type</th><th>Created</th></tr>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td>{$row['id']}</td><td>{$row['email']}</td><td>{$row['verification_status']}</td><td>{$row['user_type']}</td><td>{$row['created_at']}</td></tr>";
    }
    echo "</table>";
} catch(Exception $e) {
    echo "Error checking pending verification records: " . $e->getMessage() . "<br>";
}

// Test OTP verification process
echo "<h3>OTP Verification Process Test:</h3>";
$emailOTP = new EmailOTP();

// Test with a sample email to see the flow
$testEmail = "test@example.com";
echo "<h4>Testing OTP flow for: $testEmail</h4>";

// Check if there are any OTP records for this email
try {
    $stmt = $pdo->prepare('SELECT * FROM tbl_otp WHERE email = ? ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([$testEmail]);
    $otpRecord = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($otpRecord) {
        echo "Latest OTP record found:<br>";
        echo "OTP: {$otpRecord['otp_code']}<br>";
        echo "Created: {$otpRecord['created_at']}<br>";
        echo "Expires: " . (isset($otpRecord['expires_at']) ? $otpRecord['expires_at'] : 'N/A') . "<br>";
        echo "Verified: " . (isset($otpRecord['is_verified']) ? ($otpRecord['is_verified'] ? 'Yes' : 'No') : 'N/A') . "<br>";
        echo "Used: " . (isset($otpRecord['is_used']) ? ($otpRecord['is_used'] ? 'Yes' : 'No') : 'N/A') . "<br>";
        
        // Test verification
        $isValid = $emailOTP->verifyOTP($testEmail, $otpRecord['otp_code']);
        echo "Verification test result: " . ($isValid ? "✓ Valid" : "✗ Invalid") . "<br>";
    } else {
        echo "No OTP records found for this email.<br>";
    }
} catch(Exception $e) {
    echo "Error testing OTP verification: " . $e->getMessage() . "<br>";
}

// Check timezone settings
echo "<h3>Timezone Settings:</h3>";
echo "PHP Default Timezone: " . date_default_timezone_get() . "<br>";
echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";

// Check if timezone config exists
if (file_exists('config/timezone.php')) {
    echo "✓ Timezone config file exists<br>";
} else {
    echo "✗ Timezone config file missing<br>";
}
?>
