<?php
// Minimal test signup form
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Test Signup</title></head><body>";
echo "<h1>Basic Signup Test</h1>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<h2>Form Submitted!</h2>";
    echo "<pre>";
    print_r($_POST);
    print_r($_FILES);
    echo "</pre>";
}

?>
<form method="POST" enctype="multipart/form-data">
    <p>Name: <input type="text" name="fname" required></p>
    <p>Email: <input type="email" name="email" required></p>
    <p>Account Type: 
        <select name="level" required>
            <option value="">Select</option>
            <option value="1">User</option>
            <option value="2">Owner</option>
            <option value="3">Agent</option>
        </select>
    </p>
    <p>Document: <input type="file" name="test_file"></p>
    <p><button type="submit" name="signup">Test Submit</button></p>
</form>
</body></html>
