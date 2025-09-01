<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\Franchisee\FranchiseeItemController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;


// ADMIN ROUTES
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

// ACCOUNT CREATION (Admin only)
Route::get('/admin/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
Route::post('/admin/accounts/store', [AccountController::class, 'store'])->name('accounts.store');


// LOGIN ROUTES FOR EACH ROLE

// Franchisee Login
Route::get('/login/franchisee', [LoginController::class, 'showFranchiseeLogin'])->name('login.franchisee');
Route::post('/login/franchisee', [LoginController::class, 'loginFranchisee']);

// Franchisor Staff Login
Route::get('/login/franchisor-staff', [LoginController::class, 'showFranchisorStaffLogin'])->name('login.franchisorStaff');
Route::post('/login/franchisor-staff', [LoginController::class, 'loginFranchisorStaff']);

// Franchisee Staff Login
Route::get('/login/franchisee-staff', [LoginController::class, 'showFranchiseeStaffLogin'])->name('login.franchiseeStaff');
Route::post('/login/franchisee-staff', [LoginController::class, 'loginFranchiseeStaff']);

// Dashboards
Route::get('/dashboard/franchisee', fn () => view('franchisee.dashboard'))->name('franchisee.dashboard');
Route::get('/dashboard/franchisee-staff', fn () => view('franchisee-staff.dashboard'))->name('franchisee-staff.dashboard');
Route::get('/dashboard/franchisor-staff', fn () => view('franchisor-staff.dashboard'))->name('franchisor-staff.dashboard');

// SETTINGS / PASSWORD UPDATE — For all logged-in users
Route::middleware([\App\Http\Middleware\Authenticate::class])->group(function () {
    Route::get('/settings', [AccountSettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/password', [AccountSettingsController::class, 'editPassword'])->name('settings.password');
    Route::post('/settings/password', [AccountSettingsController::class, 'updatePassword'])->name('settings.password.update');
});

// LANDING PAGE
Route::get('/', fn () => view('welcome'));

// PASSWORD SETTINGS FOR FRANCHISOR
Route::middleware(['auth'])->group(function () {
    Route::get('/franchisor/settings/password', [PasswordController::class, 'edit'])->name('franchisor.settings.password');
    Route::put('/franchisor/settings/password', [PasswordController::class, 'update'])->name('franchisor.settings.password.update');
});

// Fallback login route required by auth middleware
Route::get('/login', fn () => redirect('/admin/login'))->name('login');

//Branch
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::get('/branches/archived', [BranchController::class, 'archived'])->name('branches.archived');
    Route::get('/branches/create', [BranchController::class, 'create'])->name('branches.create');
    Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::post('/branches/{id}/archive', [BranchController::class, 'archive'])->name('branches.archive');
    Route::get('/branches/{id}/download-contract', [BranchController::class, 'downloadContract'])->name('branches.downloadContract');
});

//Item Franchisor and Franchisor Staff
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{id}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{id}', [ItemController::class, 'update'])->name('items.update');
    Route::post('/items/{id}/archive', [ItemController::class, 'archive'])->name('items.archive');

    // Archived view (manual archive)
    Route::get('/items/archived', [ItemController::class, 'archived'])->name('items.archived');
});

// Manage Items for Franchisee and Franchisee Staff
foreach (['franchisee', 'franchisee_staff'] as $prefix) {
    Route::prefix($prefix)->name($prefix . '.')->group(function () {

        // Item list
        Route::get('/item', [FranchiseeItemController::class, 'index'])
            ->name('item.index');

        // Single item view (with numeric ID constraint)
        Route::get('/item/{id}', [FranchiseeItemController::class, 'show'])
            ->whereNumber('id')
            ->name('item.show');
    });
}

// Franchisee & Franchisee Staff Cart
foreach (['franchisee', 'franchisee_staff'] as $prefix) {
    Route::prefix($prefix)->name($prefix . '.')->group(function () {
        // Cart routes
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
        Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
        Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

        // Orders
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index'); // <-- Add this
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::post('/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');

    });
}
