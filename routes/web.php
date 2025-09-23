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
use App\Http\Controllers\StaffAccountController;
use App\Http\Controllers\Franchisee\FranchiseeController;

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

//Account Creation for Franchisee Staff (Franchisee)
Route::middleware(['auth:franchisee'])->group(function () {
    Route::get('/franchisee/account/create', [AccountController::class, 'createFranchiseeStaff'])->name('account.create');
    Route::post('/franchisee/account/store', [AccountController::class, 'storeFranchiseeStaff'])->name('account.store');
});

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

// Franchisee Dashboard
Route::middleware(['auth:franchisee'])->get('/franchisee/dashboard', function () {
    return view('franchisee.dashboard');
})->name('franchisee.dashboard');

// Franchisee Staff Dashboard
Route::middleware(['auth:franchisee_staff'])->get('/franchisee-staff/dashboard', function () {
    return view('franchisee-staff.dashboard');
})->name('franchisee-staff.dashboard');

// Franchisor Staff Dashboard
Route::middleware(['auth:franchisor_staff'])->get('/franchisor-staff/dashboard', function () {
    return view('franchisor-staff.dashboard');
})->name('franchisor-staff.dashboard');


// LANDING PAGE
Route::get('/', fn () => view('welcome'));

// Franchisor Staff Password Routes
Route::middleware('auth:franchisor_staff')->group(function () {
    Route::get('franchisor-staff/password', [AccountSettingsController::class, 'editFranchisorStaffPassword'])
        ->name('franchisor-staff.password');
    Route::post('franchisor-staff/password', [AccountSettingsController::class, 'updateFranchisorStaffPassword'])
        ->name('franchisor-staff.password.update');
});

// Franchisee Staff Password Routes
Route::middleware('auth:franchisee_staff')->group(function () {
    Route::get('franchisee-staff/password', [AccountSettingsController::class, 'editFranchiseeStaffPassword'])
        ->name('franchisee-staff.password');
    Route::post('franchisee-staff/password', [AccountSettingsController::class, 'updateFranchiseeStaffPassword'])
        ->name('franchisee-staff.password.update');
});

// Franchisee Password Routes
Route::middleware('auth:franchisee')->group(function () {
    Route::get('franchisee/password', [AccountSettingsController::class, 'editFranchiseePassword'])
        ->name('franchisee.password');
    Route::post('franchisee/password', [AccountSettingsController::class, 'updateFranchiseePassword'])
        ->name('franchisee.password.update');
});

//Franchisee to View the User Account 
Route::middleware(['auth:franchisee'])->prefix('franchisee')->name('franchisee.')->group(function () {
    Route::get('/account', [FranchiseeController::class, 'account'])->name('account.index');
    Route::get('/account/contract/{id}', [FranchiseeController::class, 'downloadContract'])->name('branches.contract');
});



// Fallback login route required by auth middleware
Route::get('/login', fn () => redirect('/admin/login'))->name('login');

//Branch
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::get('/branches/archived', [BranchController::class, 'archived'])->name('branches.archived');
    Route::get('/branches/create', [BranchController::class, 'create'])->name('branches.create');
    Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::get('/branches/{id}/edit', [BranchController::class, 'edit'])->name('branches.edit');
    Route::put('/branches/{id}', [BranchController::class, 'update'])->name('branches.update'); 
    Route::post('/branches/{id}/restore', [BranchController::class, 'restore'])->name('branches.restore');
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

    //Update Profile For Franchisee Staff and Franchisor Staff
Route::middleware(['auth:franchisor_staff'])->group(function () {
    Route::get('/franchisor-staff/account', [StaffAccountController::class, 'showFranchisorStaff'])
        ->name('franchisor-staff.account.show');
    Route::put('/franchisor-staff/account', [StaffAccountController::class, 'updateFranchisorStaff'])
        ->name('franchisor-staff.account.update');
});

Route::middleware(['auth:franchisee_staff'])->group(function () {
    Route::get('/franchisee-staff/account', [StaffAccountController::class, 'showFranchiseeStaff'])
        ->name('franchisee-staff.account.show');
    Route::put('/franchisee-staff/account', [StaffAccountController::class, 'updateFranchiseeStaff'])
        ->name('franchisee-staff.account.update');
});

}
