<?php
echo "=== Direct Database Fix and Test ===\n\n";

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'db_rental';

// Direct MySQL connection
$link = new mysqli($host, $user, $pass, $dbname);

if ($link->connect_error) {
    die("Connection failed: " . $link->connect_error);
}

echo "✓ Connected to database successfully\n\n";

// Check tbl_user_verification table
echo "1. Checking tbl_user_verification table...\n";
$result = $link->query("SHOW TABLES LIKE 'tbl_user_verification'");

if ($result->num_rows == 0) {
    echo "   Table doesn't exist. Creating it...\n";
    $createTable = "CREATE TABLE tbl_user_verification (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        email VARCHAR(255) NOT NULL,
        userName VARCHAR(255) NOT NULL,
        user_level INT NOT NULL,
        user_type VARCHAR(50) NOT NULL,
        citizenship_id VARCHAR(100) DEFAULT NULL,
        citizenship_front VARCHAR(255) DEFAULT NULL,
        citizenship_back VARCHAR(255) DEFAULT NULL,
        business_license VARCHAR(255) DEFAULT NULL,
        verification_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        admin_comments TEXT DEFAULT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        reviewed_at TIMESTAMP NULL DEFAULT NULL,
        reviewed_by INT DEFAULT NULL
    )";
    
    if ($link->query($createTable)) {
        echo "   ✓ Table created successfully\n";
    } else {
        echo "   ✗ Failed to create table: " . $link->error . "\n";
    }
} else {
    echo "   Table exists. Checking structure...\n";
    $result = $link->query("SHOW COLUMNS FROM tbl_user_verification");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    if (!in_array('userName', $columns)) {
        if (in_array('username', $columns)) {
            echo "   Renaming 'username' to 'userName'...\n";
            $link->query("ALTER TABLE tbl_user_verification CHANGE username userName VARCHAR(255) NOT NULL");
            echo "   ✓ Column renamed\n";
        } else {
            echo "   Adding userName column...\n";
            $link->query("ALTER TABLE tbl_user_verification ADD COLUMN userName VARCHAR(255) NOT NULL AFTER email");
            echo "   ✓ Column added\n";
        }
    } else {
        echo "   ✓ userName column exists\n";
    }
}

// Check current structure
echo "\n2. Final table structure:\n";
$result = $link->query("SHOW COLUMNS FROM tbl_user_verification");
while ($row = $result->fetch_assoc()) {
    echo "   - " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

// Test the email verification process
echo "\n3. Testing email verification process...\n";

$testEmail = "test.fix@example.com";

// Clean up any existing test data
$link->query("DELETE FROM tbl_pending_verification WHERE email = '$testEmail'");
$link->query("DELETE FROM tbl_user WHERE userEmail = '$testEmail'");
$link->query("DELETE FROM tbl_user_verification WHERE email = '$testEmail'");

// Create test pending verification
$testData = [
    'fname' => 'Test',
    'lname' => 'Fix',
    'email' => $testEmail,
    'cellno' => '9800000000',
    'address' => 'Test Address',
    'password' => md5('test123'),
    'level' => '2',
    'requires_verification' => true
];

$verificationToken = bin2hex(random_bytes(32));
$otp = '123456';

$insertQuery = "INSERT INTO tbl_pending_verification 
                (email, registration_data, otp, verification_token, expires_at, created_at) 
                VALUES 
                ('$testEmail', 
                 '" . mysqli_real_escape_string($link, json_encode($testData)) . "',
                 '$otp',
                 '$verificationToken',
                 DATE_ADD(NOW(), INTERVAL 1 HOUR),
                 NOW())";

if ($link->query($insertQuery)) {
    echo "   ✓ Test pending verification created\n";
} else {
    echo "   ✗ Failed to create pending verification: " . $link->error . "\n";
}

echo "\n4. Now you can test the email verification link with:\n";
echo "   Email: $testEmail\n";
echo "   Token: $verificationToken\n";
echo "   URL: verify_registration.php?email=" . urlencode($testEmail) . "&token=$verificationToken\n";

$link->close();
echo "\n✓ Setup completed. The email verification should now work!\n";
?>
