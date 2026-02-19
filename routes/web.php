<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\AssignmentsController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ActivityLogsController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect('/dashboard');
});

/*
|--------------------------------------------------------------------------
| Routes accessible by ALL authenticated users (Employee, Admin, Super Admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Dashboard - accessible to all
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Schedule - accessible to all (employees can view, admins can manage)
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
    
    // Assignments - accessible to all (employees view only, admins can assign)
    Route::get('/assignments', [AssignmentsController::class, 'index'])->name('assignments');
});

/*
|--------------------------------------------------------------------------
| Routes for Super Admin (Boss) ONLY
|--------------------------------------------------------------------------
| - Manage Orders (where to deliver, which items, within the day)
| - Dispatch (appoint drivers)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::get('/orders', [OrdersController::class, 'index'])->name('orders');
    Route::post('/orders', [OrdersController::class, 'store'])->name('orders.store');
    Route::put('/orders/{order}', [OrdersController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{order}', [OrdersController::class, 'destroy'])->name('orders.destroy');
    
    Route::get('/dispatch', [DispatchController::class, 'index'])->name('dispatch');
    Route::post('/dispatch/assign-driver', [DispatchController::class, 'assignDriver'])->name('dispatch.assign');
});

/*
|--------------------------------------------------------------------------
| Routes for Admin Manager and Super Admin
|--------------------------------------------------------------------------
| Admin Manager can manage everything EXCEPT orders and dispatch
| - Inventory (stock in, stock out)
| - Analytics (reports)
| - Employees (manage employees)
| - Settings
| - Schedule (create/edit notes)
| - Assignments (can assign tasks to employees)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin_or_above'])->group(function () {
    // Inventory management
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::put('/inventory/{item}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/inventory/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stock-in');
    Route::post('/inventory/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stock-out');
    
    // Analytics and reports
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/analytics/reports', [AnalyticsController::class, 'reports'])->name('analytics.reports');
    
    // Employee management
    Route::get('/employees', [EmployeesController::class, 'index'])->name('employees');
    Route::post('/employees', [EmployeesController::class, 'store'])->name('employees.store');
    Route::put('/employees/{employee}', [EmployeesController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeesController::class, 'destroy'])->name('employees.destroy');
    
    // Activity Logs - Admin and Super Admin only
    Route::get('/activity-logs', [ActivityLogsController::class, 'index'])->name('activity-logs');
    
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    
    // Schedule management (notes)
    Route::post('/schedule/notes', [ScheduleController::class, 'storeNote'])->name('schedule.notes.store');
    Route::put('/schedule/notes/{note}', [ScheduleController::class, 'updateNote'])->name('schedule.notes.update');
    Route::delete('/schedule/notes/{note}', [ScheduleController::class, 'destroyNote'])->name('schedule.notes.destroy');
    
    // Assignment management (can assign tasks)
    Route::post('/assignments', [AssignmentsController::class, 'store'])->name('assignments.store');
    Route::put('/assignments/{assignment}', [AssignmentsController::class, 'update'])->name('assignments.update');
    Route::delete('/assignments/{assignment}', [AssignmentsController::class, 'destroy'])->name('assignments.destroy');
});

