<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DigitalMarketingUpload;
use App\Models\Franchisee;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalItemsSold = (int) DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
            ->whereRaw('LOWER(orders.order_status) = ?', ['delivered'])
            ->sum('order_details.quantity');

        $today = now();
        $startOfWeek = now()->copy()->startOfWeek();
        $startOfMonth = now()->copy()->startOfMonth();
        $startOf30Days = now()->copy()->subDays(30);
        $startOf7Days = now()->copy()->subDays(6)->startOfDay();

        $lowStockCount = Item::where('stock_quantity', '>', 0)
            ->where('stock_quantity', '<=', 10)
            ->count();

        $outOfStockCount = Item::where('stock_quantity', '<=', 0)->count();

        $totalFranchisees = Franchisee::count();
        $activeFranchisees = Franchisee::where('franchisee_status', 'Active')->count();

        $totalOrders = Order::count();
        $pendingOrders = Order::whereRaw('LOWER(order_status) = ?', ['pending'])->count();
        $preparingOrders = Order::whereRaw('LOWER(order_status) = ?', ['preparing'])->count();
        $shippedOrders = Order::whereRaw('LOWER(order_status) = ?', ['shipped'])->count();
        $deliveredOrders = Order::whereRaw('LOWER(order_status) = ?', ['delivered'])->count();
        $cancelledOrders = Order::whereRaw('LOWER(order_status) = ?', ['cancelled'])->count();

        $totalSales = (float) Order::query()
            ->whereRaw('LOWER(order_status) = ?', ['delivered'])
            ->sum('total_amount');

        $salesThisMonth = (float) Order::query()
            ->whereRaw('LOWER(order_status) = ?', ['delivered'])
            ->whereMonth('order_date', now()->month)
            ->whereYear('order_date', now()->year)
            ->sum('total_amount');

        $today = now()->startOfDay();
        $fromDate = $today->copy()->subDays(13);

        $trendRows = DB::table('orders')
            ->selectRaw('DATE(order_date) as chart_date, SUM(total_amount) as total_sales, COUNT(*) as order_count')
            ->whereRaw('LOWER(order_status) = ?', ['delivered'])
            ->whereDate('order_date', '>=', $fromDate->toDateString())
            ->groupByRaw('DATE(order_date)')
            ->orderByRaw('DATE(order_date)')
            ->get();

        $salesByDate = [];
        $ordersByDate = [];

        foreach ($trendRows as $row) {
            $key = (string) $row->chart_date;
            $salesByDate[$key] = (float) $row->total_sales;
            $ordersByDate[$key] = (int) $row->order_count;
        }

        $salesTrendLabels = [];
        $salesTrendValues = [];
        $salesTrendOrderCounts = [];

        for ($offset = 13; $offset >= 0; $offset--) {
            $day = $today->copy()->subDays($offset);
            $key = $day->toDateString();

            $salesTrendLabels[] = $day->format('M d');
            $salesTrendValues[] = round($salesByDate[$key] ?? 0, 2);
            $salesTrendOrderCounts[] = $ordersByDate[$key] ?? 0;
        }

        $recentOrders = Order::query()
            ->select('order_id', 'name', 'order_status', 'payment_status', 'total_amount', 'order_date')
            ->orderByDesc('order_date')
            ->limit(8)
            ->get();

        $todayRevenue = (float) DB::table('orders')
            ->whereRaw('LOWER(order_status) = ?', ['delivered'])
            ->whereDate('order_date', $today->toDateString())
            ->sum('total_amount');

        $weekRevenue = (float) DB::table('orders')
            ->whereRaw('LOWER(order_status) = ?', ['delivered'])
            ->whereDate('order_date', '>=', $startOfWeek->toDateString())
            ->sum('total_amount');

        $monthRevenue = (float) DB::table('orders')
            ->whereRaw('LOWER(order_status) = ?', ['delivered'])
            ->whereDate('order_date', '>=', $startOfMonth->toDateString())
            ->sum('total_amount');

        $ordersByStatus = [
            'Pending' => $pendingOrders,
            'Preparing' => $preparingOrders,
            'Shipped' => $shippedOrders,
            'Delivered' => $deliveredOrders,
            'Cancelled' => $cancelledOrders,
        ];

        $averageOrderValue = $deliveredOrders > 0
            ? $totalSales / $deliveredOrders
            : 0;

        $topSellingItems = DB::table('order_details')
            ->join('orders', 'orders.order_id', '=', 'order_details.order_id')
            ->join('items', 'items.item_id', '=', 'order_details.item_id')
            ->whereRaw('LOWER(orders.order_status) = ?', ['delivered'])
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
            ->whereRaw('LOWER(orders.order_status) = ?', ['delivered'])
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
            ->whereRaw('LOWER(orders.order_status) = ?', ['delivered'])
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
            ->whereRaw('LOWER(order_status) = ?', ['delivered'])
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

        $digitalMarketing = DigitalMarketingUpload::query()->notArchived()->latest()->get();

        return view('admin.dashboard', compact(
            'totalItemsSold',
            'lowStockCount',
            'outOfStockCount',
            'totalFranchisees',
            'activeFranchisees',
            'totalOrders',
            'pendingOrders',
            'preparingOrders',
            'shippedOrders',
            'deliveredOrders',
            'cancelledOrders',
            'totalSales',
            'salesThisMonth',
            'salesTrendLabels',
            'salesTrendValues',
            'salesTrendOrderCounts',
            'recentOrders',
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
    }
}
