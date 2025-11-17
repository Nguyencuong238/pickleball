# Admin Login Setup - Pickleball Booking

## Overview
This setup creates a dedicated admin login system with authentication and an admin dashboard.

## What Was Created

### 1. Admin Login Page
- **File**: `resources/views/auth/admin-login.blade.php`
- Beautiful, secure login page specifically for admin users
- Custom styling with gradient background
- Error messages and validation feedback

### 2. Admin Authentication Routes
- **File**: `routes/web.php`
- `GET /admin/login` - Show admin login form
- `POST /admin/login` - Process admin login with role validation

### 3. Admin Controller Methods
- **File**: `app/Http/Controllers/AuthController.php`
- `showAdminLogin()` - Display admin login view
- `adminLogin()` - Handle admin login with role verification

### 4. Admin User Seeder
- **File**: `database/seeders/AdminUserSeeder.php`
- Creates default admin account
- Assigns admin role to user

### 5. Admin Dashboard
- **File**: `resources/views/admin/dashboard.blade.php` (already existed)
- User statistics (total users, admins, regular users, home_yard users)
- User management table
- Role & permission overview

## Default Admin Account

```
Email: admin@pickleball.com
Password: admin123456
```

## How to Use

### Access Admin Login
1. Navigate to: `http://localhost/admin/login`
2. Enter email: `admin@pickleball.com`
3. Enter password: `admin123456`
4. You'll be redirected to `/admin/dashboard`

### Protected Routes
The following routes are protected by `auth` middleware and require the `admin` role:
- `GET /admin/dashboard` - Admin dashboard
- `GET /admin/users` - User management
- `POST /admin/users/{user}` - Update user roles
- `GET /admin/news` - News management
- `GET /admin/pages` - Page management
- `GET /admin/stadiums` - Stadium management
- `GET /admin/tournaments` - Tournament management

## Security Features

1. **Role-Based Access Control**: Only users with `admin` role can access admin panels
2. **Email Verification**: Login validates email exists in system
3. **Password Hashing**: All passwords are securely hashed using bcrypt
4. **Session Management**: Proper session regeneration on login/logout
5. **Authorization Checks**: Admin login validates user role before granting access

## Change Default Password

To change the default admin password:

```bash
php artisan tinker
>>> $user = App\Models\User::where('email', 'admin@pickleball.com')->first();
>>> $user->password = bcrypt('new_password_here');
>>> $user->save();
>>> exit
```

## Create Additional Admin Users

Using Laravel Tinker:
```bash
php artisan tinker
>>> $user = App\Models\User::create(['name' => 'New Admin', 'email' => 'newadmin@example.com', 'password' => bcrypt('password123')]);
>>> $user->assignRole('admin');
>>> exit
```

Or use the admin panel to manage users and assign roles.

## File Structure
```
resources/views/
├── auth/
│   ├── admin-login.blade.php (NEW)
│   ├── login.blade.php
│   └── register.blade.php
├── admin/
│   ├── dashboard.blade.php
│   └── ...other admin views

app/Http/Controllers/
├── AuthController.php (MODIFIED)
└── Admin/
    ├── DashboardController.php
    └── ...other admin controllers

database/seeders/
└── AdminUserSeeder.php (NEW)

routes/
└── web.php (MODIFIED)
```

## Next Steps

1. Consider changing the default admin password in production
2. Set up email verification for password resets
3. Add two-factor authentication (optional)
4. Implement audit logging for admin actions
5. Set up admin activity monitoring
