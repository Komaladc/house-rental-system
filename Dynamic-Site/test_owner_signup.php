<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Testing Owner Signup Form Submission</h1>";

// Test the form processing logic
include "lib/Database.php";
include "classes/PreRegistrationVerification.php";
include "classes/EmailOTP.php";

global $db;
$db = new Database();
$preReg = new PreRegistrationVerification();

echo "✅ All classes loaded successfully<br><br>";

// Test email validation
$testEmail = "test@gmail.com";
$isRealEmail = $preReg->isRealEmail($testEmail);
echo "Email validation test for '$testEmail': " . ($isRealEmail ? '✅ Valid' : '❌ Invalid') . "<br>";

// Test upload directory
$uploadDir = "uploads/documents/";
if (!is_dir($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        echo "✅ Upload directory created: $uploadDir<br>";
    } else {
        echo "❌ Failed to create upload directory: $uploadDir<br>";
    }
} else {
    echo "✅ Upload directory exists: $uploadDir<br>";
}

if (is_writable($uploadDir)) {
    echo "✅ Upload directory is writable<br>";
} else {
    echo "❌ Upload directory is not writable<br>";
}

echo "<br><strong>Form is ready for testing!</strong><br>";
echo "<a href='signup_enhanced.php'>→ Test the Signup Form</a><br>";
echo "<a href='minimal_signup_test.php'>→ Test Minimal Form</a>";
?>
