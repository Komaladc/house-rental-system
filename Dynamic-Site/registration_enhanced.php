<?php
/**
 * Enhanced Registration Process
 * Ensures owners/agents are created with proper verification status for admin dashboard
 */

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include required files
include 'lib/Database.php';

$db = new Database();
$registrationMsg = "";
$showForm = true;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    
    // Basic validation
    $errors = array();
    
    // Required fields validation
    $required_fields = array('fname', 'lname', 'username', 'email', 'cellno', 'password', 'cnf_password', 'level');
    foreach($required_fields as $field) {
        if(empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
    }
    
    // Email validation
    if(!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Password match validation
    if(!empty($_POST['password']) && !empty($_POST['cnf_password']) && $_POST['password'] != $_POST['cnf_password']) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if email already exists
    if(!empty($_POST['email'])) {
        $checkEmail = $db->select("SELECT * FROM tbl_user WHERE userEmail = '" . mysqli_real_escape_string($db->link, $_POST['email']) . "'");
        if($checkEmail && $checkEmail->num_rows > 0) {
            $errors[] = "Email already registered";
        }
    }
    
    // Check if username already exists
    if(!empty($_POST['username'])) {
        $checkUsername = $db->select("SELECT * FROM tbl_user WHERE userName = '" . mysqli_real_escape_string($db->link, $_POST['username']) . "'");
        if($checkUsername && $checkUsername->num_rows > 0) {
            $errors[] = "Username already taken";
        }
    }
    
    if(empty($errors)) {
        // Prepare data
        $fname = mysqli_real_escape_string($db->link, $_POST['fname']);
        $lname = mysqli_real_escape_string($db->link, $_POST['lname']);
        $username = mysqli_real_escape_string($db->link, $_POST['username']);
        $email = mysqli_real_escape_string($db->link, $_POST['email']);
        $cellno = mysqli_real_escape_string($db->link, $_POST['cellno']);
        $password = md5($_POST['password']);
        $level = intval($_POST['level']);
        $address = mysqli_real_escape_string($db->link, $_POST['address'] ?? '');
        
        // Determine user status based on level
        // Level 1 (House Seekers) = Active immediately (userStatus = 1)
        // Level 2 (Owners/Agents) = Pending admin verification (userStatus = 0)
        $userStatus = ($level == 1) ? 1 : 0;
        
        // Create user account
        $userQuery = "INSERT INTO tbl_user (firstName, lastName, userName, userImg, userEmail, cellNo, phoneNo, userAddress, userPass, confPass, userLevel, userStatus, created_at) 
                     VALUES ('$fname', '$lname', '$username', '', '$email', '$cellno', '', '$address', '$password', '$password', $level, $userStatus, NOW())";
        
        $userId = $db->insert($userQuery);
        
        if($userId) {
            // If user is level 2 (Owner/Agent), add to verification table
            if($level == 2) {
                $userType = 'Owner'; // You can add logic to distinguish between Owner and Agent
                
                $verificationQuery = "INSERT INTO tbl_user_verification (user_id, email, userName, user_level, user_type, verification_status, submitted_at) 
                                     VALUES ($userId, '$email', '$username', $level, '$userType', 'pending', NOW())";
                
                $verificationResult = $db->insert($verificationQuery);
                
                if($verificationResult) {
                    $registrationMsg = "
                    <div class='alert alert-success'>
                        🎉 <strong>Registration Successful!</strong><br><br>
                        
                        <strong>Account Created:</strong> Your account has been created successfully.<br>
                        <strong>Email:</strong> $email<br>
                        <strong>Username:</strong> $username<br>
                        <strong>Account Type:</strong> Property Owner/Agent<br><br>
                        
                        <div class='alert alert-warning'>
                            📋 <strong>Admin Verification Required</strong><br>
                            As a property owner/agent, your account requires admin approval before you can access owner features.<br><br>
                            
                            <strong>What happens next:</strong><br>
                            • An admin will review your registration<br>
                            • You'll receive an email notification when approved<br>
                            • After approval, you can sign in and start posting properties<br><br>
                            
                            <strong>Timeline:</strong> Verification usually takes 1-2 business days.
                        </div>
                        
                        <div class='mt-3'>
                            <a href='signin.php' class='btn btn-primary'>Try to Sign In</a>
                            <a href='index.php' class='btn btn-secondary'>Back to Home</a>
                        </div>
                    </div>";
                } else {
                    $registrationMsg = "
                    <div class='alert alert-warning'>
                        ⚠️ <strong>Account Created with Issues</strong><br>
                        Your user account was created but there was an issue with the verification record.<br>
                        Please contact admin for manual verification.
                    </div>";
                }
            } else {
                // Level 1 user (House Seeker) - active immediately
                $registrationMsg = "
                <div class='alert alert-success'>
                    🎉 <strong>Registration Successful!</strong><br><br>
                    
                    <strong>Account Created:</strong> Your house seeker account is ready to use!<br>
                    <strong>Email:</strong> $email<br>
                    <strong>Username:</strong> $username<br><br>
                    
                    You can now sign in and start browsing properties.<br><br>
                    
                    <div class='mt-3'>
                        <a href='signin.php' class='btn btn-primary'>Sign In Now</a>
                        <a href='property_list.php' class='btn btn-secondary'>Browse Properties</a>
                    </div>
                </div>";
            }
            
            $showForm = false;
            
        } else {
            $registrationMsg = "
            <div class='alert alert-danger'>
                ❌ <strong>Registration Failed</strong><br>
                There was an error creating your account. Please try again.<br>
                Error: " . $db->link->error . "
            </div>";
        }
        
    } else {
        $registrationMsg = "
        <div class='alert alert-danger'>
            ❌ <strong>Registration Errors:</strong><br>
            " . implode("<br>", $errors) . "
        </div>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>House Rental Registration - Enhanced</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="mystyle.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">🏠 House Rental System Registration</h3>
                </div>
                <div class="card-body">
                    
                    <?php if(!empty($registrationMsg)) echo $registrationMsg; ?>
                    
                    <?php if($showForm): ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" name="fname" class="form-control" required value="<?php echo $_POST['fname'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" name="lname" class="form-control" required value="<?php echo $_POST['lname'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Username *</label>
                                    <input type="text" name="username" class="form-control" required value="<?php echo $_POST['username'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" name="email" class="form-control" required value="<?php echo $_POST['email'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone Number *</label>
                                    <input type="text" name="cellno" class="form-control" required value="<?php echo $_POST['cellno'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Account Type *</label>
                                    <select name="level" class="form-select" required onchange="showLevelInfo(this.value)">
                                        <option value="">Select Account Type</option>
                                        <option value="1" <?php echo (isset($_POST['level']) && $_POST['level'] == '1') ? 'selected' : ''; ?>>🏠 House Seeker</option>
                                        <option value="2" <?php echo (isset($_POST['level']) && $_POST['level'] == '2') ? 'selected' : ''; ?>>🏘️ Property Owner/Agent</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?php echo $_POST['address'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Password *</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Confirm Password *</label>
                                    <input type="password" name="cnf_password" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Level-specific information -->
                        <div id="level-info" class="alert" style="display: none;">
                            <div id="level-1-info" style="display: none;">
                                <strong>🏠 House Seeker Account:</strong><br>
                                • Browse and search properties<br>
                                • Book properties directly<br>
                                • Manage your bookings<br>
                                • Account is activated immediately
                            </div>
                            <div id="level-2-info" style="display: none;">
                                <strong>🏘️ Property Owner/Agent Account:</strong><br>
                                • Post and manage properties<br>
                                • Manage bookings and inquiries<br>
                                • Access analytics and reports<br>
                                • <strong style="color: #856404;">⚠️ Requires admin verification before activation</strong>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="register" class="btn btn-primary btn-lg">
                                📝 Create Account
                            </button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p>Already have an account? <a href="signin.php">Sign In</a></p>
                            <p><a href="index.php">← Back to Home</a></p>
                        </div>
                        
                    </form>
                    
                    <?php endif; ?>
                    
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
function showLevelInfo(level) {
    const infoDiv = document.getElementById('level-info');
    const level1Info = document.getElementById('level-1-info');
    const level2Info = document.getElementById('level-2-info');
    
    if (level === '1') {
        infoDiv.className = 'alert alert-info';
        infoDiv.style.display = 'block';
        level1Info.style.display = 'block';
        level2Info.style.display = 'none';
    } else if (level === '2') {
        infoDiv.className = 'alert alert-warning';
        infoDiv.style.display = 'block';
        level1Info.style.display = 'none';
        level2Info.style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
}
</script>

<style>
.alert {
    border-radius: 8px;
    border: 1px solid;
}
.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}
.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}
.alert-warning {
    background-color: #fff3cd;
    border-color: #ffeaa7;
    color: #856404;
}
.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}
.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: none;
}
</style>

</body>
</html>
