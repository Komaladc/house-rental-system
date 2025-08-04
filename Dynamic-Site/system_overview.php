<?php
// Complete System Overview and Navigation
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Nepal - Complete System Overview</title>
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
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .subtitle {
            color: #666;
            font-size: 18px;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border-left: 5px solid #667eea;
        }
        
        .card h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .card p {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .card-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: transform 0.2s ease;
            display: inline-block;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .flow-section {
            background: #e7f3ff;
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .flow-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .step {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .step-number {
            background: #667eea;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🏠 Property Nepal</div>
            <div class="subtitle">Complete Verification System Overview</div>
        </div>
        
        <div class="alert alert-info">
            <h4>🎯 <strong>System Overview</strong></h4>
            <p>This is a comprehensive property rental system with robust email OTP verification and admin approval workflow for property owners and agents. Property seekers can register and use the system immediately after email verification, while owners and agents require additional admin verification of their citizenship documents.</p>
        </div>
        
        <div class="grid">
            <!-- User Registration -->
            <div class="card">
                <h3>📝 User Registration</h3>
                <p>Complete signup system with email OTP verification and document upload for owners/agents.</p>
                <div class="card-actions">
                    <a href="signup_enhanced.php" class="btn btn-primary">🆕 Sign Up</a>
                    <a href="signin.php" class="btn btn-success">🔓 Sign In</a>
                </div>
            </div>
            
            <!-- Admin Dashboard -->
            <div class="card">
                <h3>🔐 Admin Dashboard</h3>
                <p>Complete admin panel for user verification, management, and system oversight.</p>
                <div class="card-actions">
                    <a href="admin/login.php" class="btn btn-primary">🔐 Admin Login</a>
                    <a href="admin/dashboard.php" class="btn btn-info">📊 Dashboard</a>
                </div>
            </div>
            
            <!-- Verification Status -->
            <div class="card">
                <h3>📋 Verification Status</h3>
                <p>Check your account verification status and see what's needed for approval.</p>
                <div class="card-actions">
                    <a href="check_verification_status.php" class="btn btn-info">🔍 Check Status</a>
                </div>
            </div>
            
            <!-- System Setup -->
            <div class="card">
                <h3>🛠️ System Setup</h3>
                <p>Setup and diagnostic tools for ensuring the system works properly.</p>
                <div class="card-actions">
                    <a href="setup_admin_fix.php" class="btn btn-warning">🔧 Setup Admin</a>
                    <a href="test_verification_flow.php" class="btn btn-secondary">🧪 Test System</a>
                </div>
            </div>
        </div>
        
        <div class="flow-section">
            <h3>🔄 Complete User Registration Flow</h3>
            
            <div class="flow-steps">
                <div class="step">
                    <span class="step-number">1</span>
                    <strong>Choose Account Type</strong><br>
                    Property Seeker, Owner, or Agent
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <strong>Fill Registration Form</strong><br>
                    Basic info + citizenship docs (Owner/Agent)
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <strong>Email OTP Verification</strong><br>
                    Real email required, OTP sent via email
                </div>
                <div class="step">
                    <span class="step-number">4</span>
                    <strong>Account Created</strong><br>
                    Account created with appropriate status
                </div>
                <div class="step">
                    <span class="step-number">5</span>
                    <strong>Admin Verification</strong><br>
                    Owner/Agent docs reviewed by admin
                </div>
                <div class="step">
                    <span class="step-number">6</span>
                    <strong>System Access</strong><br>
                    Full access after verification
                </div>
            </div>
        </div>
        
        <div class="alert alert-warning">
            <h4>⚠️ <strong>Important Notes</strong></h4>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li><strong>Property Seekers:</strong> Can sign in immediately after email OTP verification</li>
                <li><strong>Property Owners & Agents:</strong> Need admin verification of citizenship documents</li>
                <li><strong>Email Verification:</strong> Required for all users, uses real email addresses only</li>
                <li><strong>Admin Verification:</strong> Usually takes 1-2 business days for owner/agent approval</li>
                <li><strong>Sign In Restrictions:</strong> Pending accounts cannot access the system until approved</li>
            </ul>
        </div>
        
        <div class="grid">
            <!-- Default Admin Credentials -->
            <div class="card">
                <h3>🔑 Default Admin Login</h3>
                <p><strong>URL:</strong> admin/login.php<br>
                <strong>Username:</strong> admin<br>
                <strong>Email:</strong> admin@propertynepal.com<br>
                <strong>Password:</strong> admin123</p>
                <div class="card-actions">
                    <a href="admin/login.php" class="btn btn-primary">🚀 Login Now</a>
                </div>
            </div>
            
            <!-- Quick Testing -->
            <div class="card">
                <h3>🧪 Quick Testing</h3>
                <p>Test the complete system with different user types and verification flows.</p>
                <div class="card-actions">
                    <a href="signup_enhanced.php?test=seeker" class="btn btn-success">👤 Test Seeker</a>
                    <a href="signup_enhanced.php?test=owner" class="btn btn-info">🏠 Test Owner</a>
                    <a href="signup_enhanced.php?test=agent" class="btn btn-warning">🏢 Test Agent</a>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #dee2e6;">
            <p style="color: #666;">
                🏠 Property Nepal - Complete House Rental Management System<br>
                <small>With Email OTP Verification & Admin Approval Workflow</small>
            </p>
        </div>
    </div>
</body>
</html>
