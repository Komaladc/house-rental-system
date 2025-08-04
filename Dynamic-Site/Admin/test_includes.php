<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting include test...<br>";

try {
    echo "1. Testing header include...<br>";
    include "inc/header.php";
    echo "✅ Header included successfully<br>";
    
    echo "2. Testing Property class...<br>";
    if(class_exists('Property')) {
        echo "✅ Property class exists<br>";
        $pro_test = new Property();
        echo "✅ Property class instantiated<br>";
    } else {
        echo "❌ Property class not found<br>";
    }
    
    echo "3. Testing session...<br>";
    if(class_exists('Session')) {
        echo "✅ Session class exists<br>";
        echo "User Level: " . Session::get("userLevel") . "<br>";
        echo "User ID: " . Session::get("userId") . "<br>";
    } else {
        echo "❌ Session class not found<br>";
    }
    
    echo "4. Testing Category class...<br>";
    if(class_exists('Category')) {
        echo "✅ Category class exists<br>";
        $cat_test = new Category();
        echo "✅ Category class instantiated<br>";
    } else {
        echo "❌ Category class not found<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}

echo "<br>Test complete.";
?>
