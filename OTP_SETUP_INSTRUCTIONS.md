# 📧 EMAIL OTP VERIFICATION SYSTEM SETUP

## 🗄️ Database Setup (REQUIRED)

### Step 1: Run SQL Commands
Execute the following SQL commands in your MySQL database:

```sql
-- 1. Create OTP table
CREATE TABLE IF NOT EXISTS `tbl_otp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `purpose` enum('registration','password_reset','email_change') NOT NULL DEFAULT 'registration',
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Add email verification columns to user table
ALTER TABLE `tbl_user` ADD COLUMN `email_verified` tinyint(1) NOT NULL DEFAULT 0 AFTER `userEmail`;
ALTER TABLE `tbl_user` ADD COLUMN `verification_token` varchar(32) NULL AFTER `email_verified`;

-- 3. Create index for better performance
CREATE INDEX idx_user_email_verified ON tbl_user(userEmail, email_verified);
```

### Step 2: Import SQL File (Alternative)
You can also import the SQL file:
- File: `1-Database/otp_verification.sql`
- Import this file into your database using phpMyAdmin or MySQL command line

## ⚙️ Email Configuration

### Option 1: PHP mail() Function (Default - Works with XAMPP)
The system is configured to use PHP's built-in `mail()` function which works with XAMPP's Mercury Mail.

### Option 2: SMTP Configuration (Recommended for Production)
For production use, modify `classes/EmailOTP.php` to use SMTP:

1. Install PHPMailer:
```bash
composer require phpmailer/phpmailer
```

2. Update the `sendOTP()` method in `EmailOTP.php` to use SMTP instead of `mail()`

## 🔧 System Features

### ✅ Registration with Email Verification
1. User fills registration form
2. Account created but marked as `email_verified = 0`
3. 6-digit OTP sent to user's email
4. User must verify email before accessing account
5. Upon verification: `email_verified = 1` and auto-login

### ✅ Login with Email Verification Check
1. User attempts to login
2. System checks if `email_verified = 1`
3. If not verified: New OTP sent, redirect to verification
4. If verified: Normal login process

### ✅ Password Reset with OTP
1. User enters email on forgot password page
2. 6-digit OTP sent to email
3. User enters OTP to verify identity
4. User sets new password
5. Password updated and redirect to login

### ✅ Email Templates
- **Registration**: Welcome message with Nepal branding
- **Password Reset**: Security-focused message
- **Multi-language**: English with Nepali greetings

## 📱 User Experience

### Registration Flow:
1. `signup.php` → Fill form
2. Submit → Account created + OTP sent
3. `verify_email.php` → Enter 6-digit code
4. Verified → Auto-login to dashboard

### Login Flow:
1. `signin.php` → Enter credentials
2. If unverified → New OTP sent → `verify_email.php`
3. If verified → Direct login to dashboard

### Password Reset Flow:
1. `forgot_password_otp.php` → Enter email
2. OTP sent → Enter verification code
3. Verified → Enter new password
4. Password updated → Redirect to login

## 🛡️ Security Features

### OTP Security:
- ✅ 6-digit random codes
- ✅ 10-minute expiration
- ✅ One-time use only
- ✅ Purpose-specific (registration/password_reset)
- ✅ Database cleanup of expired codes

### Email Validation:
- ✅ Strict email format validation
- ✅ Real-world domain requirements
- ✅ Fake domain rejection
- ✅ Client-side + Server-side validation

### Account Security:
- ✅ Cannot login without email verification
- ✅ Cannot access features without verification
- ✅ Secure password reset process
- ✅ No password reset without email access

## 📧 Email Configuration Details

### XAMPP Default Setup:
The system works out-of-the-box with XAMPP using the `mail()` function.

### Email Headers:
```php
From: Property Finder Nepal <noreply@propertyfindernepal.com>
Content-Type: text/html; charset=UTF-8
```

### Email Templates Include:
- 🏠 Nepal Property Finder branding
- 📞 Contact information
- ⚠️ Security warnings
- 🎨 Professional HTML styling
- 📱 Mobile-friendly design

## 🧪 Testing the System

### Test Cases:
1. **Registration Test:**
   - Register with real email
   - Check email for OTP
   - Verify with correct/incorrect codes
   - Test OTP expiration (wait 10+ minutes)

2. **Login Test:**
   - Try login with unverified account
   - Verify email then login
   - Try login with verified account

3. **Password Reset Test:**
   - Request reset with valid/invalid email
   - Enter correct/incorrect OTP
   - Set new password and test login

### Test Email Addresses:
- Use real email addresses you can access
- Check spam/junk folders
- Test with different email providers (Gmail, Yahoo, etc.)

## 🚀 Production Deployment

### Before Going Live:
1. ✅ Update email configuration for production SMTP
2. ✅ Set proper `from_email` address in `EmailOTP.php`
3. ✅ Configure proper domain/hosting email settings
4. ✅ Test all email functionality thoroughly
5. ✅ Set up email monitoring/logging

### Email Providers for Production:
- SendGrid
- Mailgun
- Amazon SES
- SMTP2GO

## 📋 File Structure

```
Dynamic-Site/
├── classes/
│   ├── EmailOTP.php          # OTP generation and email sending
│   └── User.php              # Updated with email verification
├── verify_email.php          # Email verification page
├── forgot_password_otp.php   # OTP-based password reset
├── signin.php               # Updated with verification check
├── signup.php               # Updated registration flow
└── 1-Database/
    └── otp_verification.sql  # Database schema updates
```

## 🎯 Benefits of This System

### For College Project:
- ✅ Demonstrates advanced security concepts
- ✅ Real-world authentication system
- ✅ Professional-grade email verification
- ✅ Modern user experience
- ✅ Production-ready code quality

### For GitHub Portfolio:
- ✅ Shows knowledge of email systems
- ✅ Security best practices
- ✅ Database design skills
- ✅ User experience design
- ✅ Complete feature implementation

## ⚠️ Important Notes

1. **Email Delivery**: Test thoroughly with different email providers
2. **Spam Filters**: Use proper email headers and content to avoid spam
3. **Rate Limiting**: Consider adding rate limiting for OTP requests
4. **Logging**: Implement proper logging for debugging email issues
5. **Backup**: Always backup database before running SQL updates

## 🎉 Success!

Your house rental system now has:
- ✅ Real email verification for all users
- ✅ Secure OTP-based authentication
- ✅ Professional email templates
- ✅ Complete password reset system
- ✅ Production-ready security features

Users cannot access the system without verifying their real email addresses! 🔐📧
