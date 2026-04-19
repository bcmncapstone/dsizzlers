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


        // Keep dashboard totals aligned with the Performance and Financial pages.
        $salesBaseQuery = StockTransaction::where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
            ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
            ->where(function ($q) {
                $q->where(function ($manual) {
                    $manual->where('stock_transactions.transaction_type', 'adjustment')
                        ->where('stock_transactions.quantity', '<', 0);
                })->orWhere(function ($staffOut) {
                    $staffOut->where('stock_transactions.transaction_type', 'out')
                        ->where('stock_transactions.performed_by_type', 'franchisee_staff');
                });
            });

        // Total Orders/Sales use the same transaction set as branch performance and financial reports.
        $totalOrders = (clone $salesBaseQuery)->count();

        $totalSales = (clone $salesBaseQuery)
            ->selectRaw('COALESCE(SUM(ABS(stock_transactions.quantity) * items.price), 0) as revenue')
            ->value('revenue');

        $salesThisMonth = (clone $salesBaseQuery)
            ->whereMonth('stock_transactions.created_at', Carbon::now()->month)
            ->whereYear('stock_transactions.created_at', Carbon::now()->year)
            ->selectRaw('COALESCE(SUM(ABS(stock_transactions.quantity) * items.price), 0) as revenue')
            ->value('revenue');

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

        // Sales & Orders: include both adjustment (franchisee) and out (franchisee_staff) transactions
        $baseQuery = StockTransaction::where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
            ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
            ->where(function ($q) {
                $q->where(function ($manual) {
                    $manual->where('stock_transactions.transaction_type', 'adjustment')
                        ->where('stock_transactions.quantity', '<', 0);
                })->orWhere(function ($staffOut) {
                    $staffOut->where('stock_transactions.transaction_type', 'out')
                        ->where('stock_transactions.performed_by_type', 'franchisee_staff');
                });
            });
        if ($startDate && $endDate) {
            $baseQuery->whereBetween('stock_transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        // Total Sales
        $totalSales = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(ABS(stock_transactions.quantity) * items.price), 0) as revenue')
            ->value('revenue');

        // Total Orders (count of transactions)
        $totalOrders = (clone $baseQuery)->count();

        // Average order value
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Sales trend: group by date, sum revenue
        $trendQuery = StockTransaction::where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
            ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
            ->where(function ($q) {
                $q->where(function ($manual) {
                    $manual->where('stock_transactions.transaction_type', 'adjustment')
                        ->where('stock_transactions.quantity', '<', 0);
                })->orWhere(function ($staffOut) {
                    $staffOut->where('stock_transactions.transaction_type', 'out')
                        ->where('stock_transactions.performed_by_type', 'franchisee_staff');
                });
            })
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
            ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
            ->where(function ($q) {
                $q->where(function ($manual) {
                    $manual->where('stock_transactions.transaction_type', 'adjustment')
                        ->where('stock_transactions.quantity', '<', 0);
                })->orWhere(function ($staffOut) {
                    $staffOut->where('stock_transactions.transaction_type', 'out')
                        ->where('stock_transactions.performed_by_type', 'franchisee_staff');
                });
            });
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
                'price' => $stock->item->price,
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


        // Calculate total revenue (filtered by date if provided) using both adjustment (franchisee) and out (franchisee_staff) logic
        $salesQuery = StockTransaction::where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
            ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
            ->where(function ($q) {
                $q->where(function ($manual) {
                    $manual->where('stock_transactions.transaction_type', 'adjustment')
                        ->where('stock_transactions.quantity', '<', 0);
                })->orWhere(function ($staffOut) {
                    $staffOut->where('stock_transactions.transaction_type', 'out')
                        ->where('stock_transactions.performed_by_type', 'franchisee_staff');
                });
            });

        if ($startDate && $endDate) {
            $salesQuery->whereBetween('stock_transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            $dateRangeLabel = Carbon::parse($startDate)->format('M d, Y') . ' - ' . Carbon::parse($endDate)->format('M d, Y');
        } else {
            $dateRangeLabel = 'All Time';
        }

        $totalRevenue = $salesQuery->selectRaw('COALESCE(SUM(ABS(stock_transactions.quantity) * items.price), 0) as revenue')->value('revenue');

        // Get sales data grouped by date
        $trendQuery = StockTransaction::where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
            ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
            ->where(function ($q) {
                $q->where(function ($manual) {
                    $manual->where('stock_transactions.transaction_type', 'adjustment')
                        ->where('stock_transactions.quantity', '<', 0);
                })->orWhere(function ($staffOut) {
                    $staffOut->where('stock_transactions.transaction_type', 'out')
                        ->where('stock_transactions.performed_by_type', 'franchisee_staff');
                });
            })
            ->selectRaw('DATE(stock_transactions.created_at) as date, COALESCE(SUM(ABS(stock_transactions.quantity) * items.price), 0) as total')
            ->groupBy('date')
            ->orderBy('date', 'desc');

        if ($startDate && $endDate) {
            $trendQuery->whereBetween('stock_transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } else {
            $trendQuery->where('stock_transactions.created_at', '>=', Carbon::now()->subDays(30));
        }

        $salesData = $trendQuery->get()->map(function($item) {
            return [
                'date' => Carbon::parse($item->date)->format('M d, Y'),
                'total' => $item->total
            ];
        });

        // Current month stats
        $currentMonthRevenue = StockTransaction::where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
            ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
            ->where(function ($q) {
                $q->where(function ($manual) {
                    $manual->where('stock_transactions.transaction_type', 'adjustment')
                        ->where('stock_transactions.quantity', '<', 0);
                })->orWhere(function ($staffOut) {
                    $staffOut->where('stock_transactions.transaction_type', 'out')
                        ->where('stock_transactions.performed_by_type', 'franchisee_staff');
                });
            })
            ->whereMonth('stock_transactions.created_at', Carbon::now()->month)
            ->whereYear('stock_transactions.created_at', Carbon::now()->year)
            ->selectRaw('COALESCE(SUM(ABS(stock_transactions.quantity) * items.price), 0) as revenue')
            ->value('revenue');

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
        $stock = FranchiseeStock::where('franchisee_id', $franchisee->franchisee_id)
            ->where('item_id', $itemId)
            ->first();

        if (!$stock) {
            return redirect()->back()->with('error', 'Stock item not found.');
        }

        $direction = request('direction'); // 'add' or 'deduct'
        $adjustBy = request('adjust_by');

        if (!in_array($direction, ['add', 'deduct'])) {
            return redirect()->back()->with('error', 'Invalid adjustment direction.');
        }
        if (!is_numeric($adjustBy) || (int)$adjustBy < 1) {
            return redirect()->back()->with('error', 'Adjustment quantity must be a positive number.');
        }
        // Notes validation removed

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
        StockTransaction::create([
            'franchisee_id' => $franchisee->franchisee_id,
            'item_id' => $itemId,
            'quantity' => $direction === 'deduct' ? -$qty : $qty,
            'transaction_type' => 'adjustment',
            'balance_after' => $stock->current_quantity,
        ]);

        return redirect()->back()
            ->with('success', 'Inventory adjusted successfully.')
            ->with('flash_timeout', 5000);
    }
}
