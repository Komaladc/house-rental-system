<?php
echo "PHP is working!<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "File exists: " . (file_exists('signup_enhanced.php') ? 'Yes' : 'No') . "<br>";

// Test database connection
try {
    include "lib/Database.php";
    $db = new Database();
    echo "Database connection: OK<br>";
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

// Test classes
try {
    include "classes/PreRegistrationVerification.php";
    echo "PreRegistrationVerification class: OK<br>";
} catch (Exception $e) {
    echo "PreRegistrationVerification error: " . $e->getMessage() . "<br>";
}

try {
    include "classes/EmailOTP.php";
    echo "EmailOTP class: OK<br>";
} catch (Exception $e) {
    echo "EmailOTP error: " . $e->getMessage() . "<br>";
}
?>
