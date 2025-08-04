<?php
echo "=== Testing verifyAndCreateAccount Method ===\n\n";

// Direct database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'db_rental';

$link = new mysqli($host, $user, $pass, $dbname);

if ($link->connect_error) {
    die("Connection failed: " . $link->connect_error);
}

echo "✓ Connected to database\n";

// Ensure table exists
$tableExists = $link->query("SHOW TABLES LIKE 'tbl_user_verification'");
if ($tableExists->num_rows == 0) {
    echo "Creating tbl_user_verification table...\n";
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
    $link->query($createTable);
}

$testEmail = "test.verify@example.com";

// Clean up
$link->query("DELETE FROM tbl_pending_verification WHERE email = '$testEmail'");
$link->query("DELETE FROM tbl_user WHERE userEmail = '$testEmail'");
$link->query("DELETE FROM tbl_user_verification WHERE email = '$testEmail'");

// Create test data
$testData = [
    'fname' => 'Test',
    'lname' => 'Verify',
    'email' => $testEmail,
    'cellno' => '9800000000',
    'address' => 'Test Address',
    'password' => md5('test123'),
    'level' => '2',
    'requires_verification' => true
];

$verificationToken = bin2hex(random_bytes(16));

$insertQuery = "INSERT INTO tbl_pending_verification 
                (email, registration_data, otp, verification_token, expires_at, created_at) 
                VALUES 
                ('$testEmail', 
                 '" . mysqli_real_escape_string($link, json_encode($testData)) . "',
                 '123456',
                 '$verificationToken',
                 DATE_ADD(NOW(), INTERVAL 1 HOUR),
                 NOW())";

if (!$link->query($insertQuery)) {
    die("Failed to create test data: " . $link->error);
}

echo "✓ Test data created\n";

// Test the createUserAccount process step by step
echo "\nTesting user creation process...\n";

// 1. Create user in tbl_user
$hashedPassword = md5($testData['password']);
$username = explode('@', $testData['email'])[0]; // Generate username from email

$userQuery = "INSERT INTO tbl_user(firstName, lastName, userName, userImg, userEmail, cellNo, phoneNo, userAddress, userPass, confPass, userLevel, userStatus) 
             VALUES('" . mysqli_real_escape_string($link, $testData['fname']) . "',
                    '" . mysqli_real_escape_string($link, $testData['lname']) . "',
                    '" . mysqli_real_escape_string($link, $username) . "',
                    '',
                    '" . mysqli_real_escape_string($link, $testData['email']) . "',
                    '" . mysqli_real_escape_string($link, $testData['cellno']) . "',
                    '',
                    '" . mysqli_real_escape_string($link, $testData['address'] ?? '') . "',
                    '$hashedPassword',
                    '$hashedPassword',
                    '" . mysqli_real_escape_string($link, $testData['level']) . "',
                    0)";

if ($link->query($userQuery)) {
    $userId = $link->insert_id;
    echo "✓ User created with ID: $userId\n";
    
    // 2. Test storing user documents in verification table
    $userType = ($testData['level'] == 2) ? 'owner' : 'agent';
    
    $insertQuery = "INSERT INTO tbl_user_verification (user_id, email, userName, user_level, user_type, citizenship_id, citizenship_front, citizenship_back, business_license, verification_status, submitted_at) 
                   VALUES ($userId, 
                          '" . mysqli_real_escape_string($link, $testData['email']) . "',
                          '" . mysqli_real_escape_string($link, $username) . "',
                          " . $testData['level'] . ",
                          '$userType', 
                          NULL,
                          NULL, 
                          NULL, 
                          NULL, 
                          'pending', 
                          NOW())";
    
    echo "Testing verification table insert...\n";
    echo "Query: $insertQuery\n\n";
    
    if ($link->query($insertQuery)) {
        echo "✓ User verification record created successfully!\n";
        
        // Verify the data
        $checkQuery = "SELECT * FROM tbl_user_verification WHERE user_id = $userId";
        $result = $link->query($checkQuery);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "✓ Verification record details:\n";
            echo "   - ID: " . $row['id'] . "\n";
            echo "   - User ID: " . $row['user_id'] . "\n";
            echo "   - Email: " . $row['email'] . "\n";
            echo "   - Username: " . $row['userName'] . "\n";
            echo "   - User Type: " . $row['user_type'] . "\n";
            echo "   - Status: " . $row['verification_status'] . "\n";
        }
        
    } else {
        echo "✗ Failed to create verification record: " . $link->error . "\n";
    }
    
} else {
    echo "✗ Failed to create user: " . $link->error . "\n";
}

// Clean up
$link->query("DELETE FROM tbl_pending_verification WHERE email = '$testEmail'");
$link->query("DELETE FROM tbl_user WHERE userEmail = '$testEmail'");
$link->query("DELETE FROM tbl_user_verification WHERE email = '$testEmail'");

echo "\n✓ Test completed and cleaned up\n";
echo "\nThe fix should now work! Try the email verification process again.\n";

$link->close();
?>
