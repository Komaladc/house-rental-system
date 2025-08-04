# 🏠 House Rental System - Setup Guide

## Step 1: Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** service
3. Start **MySQL** service
4. Both should show green "Running" status

## Step 2: Setup Database
1. Open browser and go to: http://localhost/phpmyadmin
2. Create a new database named: `db_rental`
3. Import the database file: `Dynamic-Site/1-Database/db_rental.sql`

## Step 3: Configure Database Connection
Check `Dynamic-Site/config/config.php` file for database settings:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_rental');
```

## Step 4: Access the Application

### Main Application URLs:
- **Homepage**: http://localhost/house-rental-system/Dynamic-Site/
- **User Registration**: http://localhost/house-rental-system/Dynamic-Site/signup.php
- **User Login**: http://localhost/house-rental-system/Dynamic-Site/signin.php
- **Property Listings**: http://localhost/house-rental-system/Dynamic-Site/property_list.php

### Admin Panel URLs:
- **Admin Dashboard**: http://localhost/house-rental-system/Dynamic-Site/Admin/
- **Add Property**: http://localhost/house-rental-system/Dynamic-Site/Admin/add_property.php
- **Manage Properties**: http://localhost/house-rental-system/Dynamic-Site/Admin/property_list_admin.php

## Step 5: Default Admin Access
Check the database for admin credentials or create an admin user with:
- User Level: 1 (Admin) or 2 (Property Owner)
- Status: 1 (Active)

## Troubleshooting
- Ensure XAMPP services are running
- Check database connection in config.php
- Verify file permissions if needed
- Check error logs in XAMPP for any issues

## Project Structure
- `Dynamic-Site/` - Main application
- `Static-Template/` - Static HTML templates
- `Admin/` - Admin panel files
- `classes/` - PHP classes (User, Property, etc.)
- `config/` - Database configuration
- `uploads/` - File uploads directory
