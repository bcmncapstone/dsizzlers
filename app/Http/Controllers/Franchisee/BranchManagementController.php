<?php

namespace App\Http\Controllers\Franchisee;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Item;
use App\Models\FranchiseeStock;
use App\Models\StockTransaction;
use Carbon\Carbon;

class BranchManagementController extends Controller
{
    /**
     * Show the branch management dashboard
     */
    public function dashboard()
    {
        $franchisee = Auth::guard('franchisee')->user();
        
        // Get the franchisee's branch
        $branch = Branch::where('email', $franchisee->franchisee_email)
                        ->where('branch_status', '=', DB::raw('TRUE'))
                        ->first();

        if (!$branch) {
            return redirect()->route('franchisee.dashboard')
                ->with('error', 'No active branch found for your account.');
        }

        // Get basic performance metrics for the dashboard overview
        $totalOrders = Order::where('franchisee_id', $franchisee->franchisee_id)
                            ->count();

        $totalSales = Order::where('franchisee_id', $franchisee->franchisee_id)
                          ->sum('total_amount');

        $salesThisMonth = Order::where('franchisee_id', $franchisee->franchisee_id)
                              ->whereMonth('order_date', Carbon::now()->month)
                              ->whereYear('order_date', Carbon::now()->year)
                              ->sum('total_amount');

        return view('franchisee.branch.dashboard', compact(
            'branch',
            'totalOrders',
            'totalSales',
            'salesThisMonth'
        ));
    }

    /**
     * Show performance metrics
     * Displays sales and operational performance of the branch with date filtering
     * Sales are based on manual stock reductions by the franchisee
     */
    public function performance()
    {
        $franchisee = Auth::guard('franchisee')->user();
        $startDate = request('start_date');
        $endDate = request('end_date');

        // Sales: sum of ABS(quantity) * price for adjustment transactions (franchisee only)
        $salesQuery = StockTransaction::where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
            ->where('stock_transactions.transaction_type', 'adjustment')
            ->where('stock_transactions.quantity', '<', 0)
            ->join('items', 'stock_transactions.item_id', '=', 'items.item_id');
        if ($startDate && $endDate) {
            $salesQuery->whereBetween('stock_transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        $totalSales = $salesQuery->selectRaw('COALESCE(SUM(ABS(stock_transactions.quantity) * items.price), 0) as revenue')->value('revenue');

        // Total orders: count of unique adjustment transactions (or you may want to count unique days or another logic)
        $totalOrders = (clone $salesQuery)->count();

        // Average order value (if you want to show it)
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Sales trend: group by date, sum revenue
        $trendQuery = StockTransaction::where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
            ->where('stock_transactions.transaction_type', 'adjustment')
            ->where('stock_transactions.quantity', '<', 0)
            ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
            ->selectRaw('DATE(stock_transactions.created_at) as date, COALESCE(SUM(ABS(stock_transactions.quantity) * items.price), 0) as sales')
            ->groupBy('date')
            ->orderBy('date', 'desc');
        if ($startDate && $endDate) {
            $trendQuery->whereBetween('stock_transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } else {
            $trendQuery->where('stock_transactions.created_at', '>=', Carbon::now()->subDays(30));
        }
        $salesTrend = $trendQuery->get()->map(function($item) {
            return [
                'date' => Carbon::parse($item->date)->format('M d, Y'),
                'sales' => $item->sales
            ];
        });

        // Top selling items: sum of ABS(quantity) and revenue by item
        $topSellingQuery = StockTransaction::where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
            ->where('stock_transactions.transaction_type', 'adjustment')
            ->where('stock_transactions.quantity', '<', 0)
            ->join('items', 'stock_transactions.item_id', '=', 'items.item_id');
        if ($startDate && $endDate) {
            $topSellingQuery->whereBetween('stock_transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        $topSellingItems = $topSellingQuery
            ->selectRaw('items.item_name, SUM(ABS(stock_transactions.quantity)) as total_quantity, SUM(ABS(stock_transactions.quantity) * items.price) as total_revenue')
            ->groupBy('items.item_id', 'items.item_name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        // Recent orders: fetch from Order model for correct fields
        $recentOrders = Order::where('franchisee_id', $franchisee->franchisee_id)
            ->orderBy('order_date', 'desc')
            ->limit(5)
            ->get();

        return view('franchisee.branch.performance', compact(
            'totalSales',
            'totalOrders',
            'averageOrderValue',
            'salesTrend',
            'topSellingItems',
            'recentOrders'
        ));
    }

    /**
     * Show inventory levels from franchisee_stock table
     */
    public function inventory()
    {
        $franchisee = Auth::guard('franchisee')->user();

        // Get all stock items for this franchisee from franchisee_stock table
        $stockItems = FranchiseeStock::where('franchisee_id', $franchisee->franchisee_id)
                                     ->with('item')
                                     ->get();

        // Build inventory array with status indicators
        $inventory = [];
        foreach ($stockItems as $stock) {
            $inventory[] = [
                'item_id' => $stock->item_id,
                'item_name' => $stock->item->item_name,
                'item_category' => $stock->item->item_category,
                'current_stock' => $stock->current_quantity,
                'minimum_quantity' => $stock->minimum_quantity,
                'status' => $stock->status,
                'needs_replenishment' => $stock->current_quantity <= $stock->minimum_quantity
            ];
        }

        // Sort by status (out of stock first, then low stock, then in stock)
        usort($inventory, function($a, $b) {
            $statusOrder = ['out_of_stock' => 0, 'low_stock' => 1, 'in_stock' => 2];
            return $statusOrder[$a['status']] <=> $statusOrder[$b['status']];
        });

        // Statistics
        $totalItems = count($inventory);
        $outOfStock = count(array_filter($inventory, fn($item) => $item['status'] === 'out_of_stock'));
        $lowStock = count(array_filter($inventory, fn($item) => $item['status'] === 'low_stock'));
        $inStock = count(array_filter($inventory, fn($item) => $item['status'] === 'in_stock'));

        return view('franchisee.branch.inventory', compact(
            'inventory',
            'totalItems',
            'outOfStock',
            'lowStock',
            'inStock'
        ));
    }

    /**
     * Show financial summary
     * Displays total revenue and sales breakdown with date filtering
     */
    public function financial()
    {
        $franchisee = Auth::guard('franchisee')->user();

        // Get date filters from request
        $startDate = request('start_date');
        $endDate = request('end_date');

        // Calculate total revenue (filtered by date if provided)
        $revenueQuery = Order::where('franchisee_id', $franchisee->franchisee_id);
        
        if ($startDate && $endDate) {
            $revenueQuery->whereBetween('order_date', [$startDate, $endDate]);
            $totalRevenue = $revenueQuery->sum('total_amount');
            $dateRangeLabel = Carbon::parse($startDate)->format('M d, Y') . ' - ' . Carbon::parse($endDate)->format('M d, Y');
        } else {
            $totalRevenue = $revenueQuery->sum('total_amount');
            $dateRangeLabel = 'All Time';
        }

        // Get sales data grouped by date
        $salesQuery = Order::where('franchisee_id', $franchisee->franchisee_id)
                          ->selectRaw('DATE(order_date) as date, SUM(total_amount) as total')
                          ->groupBy('date')
                          ->orderBy('date', 'desc');
        
        if ($startDate && $endDate) {
            $salesQuery->whereBetween('order_date', [$startDate, $endDate]);
        } else {
            // Default to last 30 days if no filter
            $salesQuery->where('order_date', '>=', Carbon::now()->subDays(30));
        }
        
        $salesData = $salesQuery->get()->map(function($item) {
            return [
                'date' => Carbon::parse($item->date)->format('M d, Y'),
                'total' => $item->total
            ];
        });

        // Current month stats
        $currentMonthRevenue = Order::where('franchisee_id', $franchisee->franchisee_id)
                                    ->whereMonth('order_date', Carbon::now()->month)
                                    ->whereYear('order_date', Carbon::now()->year)
                                    ->sum('total_amount');

        return view('franchisee.branch.financial', compact(
            'totalRevenue',
            'salesData',
            'currentMonthRevenue',
            'dateRangeLabel'
        ));
    }
        /**
     * Adjust inventory for a specific item (permanent solution)
     * Handles POST /franchisee/branch/inventory/adjust/{itemId}
     */
    public function adjustInventory($itemId)
    {
        $franchisee = Auth::guard('franchisee')->user();
        $stock = \App\Models\FranchiseeStock::where('franchisee_id', $franchisee->franchisee_id)
            ->where('item_id', $itemId)
            ->first();

        if (!$stock) {
            return redirect()->back()->with('error', 'Stock item not found.');
        }

        $direction = request('direction'); // 'add' or 'deduct'
        $adjustBy = request('adjust_by');
        $notes = request('notes');

        if (!in_array($direction, ['add', 'deduct'])) {
            return redirect()->back()->with('error', 'Invalid adjustment direction.');
        }
        if (!is_numeric($adjustBy) || (int)$adjustBy < 1) {
            return redirect()->back()->with('error', 'Adjustment quantity must be a positive number.');
        }
        if (!$notes || strlen($notes) > 255) {
            return redirect()->back()->with('error', 'Notes are required and must be less than 255 characters.');
        }

        $qty = (int)$adjustBy;
        if ($direction === 'deduct') {
            if ($stock->current_quantity < $qty) {
                return redirect()->back()->with('error', 'Cannot deduct more than current stock.');
            }
            $stock->current_quantity -= $qty;
        } else {
            $stock->current_quantity += $qty;
        }
        $stock->save();

        // Log the adjustment in StockTransaction table, including balance_after
        \App\Models\StockTransaction::create([
            'franchisee_id' => $franchisee->franchisee_id,
            'item_id' => $itemId,
            'quantity' => $direction === 'deduct' ? -$qty : $qty,
            'transaction_type' => 'adjustment',
            'notes' => $notes,
            'balance_after' => $stock->current_quantity,
        ]);

        return redirect()->back()
            ->with('success', 'Inventory adjusted successfully.')
            ->with('flash_timeout', 5000);
    }
}