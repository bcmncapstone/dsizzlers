<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AccountSettingsController;

// Redirect root to admin login
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// ADMIN ROUTES
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

// ACCOUNT CREATION (Admin)
Route::get('/admin/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
Route::post('/admin/accounts/store', [AccountController::class, 'store'])->name('accounts.store');


// LOGIN FOR EACH ROLE

// Franchisee Login
Route::get('/login/franchisee', [LoginController::class, 'showFranchiseeLogin'])->name('login.franchisee');
Route::post('/login/franchisee', [LoginController::class, 'loginFranchisee']);

// Franchisor Staff Login
Route::get('/login/franchisor-staff', [LoginController::class, 'showFranchisorStaffLogin'])->name('login.franchisorStaff');
Route::post('/login/franchisor-staff', [LoginController::class, 'loginFranchisorStaff']);

// Franchisee Staff Login
Route::get('/login/franchisee-staff', [LoginController::class, 'showFranchiseeStaffLogin'])->name('login.franchiseeStaff');
Route::post('/login/franchisee-staff', [LoginController::class, 'loginFranchiseeStaff']);


// DASHBOARD PLACEHOLDERS

// Franchisee Dashboard (after login)
Route::get('/franchisee/dashboard', function () {
    return "Welcome, Franchisee!";
})->name('franchisee.dashboard');

Route::post('/logout/franchisee', function () {
    session()->forget('franchisee_id');
    return redirect()->route('login.franchisee');
})->name('logout.franchisee');

// This is for the landing page
Route::get('/', function () {
    return view('welcome');
});
// Update Password /Settings
Route::middleware([\App\Http\Middleware\Authenticate::class])->group(function () {
    // Settings Page (with option to update password)
    Route::get('/settings', [AccountSettingsController::class, 'index'])->name('settings.index');
    
    // Password Update Page
    Route::get('/settings/password', [AccountSettingsController::class, 'editPassword'])->name('settings.password');
    Route::post('/settings/password', [AccountSettingsController::class, 'updatePassword'])->name('settings.password.update');
});
