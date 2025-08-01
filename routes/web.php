<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\BranchController;

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
    Route::get('/branches/{id}/archive', [BranchController::class, 'archive'])->name('branches.archive');
    Route::get('/branches/{id}/download-contract', [BranchController::class, 'downloadContract'])->name('branches.downloadContract');
});
