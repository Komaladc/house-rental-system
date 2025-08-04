<?php
// Simple test to trace Database class inclusion
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Tracing Database Class Inclusion</h3>";

// Step 1: Include header which includes Database
echo "<p>Step 1: Including header.php...</p>";
include 'inc/header.php';
echo "<p>✓ Header included. Database class exists: " . (class_exists('Database') ? 'YES' : 'NO') . "</p>";

// Step 2: Try to include PreRegistrationVerification
echo "<p>Step 2: Including PreRegistrationVerification...</p>";
include_once 'classes/PreRegistrationVerification.php';
echo "<p>✓ PreRegistrationVerification included</p>";

// Step 3: Try to create instances
echo "<p>Step 3: Creating PreRegistrationVerification instance...</p>";
$preVerification = new PreRegistrationVerification();
echo "<p>✓ PreRegistrationVerification created</p>";

echo "<p><strong>All tests passed! No Database class conflict.</strong></p>";
?>
