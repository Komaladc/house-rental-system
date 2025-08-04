<?php
// Database Setup for OTP Email Verification System
include 'config/config.php';
include 'lib/Database.php';

$db = new Database();

echo "<h2>🗄️ Setting up OTP Email Verification Database</h2>";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; background: #f8f9fa; border-radius: 10px;'>";

// Check current database structure
echo "<h3>📋 Checking Current Database Structure...</h3>";

// Check if tbl_user exists
$checkUserTable = "SHOW TABLES LIKE 'tbl_user'";
$userTableExists = $db->select($checkUserTable);

if(!$userTableExists || $userTableExists->num_rows == 0) {
    echo "<p style='color: red;'>❌ Error: tbl_user table not found. Please make sure your basic database is set up first.</p>";
    echo "<p>Please import the main database file: <code>1-Database/db_rental.sql</code></p>";
    exit();
}

echo "<p style='color: green;'>✅ tbl_user table found</p>";

// Check if email_verified column exists
$checkEmailVerified = "SHOW COLUMNS FROM tbl_user LIKE 'email_verified'";
$emailVerifiedExists = $db->select($checkEmailVerified);

if($emailVerifiedExists && $emailVerifiedExists->num_rows > 0) {
    echo "<p style='color: green;'>✅ email_verified column already exists</p>";
} else {
    echo "<p style='color: orange;'>⚠️ email_verified column missing - will be added</p>";
}

// Check if tbl_otp exists
$checkOtpTable = "SHOW TABLES LIKE 'tbl_otp'";
$otpTableExists = $db->select($checkOtpTable);

if($otpTableExists && $otpTableExists->num_rows > 0) {
    echo "<p style='color: green;'>✅ tbl_otp table already exists</p>";
} else {
    echo "<p style='color: orange;'>⚠️ tbl_otp table missing - will be created</p>";
}

echo "<hr>";

// Now run the setup
echo "<h3>🔧 Running Database Setup...</h3>";

$errors = [];
$success = [];

// 1. Create OTP table
if(!$otpTableExists || $otpTableExists->num_rows == 0) {
    $createOtpTable = "
    CREATE TABLE IF NOT EXISTS `tbl_otp` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `email` varchar(255) NOT NULL,
      `otp` varchar(6) NOT NULL,
      `purpose` enum('registration','password_reset','email_change') NOT NULL DEFAULT 'registration',
      `expires_at` datetime NOT NULL,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `is_used` tinyint(1) NOT NULL DEFAULT 0,
      PRIMARY KEY (`id`),
      KEY `email` (`email`),
      KEY `expires_at` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $result = $db->link->query($createOtpTable);
    if($result) {
        $success[] = "✅ Created tbl_otp table successfully";
    } else {
        $errors[] = "❌ Failed to create tbl_otp table: " . $db->link->error;
    }
}

// 2. Add email_verified column
if(!$emailVerifiedExists || $emailVerifiedExists->num_rows == 0) {
    $addEmailVerified = "ALTER TABLE `tbl_user` ADD COLUMN `email_verified` tinyint(1) NOT NULL DEFAULT 0 AFTER `userEmail`";
    $result = $db->link->query($addEmailVerified);
    if($result) {
        $success[] = "✅ Added email_verified column to tbl_user";
    } else {
        $errors[] = "❌ Failed to add email_verified column: " . $db->link->error;
    }
}

// 3. Add verification_token column
$checkVerificationToken = "SHOW COLUMNS FROM tbl_user LIKE 'verification_token'";
$verificationTokenExists = $db->select($checkVerificationToken);

if(!$verificationTokenExists || $verificationTokenExists->num_rows == 0) {
    $addVerificationToken = "ALTER TABLE `tbl_user` ADD COLUMN `verification_token` varchar(32) NULL AFTER `email_verified`";
    $result = $db->link->query($addVerificationToken);
    if($result) {
        $success[] = "✅ Added verification_token column to tbl_user";
    } else {
        $errors[] = "❌ Failed to add verification_token column: " . $db->link->error;
    }
}

// 4. Create index for better performance
$createIndex = "CREATE INDEX IF NOT EXISTS idx_user_email_verified ON tbl_user(userEmail, email_verified)";
$result = $db->link->query($createIndex);
if($result) {
    $success[] = "✅ Created database index for better performance";
} else {
    $errors[] = "❌ Failed to create index: " . $db->link->error;
}

// Display results
echo "<h3>📊 Setup Results:</h3>";

if(!empty($success)) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ Successful Operations:</h4>";
    foreach($success as $msg) {
        echo "<p>$msg</p>";
    }
    echo "</div>";
}

if(!empty($errors)) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ Errors:</h4>";
    foreach($errors as $msg) {
        echo "<p>$msg</p>";
    }
    echo "</div>";
}

if(empty($errors)) {
    echo "<div style='background: #d1ecf1; color: #0c5460; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 Database Setup Complete!</h3>";
    echo "<p>Your database is now ready for the OTP email verification system.</p>";
    echo "<p><strong>What's been set up:</strong></p>";
    echo "<ul>";
    echo "<li>📧 OTP table for storing verification codes</li>";
    echo "<li>🔐 Email verification columns in user table</li>";
    echo "<li>⚡ Database indexes for better performance</li>";
    echo "</ul>";
    
    echo "<h4>🚀 Next Steps:</h4>";
    echo "<ol>";
    echo "<li><a href='signup.php' style='color: #0c5460; font-weight: bold;'>Test Registration</a> with your real email address</li>";
    echo "<li>Check your email for the OTP verification code</li>";
    echo "<li><a href='verify_email.php' style='color: #0c5460; font-weight: bold;'>Verify your email</a> with the received code</li>";
    echo "<li><a href='signin.php' style='color: #0c5460; font-weight: bold;'>Sign in</a> to your verified account</li>";
    echo "</ol>";
    
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='test_otp_system.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>🧪 Go to Testing Page</a>";
    echo "</div>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>⚠️ Setup Incomplete</h3>";
    echo "<p>Some errors occurred during setup. Please check the error messages above and try again.</p>";
    echo "<p>You may need to:</p>";
    echo "<ul>";
    echo "<li>Check your database connection settings</li>";
    echo "<li>Ensure your MySQL server is running</li>";
    echo "<li>Verify database permissions</li>";
    echo "</ul>";
    echo "</div>";
}

// Show current table structure
echo "<h3>📋 Current Database Structure:</h3>";
echo "<div style='background: white; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";

$showUserColumns = "SHOW COLUMNS FROM tbl_user";
$userColumns = $db->select($showUserColumns);

if($userColumns) {
    echo "<h4>tbl_user columns:</h4>";
    echo "<table style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'><th style='border: 1px solid #ddd; padding: 8px;'>Column</th><th style='border: 1px solid #ddd; padding: 8px;'>Type</th><th style='border: 1px solid #ddd; padding: 8px;'>Default</th></tr>";
    
    while($row = $userColumns->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['Field'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['Type'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$showOtpColumns = "SHOW COLUMNS FROM tbl_otp";
$otpColumns = $db->select($showOtpColumns);

if($otpColumns) {
    echo "<h4>tbl_otp columns:</h4>";
    echo "<table style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'><th style='border: 1px solid #ddd; padding: 8px;'>Column</th><th style='border: 1px solid #ddd; padding: 8px;'>Type</th><th style='border: 1px solid #ddd; padding: 8px;'>Default</th></tr>";
    
    while($row = $otpColumns->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['Field'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $row['Type'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "</div>";
echo "</div>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    margin: 0;
    padding: 20px;
}

h2, h3, h4 {
    color: #2c3e50;
}

code {
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
}

a {
    color: #007bff;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
</style>
