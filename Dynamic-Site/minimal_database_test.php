<?php
// Ultra-minimal Database conflict test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing Database class conflict...\n";

// Include header first (which includes Database)
include_once 'inc/header.php';
echo "1. Header included\n";

// Now try to include PreRegistrationVerification 
include_once 'classes/PreRegistrationVerification.php';
echo "2. PreRegistrationVerification included\n";

// Try to create instance
$test = new PreRegistrationVerification();
echo "3. Instance created successfully\n";

echo "✓ No Database class conflict detected!\n";
?>
