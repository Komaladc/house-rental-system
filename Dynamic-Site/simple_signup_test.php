<!DOCTYPE html>
<html>
<head>
    <title>🆕 Test Signup - Property Finder Nepal</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="mystyle.css"/>
    <style>
        .test-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn {
            background: #27ae60;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .btn:hover {
            background: #229954;
        }
        .error {
            color: #e74c3c;
            background: #fdeaea;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .success {
            color: #27ae60;
            background: #d5f4e6;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Minimal includes for testing
include'lib/Session.php';
Session::init();
include'lib/Database.php';

$message = "";
$showForm = true;

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['test_register'])) {
    try {
        $db = new Database();
        
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $email = $_POST['email'];
        $password = md5($_POST['password']);
        
        // Simple validation
        if (empty($fname) || empty($lname) || empty($email) || empty($_POST['password'])) {
            $message = '<div class="error">❌ All fields are required!</div>';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = '<div class="error">❌ Invalid email format!</div>';
        } else {
            // Check if email exists
            $checkQuery = "SELECT userEmail FROM tbl_user WHERE userEmail = '" . mysqli_real_escape_string($db->link, $email) . "'";
            $checkResult = $db->select($checkQuery);
            
            if ($checkResult && mysqli_num_rows($checkResult) > 0) {
                $message = '<div class="error">❌ Email already exists!</div>';
            } else {
                // Insert user directly (simplified for testing)
                $insertQuery = "INSERT INTO tbl_user (firstName, lastName, userEmail, userPass, confPass, userLevel, is_email_verified) 
                               VALUES ('" . mysqli_real_escape_string($db->link, $fname) . "',
                                       '" . mysqli_real_escape_string($db->link, $lname) . "',
                                       '" . mysqli_real_escape_string($db->link, $email) . "',
                                       '$password',
                                       '$password',
                                       1,
                                       1)";
                
                if ($db->insert($insertQuery)) {
                    $message = '<div class="success">✅ Account created successfully! <a href="signin.php">Sign in now</a></div>';
                    $showForm = false;
                } else {
                    $message = '<div class="error">❌ Failed to create account. Database error.</div>';
                }
            }
        }
    } catch (Exception $e) {
        $message = '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
    }
}
?>

<div class="test-container">
    <h1>🧪 Simple Signup Test</h1>
    <p>This is a simplified signup form to test basic functionality.</p>
    
    <?php echo $message; ?>
    
    <?php if ($showForm) { ?>
    <form method="POST" action="">
        <div class="form-group">
            <label>👤 First Name:</label>
            <input type="text" name="fname" required>
        </div>
        
        <div class="form-group">
            <label>👤 Last Name:</label>
            <input type="text" name="lname" required>
        </div>
        
        <div class="form-group">
            <label>📧 Email:</label>
            <input type="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label>🔐 Password:</label>
            <input type="password" name="password" required>
        </div>
        
        <div class="form-group">
            <button type="submit" name="test_register" class="btn">
                🆕 Create Test Account
            </button>
        </div>
    </form>
    
    <p><small>⚠️ This is a simplified test. The full signup with email verification is in <a href="signup_with_verification.php">signup_with_verification.php</a></small></p>
    <?php } ?>
    
    <hr>
    <h3>🔧 Debug Links:</h3>
    <a href="fix_database.php">🛠️ Fix Database</a> | 
    <a href="test_signup_debug.php">🚨 Debug Test</a> | 
    <a href="signup_with_verification.php">📧 Full Signup</a> |
    <a href="signin.php">🔑 Sign In</a>
</div>

</body>
</html>
