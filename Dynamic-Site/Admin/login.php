<?php
session_start();
include "../lib/Database.php";

$db = new Database();
$loginMsg = "";

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($db->link, $_POST['username']);
    $password = md5($_POST['password']);
    
    // First check if admin exists in tbl_admin_users
    $adminQuery = "SELECT * FROM tbl_admin_users WHERE (username = '$username' OR email = '$username') AND password = '$password' AND status = 'active'";
    $adminResult = $db->select($adminQuery);
    
    if ($adminResult && $adminResult->num_rows > 0) {
        $admin = $adminResult->fetch_assoc();
        
        // Set session variables
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_name'] = $admin['full_name'];
        
        // Log admin login
        $loginQuery = "INSERT INTO tbl_admin_logs (admin_id, action, description, ip_address, user_agent) 
                       VALUES ('" . $admin['id'] . "', 'login', 'Admin logged in', '" . $_SERVER['REMOTE_ADDR'] . "', '" . $_SERVER['HTTP_USER_AGENT'] . "')";
        $db->insert($loginQuery);
        
        // Update last login
        $updateQuery = "UPDATE tbl_admin_users SET last_login = NOW() WHERE id = " . $admin['id'];
        $db->update($updateQuery);
        
        header("Location: dashboard.php");
        exit();
    } else {
        $loginMsg = "❌ Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Property Nepal</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
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
            text-align: left;
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
        
        .btn-login {
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
        
        .btn-login:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .footer-links {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .footer-links a {
            color: #667eea;
            text-decoration: none;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        .admin-info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: left;
            font-size: 14px;
            color: #0c5460;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">🏠 Property Nepal</div>
        <div class="subtitle">Admin Dashboard Login</div>
        
        <?php echo $loginMsg; ?>
        
        <div class="admin-info">
            <strong>🔐 Admin Access Only</strong><br>
            This area is restricted to authorized administrators only.
            Please use your admin credentials to access the dashboard.
        </div>
        
        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="username">👤 Username or Email</label>
                <input type="text" name="username" id="username" required 
                       placeholder="Enter your username or email"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">🔒 Password</label>
                <input type="password" name="password" id="password" required 
                       placeholder="Enter your password">
            </div>
            
            <button type="submit" name="login" class="btn-login">
                🚀 Login to Dashboard
            </button>
        </form>
        
        <div class="footer-links">
            <a href="../index.php">← Back to Website</a>
        </div>
    </div>
    
    <script>
        // Focus on username field when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        // Handle Enter key submission
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('.login-form').submit();
            }
        });
    </script>
</body>
</html>
