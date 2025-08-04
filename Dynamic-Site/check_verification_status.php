<?php
// User Verification Status Check
session_start();
include "lib/Database.php";

$db = new Database();
$statusMsg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['check_status'])) {
    $email = mysqli_real_escape_string($db->link, $_POST['email']);
    
    if (!empty($email)) {
        // Check user status
        $userQuery = "SELECT firstName, lastName, userEmail, userLevel, verification_status, requires_verification, email_verified, document_verified, status 
                     FROM tbl_user WHERE userEmail = '$email'";
        $userResult = $db->select($userQuery);
        
        if ($userResult && $userResult->num_rows > 0) {
            $user = $userResult->fetch_assoc();
            
            $levelText = '';
            switch($user['userLevel']) {
                case 1: $levelText = 'Property Seeker'; break;
                case 2: $levelText = 'Property Owner'; break;
                case 3: $levelText = 'Real Estate Agent'; break;
                default: $levelText = 'Unknown'; break;
            }
            
            $statusMsg = "<div class='status-result'>";
            $statusMsg .= "<h3>📋 Account Status for " . htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) . "</h3>";
            $statusMsg .= "<div class='status-grid'>";
            $statusMsg .= "<div class='status-item'><strong>Account Type:</strong> " . $levelText . "</div>";
            $statusMsg .= "<div class='status-item'><strong>Email Verified:</strong> " . ($user['email_verified'] ? '✅ Yes' : '❌ No') . "</div>";
            
            if ($user['requires_verification']) {
                $statusMsg .= "<div class='status-item'><strong>Admin Verification:</strong> ";
                switch($user['verification_status']) {
                    case 'pending':
                        $statusMsg .= "<span style='color: orange;'>⏳ Pending</span>";
                        break;
                    case 'verified':
                        $statusMsg .= "<span style='color: green;'>✅ Approved</span>";
                        break;
                    case 'rejected':
                        $statusMsg .= "<span style='color: red;'>❌ Rejected</span>";
                        break;
                    default:
                        $statusMsg .= "<span style='color: gray;'>Unknown</span>";
                        break;
                }
                $statusMsg .= "</div>";
                $statusMsg .= "<div class='status-item'><strong>Documents Verified:</strong> " . ($user['document_verified'] ? '✅ Yes' : '⏳ Pending') . "</div>";
            }
            
            $statusMsg .= "<div class='status-item'><strong>Account Status:</strong> " . ($user['status'] ? '<span style="color: green;">✅ Active</span>' : '<span style="color: red;">❌ Inactive</span>') . "</div>";
            $statusMsg .= "</div>";
            
            // Show appropriate message
            if ($user['requires_verification'] && $user['verification_status'] == 'pending') {
                $statusMsg .= "<div class='alert alert-warning'>";
                $statusMsg .= "⏳ <strong>Verification Pending</strong><br>";
                $statusMsg .= "Your account is waiting for admin approval. You'll receive an email once your documents are verified.<br>";
                $statusMsg .= "This usually takes 1-2 business days.";
                $statusMsg .= "</div>";
            } elseif ($user['requires_verification'] && $user['verification_status'] == 'rejected') {
                $statusMsg .= "<div class='alert alert-danger'>";
                $statusMsg .= "❌ <strong>Verification Rejected</strong><br>";
                $statusMsg .= "Your account verification was rejected. Please check your email for details and contact admin.";
                $statusMsg .= "</div>";
            } elseif ($user['verification_status'] == 'verified' || !$user['requires_verification']) {
                $statusMsg .= "<div class='alert alert-success'>";
                $statusMsg .= "✅ <strong>Account Verified</strong><br>";
                $statusMsg .= "You can sign in and use all features.";
                $statusMsg .= "</div>";
            }
            
            $statusMsg .= "</div>";
        } else {
            $statusMsg = "<div class='alert alert-error'>❌ No account found with this email address.</div>";
        }
    } else {
        $statusMsg = "<div class='alert alert-error'>❌ Please enter your email address.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Verification Status - Property Nepal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-check {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .btn-check:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status-result {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .status-grid {
            display: grid;
            gap: 10px;
            margin: 15px 0;
        }
        
        .status-item {
            padding: 10px;
            background: white;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        
        .footer-links {
            text-align: center;
            margin-top: 30px;
        }
        
        .footer-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 15px;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🏠 Property Nepal</div>
            <div class="subtitle">Check Your Verification Status</div>
        </div>
        
        <?php echo $statusMsg; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">📧 Email Address</label>
                <input type="email" name="email" id="email" required 
                       placeholder="Enter your registered email address"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <button type="submit" name="check_status" class="btn-check">
                🔍 Check Status
            </button>
        </form>
        
        <div class="footer-links">
            <a href="signin.php">← Sign In</a>
            <a href="signup_enhanced.php">Create Account</a>
            <a href="index.php">← Back to Website</a>
        </div>
    </div>
</body>
</html>
