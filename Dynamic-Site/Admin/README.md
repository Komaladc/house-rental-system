# Admin Panel Documentation - Property Nepal

## 🏠 Overview

The Property Nepal Admin Panel is a comprehensive management system that allows administrators to manage all aspects of the house rental platform. The admin panel provides user verification, property management, analytics, and system settings functionality.

## 🚀 Getting Started

### Initial Setup

1. **Database Setup**: Run the database setup script
   ```
   Navigate to: http://localhost/house-rental-system/Dynamic-Site/setup_admin_database.php
   ```

2. **Create Admin User**: Create the first admin account
   ```
   Navigate to: http://localhost/house-rental-system/Dynamic-Site/create_admin.php
   ```

3. **Admin Login**: Access the admin panel
   ```
   URL: http://localhost/house-rental-system/Dynamic-Site/admin/login.php
   Default Credentials:
   - Email: admin@propertynepal.com
   - Password: admin123
   ```

## 📋 Features

### 1. Dashboard (`dashboard.php`)
- **Overview Statistics**: Total users, properties, categories, pending verifications
- **Quick Actions**: Direct access to key admin functions
- **Recent Activity**: Latest user registrations and property listings
- **Beautiful UI**: Modern, responsive design with gradient backgrounds

### 2. User Verification (`verify_users.php`)
- **Pending Users**: View all users awaiting verification
- **Document Review**: Preview uploaded citizenship documents
- **Approval/Rejection**: Approve or reject users with reasons
- **Email Notifications**: Automatic status updates to users
- **Admin Logging**: Track all verification decisions

### 3. User Management (`manage_users.php`)
- **User Search & Filtering**: Search by name, email, level, status
- **User Statistics**: Overview of total, active, and pending users
- **User Actions**: Activate, deactivate, or delete user accounts
- **Level Management**: Handle Property Seekers, Owners, and Agents
- **Status Tracking**: Monitor verification and account status

### 4. Property Management (`manage_properties.php`)
- **Property Listings**: View all properties with detailed information
- **Search & Filter**: Filter by category, location, status
- **Property Actions**: Approve, reject, or delete properties
- **Owner Information**: View property owner details
- **Category Management**: Organize properties by type

### 5. Analytics & Settings (`analytics.php`)
- **Website Analytics**: User and property registration trends
- **Interactive Charts**: Visual representation of growth metrics
- **Recent Activities**: Real-time activity monitoring
- **System Settings**: Configure site information and contact details
- **Performance Metrics**: Track platform usage and engagement

## 🔐 Security Features

### Authentication
- **Secure Login**: Session-based authentication system
- **Password Protection**: Encrypted password storage
- **Session Management**: Automatic logout after inactivity
- **Access Control**: Admin-only access to sensitive operations

### Data Protection
- **SQL Injection Prevention**: Prepared statements and input sanitization
- **XSS Protection**: HTML entity encoding for user inputs
- **File Upload Security**: Restricted file types and secure storage
- **Admin Activity Logging**: Track all administrative actions

## 📊 Database Structure

### Core Tables
- `tbl_user`: User accounts and basic information
- `tbl_property`: Property listings and details
- `tbl_category`: Property categories
- `tbl_user_verification`: User verification documents and status
- `tbl_admin_sessions`: Admin login sessions
- `tbl_admin_logs`: Administrative action logging
- `tbl_website_stats`: System analytics and settings

### Verification System
- **Document Storage**: Secure file storage for citizenship documents
- **Status Tracking**: Pending, verified, rejected states
- **Rejection Reasons**: Detailed feedback for rejected applications
- **Email Integration**: Automated notification system

## 🛠️ Technical Implementation

### Backend Architecture
- **PHP 7.4+**: Server-side scripting
- **MySQL 5.7+**: Database management
- **Custom MVC**: Organized file structure
- **Object-Oriented**: Clean, maintainable code

### Frontend Design
- **Responsive CSS**: Mobile-first design approach
- **Chart.js**: Interactive data visualization
- **Modern UI**: Clean, professional interface
- **User Experience**: Intuitive navigation and workflows

### File Structure
```
admin/
├── login.php              # Admin authentication
├── dashboard.php          # Main admin dashboard
├── verify_users.php       # User verification system
├── manage_users.php       # User management
├── manage_properties.php  # Property management
├── analytics.php          # Analytics & settings
└── inc/                   # Shared components
```

## 📱 User Interface

### Design Principles
- **Clean & Modern**: Professional appearance
- **Responsive**: Works on all device sizes
- **Intuitive**: Easy-to-understand navigation
- **Accessible**: Clear labels and visual feedback

### Color Scheme
- Primary: Gradient from #667eea to #764ba2
- Success: #28a745 (green)
- Warning: #ffc107 (yellow)
- Danger: #dc3545 (red)
- Background: #f5f6fa (light gray)

### Icons & Emojis
- Dashboard: 📊
- Users: 👥
- Properties: 🏘️
- Verification: ✅
- Analytics: 📈
- Settings: ⚙️

## 🔧 Configuration

### Email Settings
Configure SMTP settings in `config/config.php` for email notifications:
```php
// Email configuration
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_username = 'your-email@gmail.com';
$smtp_password = 'your-app-password';
```

### File Upload Settings
Document upload configurations:
- **Allowed Types**: JPG, JPEG, PNG, PDF
- **Max Size**: 5MB per file
- **Storage**: `uploads/documents/` directory
- **Security**: File type validation and secure naming

### Database Configuration
Ensure proper database connection in `lib/Database.php`:
```php
private $host = "localhost";
private $user = "root";
private $pass = "";
private $db = "db_rental";
```

## 🚨 Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Check MySQL service is running
   - Verify database credentials
   - Ensure database exists

2. **File Upload Issues**
   - Check directory permissions (755)
   - Verify upload directory exists
   - Check PHP upload limits

3. **Email Notification Problems**
   - Verify SMTP settings
   - Check firewall settings
   - Test email configuration

4. **Session Issues**
   - Clear browser cache
   - Check PHP session configuration
   - Verify session directory permissions

### Error Logging
Admin actions are logged in `tbl_admin_logs` table for debugging and audit purposes.

## 📈 Analytics & Reporting

### Available Metrics
- **User Growth**: Registration trends over time
- **Property Listings**: New listings by month
- **Verification Rate**: Approval/rejection statistics
- **Activity Monitoring**: Real-time user and property activities

### Data Visualization
- **Line Charts**: User registration trends
- **Bar Charts**: Property listing statistics
- **Activity Feed**: Real-time system activities
- **Status Cards**: Key performance indicators

## 🔄 Future Enhancements

### Planned Features
1. **Booking Management**: Handle property bookings and reservations
2. **Payment Integration**: Process rental payments and commissions
3. **Advanced Analytics**: Detailed reporting and insights
4. **Multi-language Support**: Nepali and English language options
5. **Mobile App Integration**: API for mobile applications

### Technical Improvements
1. **API Development**: RESTful API for third-party integrations
2. **Caching System**: Improve performance with Redis/Memcached
3. **Search Enhancement**: Elasticsearch integration
4. **Real-time Notifications**: WebSocket implementation
5. **Advanced Security**: Two-factor authentication

## 📞 Support

For technical support or questions about the admin panel:

- **Email**: admin@propertynepal.com
- **Documentation**: This README file
- **Code Comments**: Detailed inline documentation
- **Database Schema**: Available in `/1-Database/` directory

## 📜 License

This admin panel is part of the Property Nepal house rental system. All rights reserved.

---

**Built with ❤️ for Property Nepal**
*Empowering property management in Nepal*
