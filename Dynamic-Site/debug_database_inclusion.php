<?php
// Debug Database inclusion step by step
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Inclusion Debug</h2>\n";

// Step 1: Check if Database class exists initially
echo "Step 1: Initial Database class check: " . (class_exists('Database', false) ? 'EXISTS' : 'NOT EXISTS') . "\n";

// Step 2: Include header
echo "Step 2: Including header...\n";
include 'inc/header.php';
echo "Step 2 complete: Database class check: " . (class_exists('Database', false) ? 'EXISTS' : 'NOT EXISTS') . "\n";

// Step 3: Check what files have been included
echo "Step 3: Included files:\n";
$included = get_included_files();
foreach($included as $file) {
    if (strpos($file, 'Database.php') !== false) {
        echo "  - DATABASE FILE: $file\n";
    }
}

// Step 4: Try to include PreRegistrationVerification
echo "Step 4: Including PreRegistrationVerification...\n";
include_once 'classes/PreRegistrationVerification.php';
echo "Step 4 complete: PreRegistrationVerification included\n";

echo "Step 5: Final included files check:\n";
$included = get_included_files();
foreach($included as $file) {
    if (strpos($file, 'Database.php') !== false) {
        echo "  - DATABASE FILE: $file\n";
    }
}

echo "\nAll steps completed successfully!\n";
?>
