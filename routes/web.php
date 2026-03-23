<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
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
use App\Http\Controllers\BranchManagementController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Franchisee\ReportController as FranchiseeReportController;
use App\Models\Item;

// ADMIN ROUTES
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::get('/admin/dashboard', function () {
    $today = now();
    $startOfWeek = now()->copy()->startOfWeek();
    $startOfMonth = now()->copy()->startOfMonth();
    $startOf30Days = now()->copy()->subDays(30);
    $startOf7Days = now()->copy()->subDays(6)->startOfDay();

    $totalItemsSold = (int) DB::table('order_details')->sum('quantity');
    $lowStockCount = Item::where('stock_quantity', '>', 0)
        ->where('stock_quantity', '<=', 10)
        ->count();
    $outOfStockCount = Item::where('stock_quantity', '<=', 0)->count();

    $todayRevenue = (float) DB::table('orders')
        ->where('order_status', 'Delivered')
        ->whereDate('order_date', $today->toDateString())
        ->sum('total_amount');

    $weekRevenue = (float) DB::table('orders')
        ->where('order_status', 'Delivered')
        ->whereDate('order_date', '>=', $startOfWeek->toDateString())
        ->sum('total_amount');

    $monthRevenue = (float) DB::table('orders')
        ->where('order_status', 'Delivered')
        ->whereDate('order_date', '>=', $startOfMonth->toDateString())
        ->sum('total_amount');

    $statusOrder = ['Pending', 'Preparing', 'Shipped', 'Delivered', 'Cancelled'];
    $ordersByStatusRaw = DB::table('orders')
        ->select('order_status', DB::raw('COUNT(*) as total'))
        ->groupBy('order_status')
        ->pluck('total', 'order_status')
        ->toArray();

    $ordersByStatus = [];
    foreach ($statusOrder as $status) {
        $ordersByStatus[$status] = (int) ($ordersByStatusRaw[$status] ?? 0);
    }

    $deliveredOrdersCount = (int) DB::table('orders')
        ->where('order_status', 'Delivered')
        ->count();

    $deliveredRevenueTotal = (float) DB::table('orders')
        ->where('order_status', 'Delivered')
        ->sum('total_amount');

    $averageOrderValue = $deliveredOrdersCount > 0
        ? $deliveredRevenueTotal / $deliveredOrdersCount
        : 0;

    $topSellingItems = DB::table('order_details')
        ->join('orders', 'orders.order_id', '=', 'order_details.order_id')
        ->join('items', 'items.item_id', '=', 'order_details.item_id')
        ->where('orders.order_status', 'Delivered')
        ->select(
            'items.item_id',
            'items.item_name',
            DB::raw('SUM(order_details.quantity) as total_quantity'),
            DB::raw('SUM(order_details.subtotal) as total_sales')
        )
        ->groupBy('items.item_id', 'items.item_name')
        ->orderByDesc('total_quantity')
        ->limit(5)
        ->get();

    $itemSales30d = DB::table('order_details')
        ->join('orders', 'orders.order_id', '=', 'order_details.order_id')
        ->where('orders.order_status', 'Delivered')
        ->whereDate('orders.order_date', '>=', $startOf30Days->toDateString())
        ->select(
            'order_details.item_id',
            DB::raw('SUM(order_details.quantity) as sold_30d')
        )
        ->groupBy('order_details.item_id');

    $slowMovingItems = DB::table('items')
        ->leftJoinSub($itemSales30d, 'sales_30d', function ($join) {
            $join->on('sales_30d.item_id', '=', 'items.item_id');
        })
        ->select(
            'items.item_id',
            'items.item_name',
            'items.stock_quantity',
            DB::raw('COALESCE(sales_30d.sold_30d, 0) as sold_30d')
        )
        ->orderBy('sold_30d')
        ->orderByDesc('items.stock_quantity')
        ->limit(5)
        ->get();

    $stockRiskForecast = DB::table('items')
        ->leftJoinSub($itemSales30d, 'sales_30d', function ($join) {
            $join->on('sales_30d.item_id', '=', 'items.item_id');
        })
        ->select(
            'items.item_id',
            'items.item_name',
            'items.stock_quantity',
            DB::raw('COALESCE(sales_30d.sold_30d, 0) as sold_30d')
        )
        ->get()
        ->map(function ($item) {
            $avgDaily = ((float) $item->sold_30d) / 30;
            $daysLeft = $avgDaily > 0 ? ((float) $item->stock_quantity) / $avgDaily : null;

            $item->avg_daily_sales = $avgDaily;
            $item->days_left = $daysLeft;

            return $item;
        })
        ->filter(function ($item) {
            return $item->avg_daily_sales > 0;
        })
        ->sortBy('days_left')
        ->take(5)
        ->values();

    $branchPerformance = DB::table('orders')
        ->leftJoin('franchisees', 'franchisees.franchisee_id', '=', 'orders.franchisee_id')
        ->where('orders.order_status', 'Delivered')
        ->whereNotNull('orders.franchisee_id')
        ->select(
            'orders.franchisee_id',
            DB::raw("COALESCE(franchisees.franchisee_name, 'Unknown Franchisee') as franchisee_name"),
            DB::raw('COUNT(*) as orders_count'),
            DB::raw('SUM(orders.total_amount) as total_sales'),
            DB::raw('AVG(orders.total_amount) as average_order_value')
        )
        ->groupBy('orders.franchisee_id', 'franchisees.franchisee_name')
        ->orderByDesc('total_sales')
        ->get();

    $topBranches = $branchPerformance->take(3)->values();
    $bottomBranches = $branchPerformance->sortBy('total_sales')->take(3)->values();

    $orderTrendRaw = DB::table('orders')
        ->where('order_status', 'Delivered')
        ->whereDate('order_date', '>=', $startOf7Days->toDateString())
        ->select(
            DB::raw('DATE(order_date) as trend_date'),
            DB::raw('SUM(total_amount) as revenue'),
            DB::raw('COUNT(*) as orders_count')
        )
        ->groupBy(DB::raw('DATE(order_date)'))
        ->orderBy('trend_date')
        ->get()
        ->keyBy('trend_date');

    $stockoutsTrendRaw = DB::table('stock_transactions')
        ->whereDate('created_at', '>=', $startOf7Days->toDateString())
        ->where('balance_after', '<=', 0)
        ->select(
            DB::raw('DATE(created_at) as trend_date'),
            DB::raw('COUNT(DISTINCT item_id) as stockout_items')
        )
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('trend_date')
        ->get()
        ->keyBy('trend_date');

    $trendData = collect(range(0, 6))->map(function ($offset) use ($startOf7Days, $orderTrendRaw, $stockoutsTrendRaw) {
        $date = $startOf7Days->copy()->addDays($offset);
        $key = $date->toDateString();

        return [
            'label' => $date->format('M d'),
            'revenue' => (float) ($orderTrendRaw[$key]->revenue ?? 0),
            'orders_count' => (int) ($orderTrendRaw[$key]->orders_count ?? 0),
            'stockout_items' => (int) ($stockoutsTrendRaw[$key]->stockout_items ?? 0),
        ];
    });

    $digitalMarketing = \App\Models\DigitalMarketingUpload::latest()->get();

    return view('admin.dashboard', compact(
        'totalItemsSold',
        'lowStockCount',
        'outOfStockCount',
        'todayRevenue',
        'weekRevenue',
        'monthRevenue',
        'ordersByStatus',
        'averageOrderValue',
        'topSellingItems',
        'slowMovingItems',
        'stockRiskForecast',
        'topBranches',
        'bottomBranches',
        'trendData',
        'digitalMarketing'
    ));
})->name('admin.dashboard');

// ACCOUNT CREATION (Admin only)
Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('/admin/accounts/store', [AccountController::class, 'store'])->name('accounts.store');
});

//Account Creation for Franchisee Staff (Franchisee)
Route::middleware(['auth:franchisee'])->group(function () {
    Route::get('/franchisee/account/create', [AccountController::class, 'createFranchiseeStaff'])->name('account.create');
    Route::post('/franchisee/account/store', [AccountController::class, 'storeFranchiseeStaff'])->name('account.store');
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

// Franchisee Dashboard
Route::middleware(['auth:franchisee'])->get('/franchisee/dashboard', function () {
    $digitalMarketing = \App\Models\DigitalMarketingUpload::latest()->get();
    return view('franchisee.dashboard', compact('digitalMarketing'));
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
Route::middleware('auth:franchisee_staff')->group(function () {
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
Route::middleware(['auth:franchisee_staff'])->prefix('franchisee-staff/stock')->name('franchisee-staff.stock.')->group(function () {
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
    Route::get('/financial', [\App\Http\Controllers\Franchisee\BranchManagementController::class, 'financial'])->name('financial');
});

// Stock Management for Franchisee
Route::middleware(['auth:franchisee'])->prefix('franchisee/stock')->name('franchisee.stock.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Franchisee\StockController::class, 'index'])->name('index');
    Route::get('/{stockId}/edit', [\App\Http\Controllers\Franchisee\StockController::class, 'edit'])->name('edit');
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



