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
use App\Http\Controllers\FranchiseeStaffItemController;
use App\Http\Controllers\ManageOrderController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DigitalMarketingController;
use App\Http\Controllers\CommunicationController;

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

//Item for Franchisor
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

// Item Management for Franchisor Staff
Route::prefix('franchisor-staff')
    ->name('franchisor-staff.')
    ->middleware(['auth:franchisor_staff'])
    ->group(function () {
        Route::get('/items', [ItemController::class, 'index'])->name('items.index');
        Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
        Route::post('/items', [ItemController::class, 'store'])->name('items.store');
        Route::get('/items/{id}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('/items/{id}', [ItemController::class, 'update'])->name('items.update');
        Route::post('/items/{id}/archive', [ItemController::class, 'archive'])->name('items.archive');
        Route::get('/items/archived', [ItemController::class, 'archived'])->name('items.archived');
    });


// Manage Items for Franchisee
Route::prefix('franchisee')
    ->name('franchisee.') //
    ->middleware(['auth:franchisee'])
    ->group(function () {
        Route::get('/item', [FranchiseeItemController::class, 'index'])
            ->name('item.index');

        Route::get('/item/{id}', [FranchiseeItemController::class, 'show'])
            ->whereNumber('id')
            ->name('item.show');
    });

Route::prefix('franchisee-staff')
    ->middleware(['auth:franchisee_staff'])
    ->name('franchisee_staff.')
    ->group(function () {
        Route::get('/item', [FranchiseeStaffItemController::class, 'index'])->name('item.index');
        Route::get('/item/{id}', [FranchiseeStaffItemController::class, 'show'])->whereNumber('id')->name('item.show');
 });


// Franchisee Cart & Orders 
Route::prefix('franchisee')
    ->name('franchisee.')
    ->middleware(['auth:franchisee'])
    ->group(function () {
        // Cart
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
        Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

        // Checkout process
        Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
        Route::post('/cart/place-order', [CartController::class, 'placeOrder'])->name('cart.placeOrder');

        // Orders
       Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
         Route::get('/orders/checkout{id}', [OrderController::class, 'show'])->name('orders.checkout');
        Route::post('/orders/place-order{id}', [OrderController::class, 'show'])->name('orders.placeOrder');
    });

// Franchisee Staff Cart & Orders 
Route::prefix('franchisee-staff')
    ->name('franchisee_staff.')
    ->middleware(['auth:franchisee_staff'])
    ->group(function () {
        // Cart
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
        Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

        // Checkout process
        Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
        Route::post('/cart/place-order', [CartController::class, 'placeOrder'])->name('cart.placeOrder');

        // Orders
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/checkout{id}', [OrderController::class, 'show'])->name('orders.checkout');
        Route::post('/orders/place-order{id}', [OrderController::class, 'show'])->name('orders.placeOrder');
    });

// ===============================
//  Franchisor (Admin) Order Management
// ===============================
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:admin'])
    ->group(function () {
        // View all orders
        Route::get('manageOrder', [ManageOrderController::class, 'index'])
            ->name('manageOrder.index');

        // View single order
        Route::get('manageOrder/{id}', [ManageOrderController::class, 'show'])
            ->name('manageOrder.show');

        // Confirm payment
        Route::post('manageOrder/{id}/confirm-payment', [ManageOrderController::class, 'confirmPayment'])
            ->name('manageOrder.confirmPayment');

        // Update order status
        Route::post('manageOrder/{id}/update-order-status', [ManageOrderController::class, 'updateOrderStatus'])
            ->name('manageOrder.updateOrderStatus');

        // Update notes
        Route::post('manageOrder/{id}/update-notes', [ManageOrderController::class, 'updateNotes'])
            ->name('manageOrder.updateNotes');

        // Cancel order
        Route::post('manageOrder/{id}/cancel', [ManageOrderController::class, 'cancelOrder'])
            ->name('manageOrder.cancel');
    });


// ===============================
//  Franchisor Staff Order Management
// ===============================
Route::prefix('franchisor-staff')
    ->name('franchisor-staff.')
    ->middleware(['auth:franchisor_staff'])
    ->group(function () {
        // View all orders
        Route::get('/manageOrder', [ManageOrderController::class, 'index'])
            ->name('manageOrder.index');

        // View single order
        Route::get('/manageOrder/{id}', [ManageOrderController::class, 'show'])
            ->name('manageOrder.show');

        // Confirm payment
        Route::post('/manageOrder/{id}/confirm-payment', [ManageOrderController::class, 'confirmPayment'])
            ->name('manageOrder.confirmPayment');

        // Update order status
        Route::post('/manageOrder/{id}/update-order-status', [ManageOrderController::class, 'updateOrderStatus'])
            ->name('manageOrder.updateOrderStatus');

        // Update notes
        Route::post('/manageOrder/{id}/update-notes', [ManageOrderController::class, 'updateNotes'])
            ->name('manageOrder.updateNotes');

        // Cancel order
        Route::post('/manageOrder/{id}/cancel', [ManageOrderController::class, 'cancelOrder'])
            ->name('manageOrder.cancel');
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


// Generic logout route that logs out whichever guard is active
Route::post('/logout', function () {
    if (auth()->guard('admin')->check()) {
        auth()->guard('admin')->logout();
    } elseif (auth()->guard('franchisor_staff')->check()) {
        auth()->guard('franchisor_staff')->logout();
    } elseif (auth()->guard('franchisee_staff')->check()) {
        auth()->guard('franchisee_staff')->logout();
    } elseif (auth()->guard('franchisee')->check()) {
        auth()->guard('franchisee')->logout();
    }

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');

// FOR CHAT AND DIGITAL MARKETING 
// Allow any authenticated guard (admin, franchisor_staff, franchisee, franchisee_staff)
Route::middleware([\App\Http\Middleware\MultiAuth::class])->group(function () {
    Route::get('/communication/{conversation}', [ChatController::class, 'show']);
    Route::post('/communication/{conversation}/send', [ChatController::class, 'send']);
    Route::get('/communication/{conversation}/messages', [ChatController::class, 'fetchMessages']);

    Route::resource('digital-marketing', DigitalMarketingController::class)
        ->only(['index', 'store', 'update', 'destroy']);
        
    Route::get('/manage-communication', [CommunicationController::class, 'index'])
        ->name('communication.index');
    Route::post('/communication/start', [CommunicationController::class, 'start'])
        ->name('communication.start');
});

// Fetch messages without middleware for polling to work
Route::get('/communication/{conversation}/messages', [ChatController::class, 'fetchMessages']);



