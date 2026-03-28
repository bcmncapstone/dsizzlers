<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\BranchManagementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Franchisee\ReportController as FranchiseeReportController;
use App\Models\Franchisee;
use App\Models\Item;
use App\Models\Order;

// ADMIN ROUTES
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

// ACCOUNT CREATION (Admin only)
Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/admin/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('/admin/accounts/store', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('/admin/accounts/{type}/{id}', [AccountController::class, 'show'])->name('accounts.show');

    // Archive/Restore Franchisor Staff
    Route::post('/admin/franchisor-staff/{staffId}/archive', [AccountController::class, 'archiveFranchisorStaff'])->name('admin.franchisor-staff.archive');
    Route::post('/admin/franchisor-staff/{staffId}/restore', [AccountController::class, 'restoreFranchisorStaff'])->name('admin.franchisor-staff.restore');
});

//Account Creation for Franchisee Staff (Franchisee)
Route::middleware(['auth:franchisee'])->group(function () {
    Route::get('/franchisee/staff', [AccountController::class, 'indexFranchiseeStaff'])->name('franchisee.staff.index');
    Route::get('/franchisee/account/create', [AccountController::class, 'createFranchiseeStaff'])->name('account.create');
    Route::post('/franchisee/account/store', [AccountController::class, 'storeFranchiseeStaff'])->name('account.store');
    Route::post('/franchisee/staff/{staffId}/archive', [AccountController::class, 'archiveFranchiseeStaff'])
        ->name('franchisee.staff.archive');
    Route::post('/franchisee/staff/{staffId}/restore', [AccountController::class, 'restoreFranchiseeStaff'])
        ->name('franchisee.staff.restore');
});

// LOGIN ROUTES FOR EACH ROLE

// Unified Login from Welcome Page
Route::post('/login/unified', [LoginController::class, 'unifiedLogin'])->name('login.unified');

// Franchisee Login
Route::get('/login/franchisee', [LoginController::class, 'showFranchiseeLogin'])->name('login.franchisee');
Route::post('/login/franchisee', [LoginController::class, 'loginFranchisee']);

// Franchisor Staff Login
Route::get('/login/franchisor-staff', [LoginController::class, 'showFranchisorStaffLogin'])->name('login.franchisorStaff');
Route::post('/login/franchisor-staff', [LoginController::class, 'loginFranchisorStaff']);

// Franchisee Staff Login
Route::get('/login/franchisee-staff', [LoginController::class, 'showFranchiseeStaffLogin'])->name('login.franchiseeStaff');
Route::post('/login/franchisee-staff', [LoginController::class, 'loginFranchiseeStaff']);

// Password reset routes (all login roles)
Route::get('/password/forgot/{role}', [PasswordResetController::class, 'showForgotForm'])
    ->whereIn('role', ['admin', 'franchisee', 'franchisor-staff', 'franchisee-staff'])
    ->name('password.request');
Route::post('/password/forgot/{role}', [PasswordResetController::class, 'sendResetLink'])
    ->whereIn('role', ['admin', 'franchisee', 'franchisor-staff', 'franchisee-staff'])
    ->name('password.email');
Route::get('/password/reset/{role}/{token}', [PasswordResetController::class, 'showResetForm'])
    ->whereIn('role', ['admin', 'franchisee', 'franchisor-staff', 'franchisee-staff'])
    ->name('password.reset.form');
Route::post('/password/reset/{role}', [PasswordResetController::class, 'reset'])
    ->whereIn('role', ['admin', 'franchisee', 'franchisor-staff', 'franchisee-staff'])
    ->name('password.update');

// Franchisee Dashboard
Route::middleware(['auth:franchisee'])->get('/franchisee/dashboard', function () {
    $franchisee = Auth::guard('franchisee')->user();
    $digitalMarketing = \App\Models\DigitalMarketingUpload::query()->notArchived()->latest()->get();
    
    // Stock Statistics - using FranchiseeStock
    $lowStockCount = \App\Models\FranchiseeStock::where('franchisee_id', $franchisee->franchisee_id)
        ->where('current_quantity', '>', 0)
        ->where('current_quantity', '<=', 10)
        ->count();
    
    $outOfStockCount = \App\Models\FranchiseeStock::where('franchisee_id', $franchisee->franchisee_id)
        ->where('current_quantity', '<=', 0)
        ->count();
    
    // Orders Statistics
    $totalOrders = Order::where('franchisee_id', $franchisee->franchisee_id)->count();
    $pendingOrders = Order::where('franchisee_id', $franchisee->franchisee_id)
        ->whereRaw('LOWER(order_status) = ?', ['pending'])
        ->count();
    $preparingOrders = Order::where('franchisee_id', $franchisee->franchisee_id)
        ->whereRaw('LOWER(order_status) = ?', ['preparing'])
        ->count();
    $shippedOrders = Order::where('franchisee_id', $franchisee->franchisee_id)
        ->whereRaw('LOWER(order_status) = ?', ['shipped'])
        ->count();
    $deliveredOrders = Order::where('franchisee_id', $franchisee->franchisee_id)
        ->whereRaw('LOWER(order_status) = ?', ['delivered'])
        ->count();
    
    // Sales Data - based on manual stock decreases only.
    $manualSalesFilter = function ($q) {
        $q->where(function ($manualAdjustments) {
            $manualAdjustments->where('stock_transactions.transaction_type', 'adjustment')
                ->where('stock_transactions.quantity', '<', 0);
        })->orWhere(function ($staffOutflow) {
            $staffOutflow->where('stock_transactions.transaction_type', 'out')
                ->where('stock_transactions.performed_by_type', 'franchisee_staff');
        });
    };

    $totalSales = (float) DB::table('stock_transactions')
        ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
        ->where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
        ->where($manualSalesFilter)
        ->selectRaw("COALESCE(SUM((CASE
            WHEN stock_transactions.performed_by_type = 'franchisee_staff' THEN ABS(stock_transactions.quantity)
            WHEN stock_transactions.quantity < 0 THEN ABS(stock_transactions.quantity)
            ELSE 0
        END) * items.price), 0) as total_sales")
        ->value('total_sales');
    
    $salesThisMonth = (float) DB::table('stock_transactions')
        ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
        ->where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
        ->where($manualSalesFilter)
        ->whereMonth('stock_transactions.created_at', now()->month)
        ->whereYear('stock_transactions.created_at', now()->year)
        ->selectRaw("COALESCE(SUM((CASE
            WHEN stock_transactions.performed_by_type = 'franchisee_staff' THEN ABS(stock_transactions.quantity)
            WHEN stock_transactions.quantity < 0 THEN ABS(stock_transactions.quantity)
            ELSE 0
        END) * items.price), 0) as total_sales")
        ->value('total_sales');
    
    // Staff Count
    $staffCount = \App\Models\FranchiseeStaff::where('franchisee_id', $franchisee->franchisee_id)->count();
    
    // Top Selling Items - based on manual stock decreases.
    $topItems = DB::table('stock_transactions')
        ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
        ->where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
        ->where($manualSalesFilter)
        ->select('items.item_name')
        ->selectRaw("SUM(CASE
            WHEN stock_transactions.performed_by_type = 'franchisee_staff' THEN ABS(stock_transactions.quantity)
            WHEN stock_transactions.quantity < 0 THEN ABS(stock_transactions.quantity)
            ELSE 0
        END) as total_quantity")
        ->groupBy('items.item_id', 'items.item_name')
        ->orderByDesc('total_quantity')
        ->limit(5)
        ->get();
    
    return view('franchisee.dashboard', compact(
        'digitalMarketing',
        'lowStockCount',
        'outOfStockCount',
        'totalOrders',
        'pendingOrders',
        'preparingOrders',
        'shippedOrders',
        'deliveredOrders',
        'totalSales',
        'salesThisMonth',
        'staffCount',
        'topItems'
    ));
})->name('franchisee.dashboard');

// Franchisee Staff Dashboard
Route::middleware(['auth:franchisee_staff', 'franchisee_staff.active'])->get('/franchisee-staff/dashboard', function () {
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
    // Username
    Route::get('franchisor-staff/username', [AccountSettingsController::class, 'editFranchisorStaffUsername'])
        ->name('franchisor-staff.username');
    Route::post('franchisor-staff/username', [AccountSettingsController::class, 'updateFranchisorStaffUsername'])
        ->name('franchisor-staff.username.update');
});

// Franchisor Staff Stock Routes
Route::middleware(['auth:franchisor_staff'])->prefix('franchisor-staff/stock')->name('franchisor-staff.stock.')->group(function () {
    Route::get('/', [\App\Http\Controllers\FranchisorStaff\StockController::class, 'index'])->name('index');
    Route::get('/{stockId}/edit', [\App\Http\Controllers\FranchisorStaff\StockController::class, 'edit'])->name('edit');
    Route::post('/{stockId}', [\App\Http\Controllers\FranchisorStaff\StockController::class, 'update'])->name('update');
    Route::post('/{stockId}/cancel', [\App\Http\Controllers\FranchisorStaff\StockController::class, 'cancel'])->name('cancel');
});

// Franchisee Staff Password Routes
Route::middleware(['auth:franchisee_staff', 'franchisee_staff.active'])->group(function () {
    Route::get('franchisee-staff/password', [AccountSettingsController::class, 'editFranchiseeStaffPassword'])
        ->name('franchisee-staff.password');
    Route::post('franchisee-staff/password', [AccountSettingsController::class, 'updateFranchiseeStaffPassword'])
        ->name('franchisee-staff.password.update');
    // Username
    Route::get('franchisee-staff/username', [AccountSettingsController::class, 'editFranchiseeStaffUsername'])
        ->name('franchisee-staff.username');
    Route::post('franchisee-staff/username', [AccountSettingsController::class, 'updateFranchiseeStaffUsername'])
        ->name('franchisee-staff.username.update');
});

// Franchisee Staff Stock Routes
Route::middleware(['auth:franchisee_staff', 'franchisee_staff.active'])->prefix('franchisee-staff/stock')->name('franchisee-staff.stock.')->group(function () {
    Route::get('/', [\App\Http\Controllers\FranchiseeStaff\StockController::class, 'index'])->name('index');
    Route::get('/{stockId}/edit', [\App\Http\Controllers\FranchiseeStaff\StockController::class, 'edit'])->name('edit');
    Route::post('/{stockId}', [\App\Http\Controllers\FranchiseeStaff\StockController::class, 'update'])->name('update');
    Route::post('/{stockId}/cancel', [\App\Http\Controllers\FranchiseeStaff\StockController::class, 'cancel'])->name('cancel');
});

// Franchisee Password Routes
Route::middleware('auth:franchisee')->group(function () {
    Route::get('franchisee/password', [AccountSettingsController::class, 'editFranchiseePassword'])
        ->name('franchisee.password');
    Route::post('franchisee/password', [AccountSettingsController::class, 'updateFranchiseePassword'])
        ->name('franchisee.password.update');
    // Username
    Route::get('franchisee/username', [AccountSettingsController::class, 'editFranchiseeUsername'])
        ->name('franchisee.username');
    Route::post('franchisee/username', [AccountSettingsController::class, 'updateFranchiseeUsername'])
        ->name('franchisee.username.update');
});

// Admin Password Routes
Route::middleware('auth:admin')->group(function () {
    Route::get('admin/password', [AccountSettingsController::class, 'editAdminPassword'])
        ->name('admin.password');
    Route::post('admin/password', [AccountSettingsController::class, 'updateAdminPassword'])
        ->name('admin.password.update');
    // Username
    Route::get('admin/username', [AccountSettingsController::class, 'editAdminUsername'])
        ->name('admin.username');
    Route::post('admin/username', [AccountSettingsController::class, 'updateAdminUsername'])
        ->name('admin.username.update');
});

//Franchisee to View the User Account 
Route::middleware(['auth:franchisee'])->prefix('franchisee')->name('franchisee.')->group(function () {
    Route::get('/account', [FranchiseeController::class, 'account'])->name('account.index');
    Route::get('/account/contract/{id}', [FranchiseeController::class, 'downloadContract'])->name('branches.contract');
});

// Branch Management for Franchisee
Route::middleware(['auth:franchisee'])->prefix('franchisee/branch')->name('franchisee.branch.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Franchisee\BranchManagementController::class, 'dashboard'])->name('dashboard');
    Route::get('/performance', [\App\Http\Controllers\Franchisee\BranchManagementController::class, 'performance'])->name('performance');
    Route::get('/inventory', [\App\Http\Controllers\Franchisee\BranchManagementController::class, 'inventory'])->name('inventory');
    // Redirect GET requests for adjust to inventory page to avoid method not allowed error
    Route::get('/inventory/adjust/{itemId}', function() {
        return redirect()->route('franchisee.branch.inventory');
    });
    Route::post('/inventory/adjust/{itemId}', [\App\Http\Controllers\Franchisee\BranchManagementController::class, 'adjustInventory'])->name('inventory.adjust');
    Route::get('/financial', [\App\Http\Controllers\Franchisee\BranchManagementController::class, 'financial'])->name('financial');
});

// Stock Management for Franchisee
Route::middleware(['auth:franchisee'])->prefix('franchisee/stock')->name('franchisee.stock.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Franchisee\StockController::class, 'index'])->name('index');
    Route::post('/{stockId}', [\App\Http\Controllers\Franchisee\StockController::class, 'update'])->name('update');
    Route::get('/history', [\App\Http\Controllers\Franchisee\StockController::class, 'history'])->name('history');
    Route::get('/staff-orders', [\App\Http\Controllers\Franchisee\StockController::class, 'staffOrders'])->name('staff-orders');
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
    Route::post('/items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');

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
        Route::post('/items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');
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
    ->middleware(['auth:franchisee_staff', 'franchisee_staff.active'])
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
        // Reports
        Route::get('/reports', [FranchiseeReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [FranchiseeReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/sales/pdf', [FranchiseeReportController::class, 'salesPdf'])->name('reports.sales.pdf');
        Route::get('/reports/inventory', [FranchiseeReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/inventory/pdf', [FranchiseeReportController::class, 'inventoryPdf'])->name('reports.inventory.pdf');
        Route::get('/reports/staff', [FranchiseeReportController::class, 'staff'])->name('reports.staff');
        Route::get('/reports/staff/pdf', [FranchiseeReportController::class, 'staffPdf'])->name('reports.staff.pdf');

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
    ->middleware(['auth:franchisee_staff', 'franchisee_staff.active'])
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
        // Reports
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [AdminReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/sales/pdf', [AdminReportController::class, 'salesPdf'])->name('reports.sales.pdf');
        Route::get('/reports/inventory', [AdminReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/inventory/pdf', [AdminReportController::class, 'inventoryPdf'])->name('reports.inventory.pdf');
        Route::get('/reports/franchisee-sales', [AdminReportController::class, 'franchiseeSales'])->name('reports.franchisee-sales');
        Route::get('/reports/franchisee-sales/pdf', [AdminReportController::class, 'franchiseeSalesPdf'])->name('reports.franchisee-sales.pdf');

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
        
        // Stock Management
        Route::get('/stock', [\App\Http\Controllers\Admin\StockController::class, 'index'])
            ->name('stock.index');
        Route::post('/stock/{itemId}/adjust', [\App\Http\Controllers\Admin\StockController::class, 'adjustQuantity'])
            ->whereNumber('itemId')
            ->name('stock.adjust');
        Route::get('/stock/franchisee-inventory', [\App\Http\Controllers\Admin\StockController::class, 'franchiseeInventory'])
            ->name('stock.franchisee-inventory');
        Route::get('/stock/{franchiseeId}', [\App\Http\Controllers\Admin\StockController::class, 'show'])
            ->name('stock.show');
        Route::get('/stock/reports/all', [\App\Http\Controllers\Admin\StockController::class, 'reports'])
            ->name('stock.reports');
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

Route::middleware(['auth:franchisee_staff', 'franchisee_staff.active'])->group(function () {
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
    Route::post('/digital-marketing/{digital_marketing}/restore', [DigitalMarketingController::class, 'restore'])
        ->name('digital-marketing.restore');
        
    Route::get('/manage-communication', [CommunicationController::class, 'index'])
        ->name('communication.index');
    Route::post('/communication/start', [CommunicationController::class, 'start'])
        ->name('communication.start');
    Route::post('/communication/{conversation}/archive', [CommunicationController::class, 'archiveConversation'])
        ->name('communication.archive');
    Route::post('/communication/{conversation}/restore', [CommunicationController::class, 'restoreConversation'])
        ->name('communication.restore');
});

// Fetch messages without middleware for polling to work
Route::get('/communication/{conversation}/messages', [ChatController::class, 'fetchMessages']);



