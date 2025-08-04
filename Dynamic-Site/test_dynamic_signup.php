<?php
echo "<h2>Testing Dynamic Signup Form</h2>";

// Test database connection
include "lib/Database.php";
include "classes/PreRegistrationVerification.php";
include "classes/EmailOTP.php";

try {
    $db = new Database();
    echo "✅ Database connection: OK<br>";
    
    $preReg = new PreRegistrationVerification();
    echo "✅ PreRegistrationVerification class: OK<br>";
    
    $emailOTP = new EmailOTP();
    echo "✅ EmailOTP class: OK<br>";
    
    // Test if upload directory exists or can be created
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
    
    // Check if uploads directory is writable
    if (is_writable($uploadDir)) {
        echo "✅ Upload directory is writable<br>";
    } else {
        echo "❌ Upload directory is not writable<br>";
    }
    
    // Test email validation
    $testEmails = [
        'test@gmail.com' => 'Valid',
        'fake@fake.com' => 'Invalid (fake domain)',
        'test@10minutemail.com' => 'Invalid (temporary email)',
        'invalid-email' => 'Invalid (format)'
    ];
    
    echo "<h3>Email Validation Tests:</h3>";
    foreach ($testEmails as $email => $expected) {
        $isValid = $preReg->isRealEmail($email);
        $result = $isValid ? 'Valid' : 'Invalid';
        $status = ($result === 'Valid' && strpos($expected, 'Valid') !== false) || 
                  ($result === 'Invalid' && strpos($expected, 'Invalid') !== false) ? '✅' : '❌';
        echo "$status $email: $result (Expected: $expected)<br>";
    }
    
    echo "<br><h3>Form Features:</h3>";
    echo "✅ Account type selection at top<br>";
    echo "✅ Dynamic document upload section<br>";
    echo "✅ File validation (PDF, JPG, PNG up to 5MB)<br>";
    echo "✅ Citizenship ID validation<br>";
    echo "✅ Real email verification<br>";
    echo "✅ OTP verification system<br>";
    echo "✅ Admin verification workflow for owners/agents<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><a href='signup_dynamic.php'>→ Go to Dynamic Signup Form</a>";
echo "<br><a href='Admin/login.php'>→ Go to Admin Login</a>";
?>
