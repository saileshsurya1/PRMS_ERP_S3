<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerComplaintController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\dashboard\Analytics;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerStore'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'menu.access'])->group(function () {
    // Dashboards
    Route::get('/', [Analytics::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [Analytics::class, 'index'])->name('dashboard.home');
    Route::get('/sales/dashboard', [Analytics::class, 'owner'])->name('sales.dashboard');
    Route::get('/sales/kpis', [Analytics::class, 'kpis'])->name('sales.kpis');
    Route::get('/customer/dashboard', [Analytics::class, 'customer'])->name('customer.dashboard');

    // Profile & Photo Uploads
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Tasks & To-Do List CRUD
    Route::get('/todos', [TodoController::class, 'index'])->name('todos.index');
    Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');
    Route::patch('/todos/{todo}', [TodoController::class, 'update'])->name('todos.update');
    Route::patch('/todos/{todo}/toggle', [TodoController::class, 'toggle'])->name('todos.toggle');
    Route::delete('/todos/{todo}', [TodoController::class, 'destroy'])->name('todos.destroy');

    // Search & Notifications
    Route::get('/search', SearchController::class)->name('search');
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    // Customers CRUD
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::patch('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Sales & RFQs CRUD
    Route::post('/sales/customers', [SalesController::class, 'storeCustomer'])->name('sales.customers.store');
    Route::get('/sales/rfqs', [SalesController::class, 'rfqs'])->name('sales.rfqs');
    Route::post('/sales/rfqs', [SalesController::class, 'storeRfq'])->name('sales.rfqs.store');
    Route::get('/sales/rfqs/{rfq}', [SalesController::class, 'showRfq'])->name('sales.rfqs.show');
    Route::patch('/sales/rfqs/{rfq}', [SalesController::class, 'updateRfq'])->name('sales.rfqs.update');
    Route::delete('/sales/rfqs/{rfq}', [SalesController::class, 'destroyRfq'])->name('sales.rfqs.destroy');

    // Quotations CRUD
    Route::get('/sales/quotations', [SalesController::class, 'quotations'])->name('sales.quotations');
    Route::post('/sales/quotations', [SalesController::class, 'storeQuotation'])->name('sales.quotations.store');
    Route::patch('/sales/quotations/{quotation}', [SalesController::class, 'updateQuotation'])->name('sales.quotations.update');
    Route::delete('/sales/quotations/{quotation}', [SalesController::class, 'destroyQuotation'])->name('sales.quotations.destroy');

    // Daily KPI Log
    Route::get('/sales/daily-log', [SalesController::class, 'dailyLog'])->name('sales.daily-log');
    Route::post('/sales/daily-log', [SalesController::class, 'storeDailyLog'])->name('sales.daily-log.store');

    // Customer Complaints CRUD
    Route::get('/sales/complaints', [CustomerComplaintController::class, 'index'])->name('sales.complaints.index');
    Route::get('/sales/complaints/{complaint}', [CustomerComplaintController::class, 'show'])->name('sales.complaints.show');
    Route::post('/sales/complaints', [CustomerComplaintController::class, 'store'])->name('sales.complaints.store');
    Route::patch('/sales/complaints/{complaint}', [CustomerComplaintController::class, 'update'])->name('sales.complaints.update');
    Route::delete('/sales/complaints/{complaint}', [CustomerComplaintController::class, 'destroy'])->name('sales.complaints.destroy');

    // Admin / System Management
    Route::middleware('users.create')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::patch('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    });

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Department Master CRUD
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::patch('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        // System Configuration & Menu Access
        Route::get('/menus', [AdminController::class, 'menus'])->name('menus');
        Route::post('/menus', [AdminController::class, 'storeMenu'])->name('menus.store');
        Route::get('/menus/{menu}/edit', [AdminController::class, 'editMenu'])->name('menus.edit');
        Route::patch('/menus/{menu}', [AdminController::class, 'updateMenu'])->name('menus.update');
        Route::delete('/menus/{menu}', [AdminController::class, 'destroyMenu'])->name('menus.destroy');
        Route::post('/menus/{menu}/access', [AdminController::class, 'storeAccess'])->name('menus.access.store');
        Route::get('/menu-access', [AdminController::class, 'access'])->name('menu-access');
        Route::post('/menu-access', [AdminController::class, 'updateAccess'])->name('menu-access.update');
    });
});
