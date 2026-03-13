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

        $totalRevenue = Order::where('franchisee_id', $franchisee->franchisee_id)
                             ->sum('total_amount');

        $monthlyRevenue = Order::where('franchisee_id', $franchisee->franchisee_id)
                               ->whereMonth('order_date', Carbon::now()->month)
                               ->whereYear('order_date', Carbon::now()->year)
                               ->sum('total_amount');

        return view('franchisee.branch.dashboard', compact(
            'branch',
            'totalOrders',
            'totalRevenue',
            'monthlyRevenue'
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

        // Get date filters from request
        $startDate = request('start_date');
        $endDate = request('end_date');

        // Build base query for stock transactions (manual adjustments only - negative quantities = sales)
        $baseQuery = StockTransaction::where('franchisee_id', $franchisee->franchisee_id)
                                    ->where('transaction_type', 'adjustment')
                                    ->where('quantity', '<', 0); // Only sold items (negative quantity)
        
        if ($startDate && $endDate) {
            $baseQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        // Get total sales (sum of negative quantities converted to positive)
        $totalSales = abs((clone $baseQuery)->sum('quantity'));

        // Get total orders (count of transactions)
        $totalOrders = (clone $baseQuery)->count();

        // Average order value
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Sales trend grouped by date - from stock transactions
        $salesQuery = StockTransaction::where('franchisee_id', $franchisee->franchisee_id)
                                      ->where('transaction_type', 'adjustment')
                                      ->where('quantity', '<', 0)
                                      ->selectRaw('DATE(created_at) as date, ABS(SUM(quantity)) as sales')
                                      ->groupBy('date')
                                      ->orderBy('date', 'desc');
        
        if ($startDate && $endDate) {
            $salesQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } else {
            // Default to last 30 days if no filter
            $salesQuery->where('created_at', '>=', Carbon::now()->subDays(30));
        }
        
        $salesTrend = $salesQuery->get()->map(function($item) {
            return [
                'date' => Carbon::parse($item->date)->format('M d, Y'),
                'sales' => $item->sales
            ];
        });

        // Top selling items - from stock transactions (manual reductions)
        $topSellingQuery = StockTransaction::where('franchisee_id', $franchisee->franchisee_id)
                                          ->where('transaction_type', 'adjustment')
                                          ->where('quantity', '<', 0)
                                          ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
                                          ->selectRaw('items.item_name, ABS(SUM(stock_transactions.quantity)) as total_quantity, ABS(SUM(stock_transactions.quantity)) as total_revenue')
                                          ->groupBy('items.item_id', 'items.item_name')
                                          ->orderBy('total_quantity', 'desc')
                                          ->limit(10);
        
        if ($startDate && $endDate) {
            $topSellingQuery->whereBetween('stock_transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        
        $topSellingItems = $topSellingQuery->get();

        // Recent orders - show recent stock transactions (sales)
        $recentOrdersQuery = Order::where('franchisee_id', $franchisee->franchisee_id);
        
        if ($startDate && $endDate) {
            $recentOrdersQuery->whereBetween('order_date', [$startDate, $endDate]);
        }
        
        $recentOrders = $recentOrdersQuery->orderBy('order_date', 'desc')
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
}
