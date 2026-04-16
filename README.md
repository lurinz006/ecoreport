# Barangay EcoReport System - Updated Structure

A web-based environmental incident reporting system with organized folder structure for better security and maintainability.

📁 New Folder Structure

```
Barangay EcoReport/
├── admin/                      # Admin/Officials folder
│   ├── dashboard.php          # Official dashboard
│   └── index.php              # Admin entry point
├── user/                       # User/Residents folder
│   ├── dashboard.php          # Resident dashboard
│   └── index.php              # User entry point
├── css/
│   └── style.css              # Main stylesheet
├── js/
│   └── script.js              # JavaScript functions
├── includes/
│   ├── config.php             # Application configuration
│   ├── database.php           # Database classes and functions
│   ├── functions.php          # Utility functions
│   ├── header.php             # HTML header template
│   └── footer.php             # HTML footer template
├── uploads/                   # File upload directory
├── index.php                  # Main entry point (redirects based on role)
├── login.php                  # Login page
├── register.php               # Registration page
├── logout.php                 # Logout handler
├── dashboard.php              # Role-based dashboard redirect
├── submit_report.php          # Report submission handler
├── get_report.php             # Report details API
├── update_report_status.php   # Status update handler
├── search.php                 # Search results page
├── search_ajax.php            # AJAX search handler
├── get_notifications.php      # Notifications API
├── mark_notifications_read.php # Mark notifications as read
├── check_notifications.php    # Notification checker
├── database.sql               # Database schema
└── README_UPDATED.md          # This file
```
 🚀 Installation with XAMPP

 Step 1: Setup XAMPP
1. Install XAMPP and start Apache & MySQL services
2. Navigate to `C:\xampp\htdocs`

Step 2: Copy Project Files
1. Create folder: `C:\xampp\htdocs\ecoreport`
2. Copy all files from the project to this folder

 Step 3: Database Setup
1. Go to `http://localhost/phpmyadmin`
2. Create database: `barangay_ecoreport`
3. Import `database.sql`

 Step 4: Access the Application

Main Entry Point: `http://localhost/ecoreport/`
- Automatically redirects based on user role

irect Access:
- Admin Dashboard: `http://localhost/ecoreport/admin/`
- User Dashboard: `http://localhost/ecoreport/user/`
- Login: `http://localhost/ecoreport/login.php`
- Register: `http://localhost/ecoreport/register.php`

 🔐 Security Benefits of New Structure

Separation of Concerns
- Admin folder: Contains only official/admin functionality
- User folder: Contains only resident functionality
- Root level: Shared resources and public pages

Access Control
- Each folder has its own index.php for role verification
- Prevents direct access to unauthorized areas
- Better organization for permissions management
  
Maintainability
- Easier to maintain role-specific features
- Clear separation of admin vs user code
- Simplified debugging and updates

 📱 URL Structure

Public Pages (Root level)
- `/` - Homepage (redirects to login or dashboard)
- `/login.php` - Login page
- `/register.php` - Registration page
- `/logout.php` - Logout handler

User/Resident Area
- `/user/` - Resident dashboard
- `/user/dashboard.php` - Main resident interface

Admin/Official Area
- `/admin/` - Official dashboard
- `/admin/dashboard.php` - Main admin interface

API Endpoints (Root level)
- `/submit_report.php` - Submit new reports
- `/get_report.php` - Get report details
- `/update_report_status.php` - Update report status
- `/search.php` - Search functionality
- `/get_notifications.php` - Get notifications

## 🎯 Role-Based Access

Residents (Users)
- Can only access `/user/` folder and public pages
- Submit and track their own reports
- Receive notifications about their reports

### **Officials (Admins)**
- Can access `/admin/` folder and public pages
- Manage all submitted reports
- Update report status and add remarks
- View analytics and statistics

Security Features
- Automatic role-based redirects
- Session validation in each folder
- Protected direct access to dashboards
- Cross-folder access prevention

 🔄 How Navigation Works

1. Login: After successful login, users are redirected to their appropriate dashboard
2. Role Check: Each dashboard verifies user role
3. Navigation: Header navigation links to correct folder paths
4. Session Security: All pages validate login status and role

 Development Benefits

### **Easier Maintenance**
- Admin features isolated in `/admin/`
- User features isolated in `/user/`
- Shared resources in `/includes/`

Better Security
- Clear access boundaries
- Role-specific file organization
- Simplified permission management

Scalability
- Easy to add new admin features
- Easy to add new user features
- Clear structure for future enhancements

 📋 Default Accounts (Same as before)

| Username | Password | Role | Access |
|----------|----------|------|--------|
| admin | password | official | `/admin/` |
| resident1 | password | resident | `/user/` |
| official1 | password | official | `/admin/` |

🔄 Migration from Old Structure

If you're migrating from the previous structure:

1. No Database Changes: Database schema remains the same
2. Updated URLs: Update any hardcoded URLs to use new folder structure
3. Bookmarks: Users will need to update their bookmarks
4. Functionality: All features work exactly the same

 🎨 Frontend Files Location
 HTML Templates
- User Interface: `/user/dashboard.php`
- Admin Interface: `/admin/dashboard.php`
- Public Pages: Root level PHP files

CSS & JavaScript
- **Stylesheet**: `/css/style.css` (shared)
- **Scripts**: `/js/script.js` (shared)
- **Templates**: `/includes/header.php` and `/includes/footer.php`

Assets
- Uploads: `/uploads/` (shared file storage)
- Images: Referenced via relative paths from each folder

This new structure provides better organization, security, and maintainability while preserving all existing functionality.
