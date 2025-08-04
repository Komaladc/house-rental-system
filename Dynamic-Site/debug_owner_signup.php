<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "lib/Database.php";
include "classes/PreRegistrationVerification.php";
include "classes/EmailOTP.php";

// Create database connection
$db = new Database();
$preReg = new PreRegistrationVerification();

$registrationMsg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<h2>Debug: Form Submission</h2>";
    echo "<h3>POST Data:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    if (isset($_FILES) && !empty($_FILES)) {
        echo "<h3>FILES Data:</h3>";
        echo "<pre>";
        print_r($_FILES);
        echo "</pre>";
    }
    
    if (isset($_POST['signup'])) {
        echo "<h3>Processing Registration...</h3>";
        
        // Simple test registration data
        $registrationData = [
            'fname' => $_POST['fname'],
            'lname' => $_POST['lname'],
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'cellno' => $_POST['cellno'],
            'address' => $_POST['address'] ?? '',
            'password' => $_POST['password'],
            'level' => $_POST['level'],
            'requires_verification' => in_array($_POST['level'], [2, 3]),
            'uploaded_files' => [],
            'citizenship_id' => $_POST['citizenship_id'] ?? ''
        ];
        
        echo "<h3>Registration Data:</h3>";
        echo "<pre>";
        print_r($registrationData);
        echo "</pre>";
        
        echo "<h3>Testing Email Validation:</h3>";
        $isRealEmail = $preReg->isRealEmail($_POST['email']);
        echo "Email '" . $_POST['email'] . "' is " . ($isRealEmail ? 'VALID' : 'INVALID') . "<br>";
        
        echo "<h3>Calling initiateEmailVerification:</h3>";
        $result = $preReg->initiateEmailVerification($registrationData);
        
        echo "<h3>Result:</h3>";
        echo "<pre>";
        print_r($result);
        echo "</pre>";
        
        if (isset($result['message'])) {
            $registrationMsg = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Signup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        input, select { padding: 8px; width: 200px; }
        button { padding: 10px 20px; }
        pre { background: #f0f0f0; padding: 10px; }
    </style>
</head>
<body>
    <h1>Debug Signup Form</h1>
    
    <?php if ($registrationMsg): ?>
        <div style="background: #f0f0f0; padding: 15px; margin: 15px 0;">
            <?php echo $registrationMsg; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>First Name:</label><br>
            <input type="text" name="fname" required>
        </div>
        
        <div class="form-group">
            <label>Last Name:</label><br>
            <input type="text" name="lname" required>
        </div>
        
        <div class="form-group">
            <label>Username:</label><br>
            <input type="text" name="username" required>
        </div>
        
        <div class="form-group">
            <label>Email:</label><br>
            <input type="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label>Phone:</label><br>
            <input type="tel" name="cellno" required>
        </div>
        
        <div class="form-group">
            <label>Address:</label><br>
            <input type="text" name="address">
        </div>
        
        <div class="form-group">
            <label>Password:</label><br>
            <input type="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label>Account Type:</label><br>
            <select name="level" required>
                <option value="">Select</option>
                <option value="1">Property Seeker</option>
                <option value="2">Property Owner</option>
                <option value="3">Real Estate Agent</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Citizenship ID (for owners/agents):</label><br>
            <input type="text" name="citizenship_id">
        </div>
        
        <button type="submit" name="signup">Test Registration</button>
    </form>
</body>
</html>
