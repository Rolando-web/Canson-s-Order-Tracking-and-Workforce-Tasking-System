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

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
Route::get('/orders', [OrdersController::class, 'index'])->name('orders');
Route::get('/dispatch', [DispatchController::class, 'index'])->name('dispatch');
Route::get('/assignments', [AssignmentsController::class, 'index'])->name('assignments');
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
Route::get('/employees', [EmployeesController::class, 'index'])->name('employees');
Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
