# Role-Based Access Control (RBAC) Implementation Guide

## Overview

This application implements a role-based access control system with three distinct roles:
1. **Employee** - Basic user with read-only access to their work
2. **Admin Manager** - Management role with full access except orders and dispatch
3. **Super Admin (Boss)** - Top-level role with full system access

---

## Role Definitions

### 1. Employee Role (`employee`)
**Responsibilities:**
- View dashboard
- View schedule and notes
- View assignments (only those assigned to them)

**Access Level:** Read-only access to basic features

**Cannot:**
- Create or edit anything
- Assign tasks
- Manage inventory
- Access reports
- Manage orders or dispatch

---

### 2. Admin Manager (`admin`)
**Responsibilities:**
- Manage all operations EXCEPT orders and driver dispatch
- Manage employees (create, update, delete)
- Manage inventory (stock in, stock out)
- View analytics and generate reports
- Create and manage schedules with notes
- Assign tasks to employees
- Manage system settings

**Access Level:** Full management access except for order management and dispatch

**Cannot:**
- Manage orders (order management is exclusively for Super Admin)
- Appoint or manage drivers in dispatch

---

### 3. Super Admin / Boss (`super_admin`)
**Responsibilities:**
- Full system access
- Manage orders (decide where to deliver, which items, daily operations)
- Dispatch management (appoint drivers)
- All privileges of Admin Manager

**Access Level:** Complete system access

---

## Route Protection

### Routes Accessible by ALL Authenticated Users
```php
- GET  /dashboard
- GET  /schedule
- GET  /assignments
```

### Routes for Super Admin ONLY
```php
- GET    /orders
- POST   /orders
- PUT    /orders/{order}
- DELETE /orders/{order}
- GET    /dispatch
- POST   /dispatch/assign-driver
```

### Routes for Admin Manager and Super Admin
```php
# Inventory
- GET    /inventory
- POST   /inventory
- PUT    /inventory/{item}
- DELETE /inventory/{item}
- POST   /inventory/stock-in
- POST   /inventory/stock-out

# Analytics
- GET /analytics
- GET /analytics/reports

# Employees
- GET    /employees
- POST   /employees
- PUT    /employees/{employee}
- DELETE /employees/{employee}

# Settings
- GET /settings
- PUT /settings

# Schedule Notes
- POST   /schedule/notes
- PUT    /schedule/notes/{note}
- DELETE /schedule/notes/{note}

# Assignments (Create/Manage)
- POST   /assignments
- PUT    /assignments/{assignment}
- DELETE /assignments/{assignment}
```

---

## Available Middleware

The following middleware aliases are registered:

| Middleware | Description |
|------------|-------------|
| `role:employee,admin,super_admin` | Check if user has any of the specified roles |
| `employee` | Allow only employees |
| `admin` | Allow only admin managers |
| `super_admin` | Allow only super admins |
| `admin_or_above` | Allow admin managers and super admins |

### Usage Examples

```php
// Protect a single route
Route::get('/page', [Controller::class, 'method'])->middleware('admin');

// Protect a route group
Route::middleware(['auth', 'admin_or_above'])->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/inventory', [InventoryController::class, 'store']);
});

// Check multiple roles
Route::get('/page', [Controller::class, 'method'])->middleware('role:admin,super_admin');
```

---

## User Model Helper Methods

The User model includes convenient methods to check roles:

```php
// Check specific role
$user->isEmployee();        // Returns true if role is 'employee'
$user->isAdmin();           // Returns true if role is 'admin'
$user->isSuperAdmin();      // Returns true if role is 'super_admin'

// Check role level
$user->isAdminOrAbove();    // Returns true if admin or super_admin

// Generic role checks
$user->hasRole('admin');                           // Check single role
$user->hasAnyRole(['employee', 'admin']);          // Check multiple roles
```

### Usage in Controllers

```php
class AssignmentsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        if ($user->isEmployee()) {
            // Show only assignments assigned to this employee
            $assignments = Assignment::where('employee_id', $user->id)->get();
        } else {
            // Admin and Super Admin can see all assignments
            $assignments = Assignment::all();
        }
        
        return view('assignments.index', compact('assignments'));
    }
}
```

### Usage in Blade Views

```blade
@if(auth()->user()->isSuperAdmin())
    <a href="{{ route('orders') }}">Manage Orders</a>
@endif

@if(auth()->user()->isAdminOrAbove())
    <a href="{{ route('employees') }}">Manage Employees</a>
    <a href="{{ route('inventory') }}">Manage Inventory</a>
@endif

{{-- Available to all authenticated users --}}
<a href="{{ route('dashboard') }}">Dashboard</a>
<a href="{{ route('schedule') }}">Schedule</a>
<a href="{{ route('assignments') }}">Assignments</a>
```

---

## Default Test Users

The database seeder creates the following test users:

| Username | Password | Role | Access |
|----------|----------|------|--------|
| boss | password | Super Admin | Full access |
| admin | password | Admin Manager | All except orders/dispatch |
| employee | password | Employee | Read-only basic access |
| test | password | Employee | Read-only basic access |

**Note:** Login uses username only (no email required for enterprise system).

---

## Setup Instructions

### 1. Run the Migration
```bash
php artisan migrate
```

This will add the `role` column to the `users` table.

### 2. Seed the Database
```bash
php artisan db:seed
```

This will create the test users with different roles.

### 3. Clear Application Cache (Optional)
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

---

## Role Assignment

### Creating a New User with a Role

```php
use App\Models\User;

// Create Super Admin
User::create([
    'name' => 'John Boss',
    'email' => 'john@example.com',
    'password' => bcrypt('password'),
    'role' => User::ROLE_SUPER_ADMIN,
]);

// Create Admin Manager
User::create([
    'name' => 'Jane Manager',
    'email' => 'jane@example.com',
    'password' => bcrypt('password'),
    'role' => User::ROLE_ADMIN,
]);

// Create Employee
User::create([
    'name' => 'Bob Worker',
    'email' => 'bob@example.com',
    'password' => bcrypt('password'),
    'role' => User::ROLE_EMPLOYEE,
]);
```

### Updating User Role

```php
$user = User::find(1);
$user->role = User::ROLE_ADMIN;
$user->save();
```

---

## Access Control Summary

| Feature | Employee | Admin Manager | Super Admin |
|---------|----------|---------------|-------------|
| Dashboard | ✓ View | ✓ View | ✓ View |
| Schedule | ✓ View | ✓ Manage (Notes) | ✓ Manage (Notes) |
| Assignments | ✓ View Own | ✓ Assign/Manage | ✓ Assign/Manage |
| Inventory | ✗ | ✓ Full Access | ✓ Full Access |
| Analytics | ✗ | ✓ Full Access | ✓ Full Access |
| Employees | ✗ | ✓ Full Access | ✓ Full Access |
| **Orders** | ✗ | ✗ | **✓ Full Access** |
| **Dispatch** | ✗ | ✗ | **✓ Full Access** |
| Settings | ✗ | ✓ Full Access | ✓ Full Access |

---

## Security Notes

1. **Default Role**: New users are automatically assigned the `employee` role by default (defined in the migration).

2. **Authentication**: All routes except login are protected by authentication. Unauthenticated users will be redirected to the login page.

3. **Authorization**: Attempting to access a restricted page will result in a 403 Forbidden error.

4. **Role Constants**: Always use the User model constants (e.g., `User::ROLE_ADMIN`) instead of hardcoded strings to avoid typos.

---

## Troubleshooting

### User Cannot Access Expected Pages

1. Verify the user's role:
```php
$user = User::find($userId);
echo $user->role; // Should be 'employee', 'admin', or 'super_admin'
```

2. Check if middleware is properly applied to routes:
```bash
php artisan route:list
```

3. Clear configuration cache:
```bash
php artisan config:clear
php artisan route:clear
```

### 403 Forbidden Errors

- Ensure the user is logged in
- Verify the user has the correct role for the page they're accessing
- Check that the middleware is registered in `bootstrap/app.php`

---

## Future Enhancements

Potential improvements to consider:

1. **Permissions System**: Add granular permissions beyond roles
2. **Role Hierarchy**: Implement automatic inheritance of permissions
3. **Audit Logging**: Track role changes and access attempts
4. **Dynamic Roles**: Allow administrators to create custom roles
5. **Multi-Role Support**: Allow users to have multiple roles simultaneously

---

## Support

For questions or issues related to the role-based access control system, please refer to the Laravel documentation on [Authorization](https://laravel.com/docs/authorization) or consult the development team.
