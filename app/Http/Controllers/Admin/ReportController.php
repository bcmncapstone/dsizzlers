<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Franchisee;
use App\Models\Order;
use App\Models\Item;
use App\Models\StockTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function sales(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()->with('error', 'End date cannot be earlier than start date.');
        }

        $query = DB::table('order_details')
            ->join('items', 'order_details.item_id', '=', 'items.item_id')
            ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
            ->select(
                'order_details.order_id',
                'order_details.item_id',
                'items.item_name',
                'items.item_category',
                'order_details.quantity',
                'order_details.subtotal',
                'order_details.price',
                'orders.order_date'
            )
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('orders.order_date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('orders.order_date', '<=', $request->end_date);
            });

        $summaryQuery = clone $query;
        $totalSales = $summaryQuery->sum('order_details.subtotal');
        $totalQuantity = $summaryQuery->sum('order_details.quantity');

        $orderDetails = $query->orderBy('orders.order_date', 'desc')->paginate(50);
        $noData = $orderDetails->isEmpty();
        $availableRange = $noData ? $this->getOrderDateRangeAll() : null;

        // Get data for charts (use full query without pagination)
        $chartQuery = DB::table('order_details')
            ->join('items', 'order_details.item_id', '=', 'items.item_id')
            ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
            ->select(
                'order_details.item_id',
                'items.item_name',
                'items.item_category',
                'order_details.quantity',
                'order_details.subtotal',
                'orders.order_date'
            )
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('orders.order_date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('orders.order_date', '<=', $request->end_date);
            })
            ->get();

        // Top selling items by quantity
        $topItems = collect($chartQuery)
            ->groupBy('item_name')
            ->map(function($group) { 
                return [
                    'name' => $group->first()->item_name,
                    'quantity' => $group->sum('quantity'),
                    'sales' => $group->sum('subtotal')
                ];
            })
            ->sortByDesc('quantity')
            ->take(10)
            ->values()
            ->toArray();

        // Sales by category
        $salesByCategory = collect($chartQuery)
            ->groupBy('item_category')
            ->map(function($group) { 
                return [
                    'category' => $group->first()->item_category ?? 'Uncategorized',
                    'quantity' => $group->sum('quantity'),
                    'sales' => $group->sum('subtotal')
                ];
            })
            ->sortByDesc('sales')
            ->values()
            ->toArray();

        // Daily sales trend
        $dailySales = collect($chartQuery)
            ->groupBy(function($item) { 
                return \Carbon\Carbon::parse($item->order_date)->format('Y-m-d');
            })
            ->map(function($group) { 
                return [
                    'date' => $group->first()->order_date,
                    'sales' => $group->sum('subtotal'),
                    'quantity' => $group->sum('quantity')
                ];
            })
            ->sortBy('date')
            ->values()
            ->toArray();

        return view('admin.reports.sales', compact(
            'orderDetails',
            'totalSales',
            'totalQuantity',
            'noData',
            'availableRange',
            'topItems',
            'salesByCategory',
            'dailySales'
        ));
    }

    public function salesPdf(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()->with('error', 'End date cannot be earlier than start date.');
        }

        $query = DB::table('order_details')
            ->join('items', 'order_details.item_id', '=', 'items.item_id')
            ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
            ->select(
                'order_details.order_id',
                'order_details.item_id',
                'items.item_name',
                'items.item_category',
                'order_details.quantity',
                'order_details.subtotal',
                'order_details.price',
                'orders.order_date'
            )
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('orders.order_date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('orders.order_date', '<=', $request->end_date);
            });

        $orderDetails = $query->orderBy('orders.order_date', 'desc')->get();

        if ($orderDetails->isEmpty()) {
            return redirect()->back()->with('error', 'No sales data found for the selected filters.');
        }

        $totalSales = $orderDetails->sum('subtotal');
        $totalQuantity = $orderDetails->sum('quantity');

        // Get top selling items for PDF
        $topItemsCollection = collect($orderDetails)
            ->groupBy('item_name')
            ->map(function($group) { 
                return [
                    'name' => $group->first()->item_name,
                    'quantity' => (int)$group->sum('quantity'),
                    'sales' => (float)$group->sum('subtotal')
                ];
            })
            ->sortByDesc('quantity')
            ->take(5)
            ->values();
        
        $topItems = array_values($topItemsCollection->toArray());

        // Sales by category for PDF
        $salesByCategoryCollection = collect($orderDetails)
            ->groupBy('item_category')
            ->map(function($group) { 
                return [
                    'category' => $group->first()->item_category ?? 'Uncategorized',
                    'quantity' => (int)$group->sum('quantity'),
                    'sales' => (float)$group->sum('subtotal')
                ];
            })
            ->sortByDesc('sales')
            ->values();
        
        $salesByCategory = array_values($salesByCategoryCollection->toArray());

        $pdf = Pdf::loadView('admin.reports.pdf.sales', [
            'orderDetails' => $orderDetails,
            'totalSales' => $totalSales,
            'totalQuantity' => $totalQuantity,
            'topItems' => $topItems,
            'salesByCategory' => $salesByCategory,
            'filters' => $request->only(['start_date', 'end_date']),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('sales-report.pdf');
    }

    public function inventory(Request $request)
    {
        // Get all items with their current stock levels
        $items = Item::all();

        // Categorize items by stock status
        $inStock = $items->filter(function($item) { return $item->stock_quantity > 10; });
        $lowStock = $items->filter(function($item) { return $item->stock_quantity > 0 && $item->stock_quantity <= 10; });
        $outOfStock = $items->filter(function($item) { return $item->stock_quantity == 0; });

        // Prepare data for pie chart
        $stockDistribution = [
            'in_stock' => $inStock->count(),
            'low_stock' => $lowStock->count(),
            'out_of_stock' => $outOfStock->count(),
        ];

        // Calculate total inventory value and quantity
        $totalQuantity = $items->sum('stock_quantity');
        $totalValue = $items->sum(function($item) { return $item->stock_quantity * $item->price; });
        $averagePrice = $items->count() > 0 ? $items->avg('price') : 0;

        // Get top items by stock quantity
        $topItems = $items->sortByDesc('stock_quantity')->take(10);
        $lowStockItems = $lowStock->sortBy('stock_quantity')->take(10);

        return view('admin.reports.inventory', compact(
            'items',
            'inStock',
            'lowStock',
            'outOfStock',
            'stockDistribution',
            'totalQuantity',
            'totalValue',
            'averagePrice',
            'topItems',
            'lowStockItems'
        ));
    }

    public function inventoryPdf(Request $request)
    {
        // Get all items with their current stock levels
        $items = Item::all();

        // Categorize items by stock status
        $inStock = $items->filter(function($item) { return $item->stock_quantity > 10; });
        $lowStock = $items->filter(function($item) { return $item->stock_quantity > 0 && $item->stock_quantity <= 10; });
        $outOfStock = $items->filter(function($item) { return $item->stock_quantity == 0; });

        // Calculate totals
        $totalQuantity = $items->sum('stock_quantity');
        $totalValue = $items->sum(function($item) { return $item->stock_quantity * $item->price; });

        $pdf = Pdf::loadView('admin.reports.pdf.inventory', [
            'items' => $items,
            'inStock' => $inStock,
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
            'totalQuantity' => $totalQuantity,
            'totalValue' => $totalValue,
        ])->setPaper('A4', 'landscape');

        return $pdf->download('inventory-report.pdf');
    }

    public function franchiseeSales(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()->with('error', 'End date cannot be earlier than start date.');
        }

        $franchisees = Franchisee::orderBy('franchisee_name')->get();

        $query = Order::query()
            ->select('franchisee_id', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total_amount) as total_sales'))
            ->whereNotNull('franchisee_id')
            ->when($request->franchisee_id, function ($q) use ($request) {
                $q->where('franchisee_id', $request->franchisee_id);
            })
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('order_date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('order_date', '<=', $request->end_date);
            })
            ->groupBy('franchisee_id');

        $rows = $query->get();
        $noData = $rows->isEmpty();
        $availableRange = $noData ? $this->getOrderDateRange($request->franchisee_id) : null;

        $franchiseeMap = $franchisees->keyBy('franchisee_id');

        // Prepare chart data
        $chartRowsCollection = $rows->map(function($row) use ($franchiseeMap) {
            return [
                'name' => $franchiseeMap[$row->franchisee_id]->franchisee_name ?? 'Unknown',
                'orders' => (int)$row->orders_count,
                'sales' => (float)$row->total_sales
            ];
        })
        ->sortByDesc('sales')
        ->values();
        
        $chartRows = array_values(array_unique($chartRowsCollection->toArray(), SORT_REGULAR));

        $totalSales = $rows->sum('total_sales');
        $totalOrders = $rows->sum('orders_count');
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
        $topFranchisee = count($chartRows) > 0 ? $chartRows[0] : null;

        return view('admin.reports.franchisee-sales', compact(
            'franchisees',
            'rows',
            'noData',
            'availableRange',
            'franchiseeMap',
            'chartRows',
            'totalSales',
            'totalOrders',
            'averageOrderValue',
            'topFranchisee'
        ));
    }

    public function franchiseeSalesPdf(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()->with('error', 'End date cannot be earlier than start date.');
        }

        $query = Order::query()
            ->select('franchisee_id', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total_amount) as total_sales'))
            ->whereNotNull('franchisee_id')
            ->when($request->franchisee_id, function ($q) use ($request) {
                $q->where('franchisee_id', $request->franchisee_id);
            })
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('order_date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('order_date', '<=', $request->end_date);
            })
            ->groupBy('franchisee_id');

        $rows = $query->get();

        if ($rows->isEmpty()) {
            return redirect()->back()->with('error', 'No franchisee sales data found for the selected filters.');
        }

        $franchisees = Franchisee::orderBy('franchisee_name')->get()->keyBy('franchisee_id');

        // Prepare chart data
        $chartRowsCollection = $rows->map(function($row) use ($franchisees) {
            return [
                'name' => $franchisees[$row->franchisee_id]->franchisee_name ?? 'Unknown',
                'orders' => (int)$row->orders_count,
                'sales' => (float)$row->total_sales
            ];
        })
        ->sortByDesc('sales')
        ->values();
        
        $chartRows = array_values(array_unique($chartRowsCollection->toArray(), SORT_REGULAR));

        $totalSales = $rows->sum('total_sales');
        $totalOrders = $rows->sum('orders_count');

        $pdf = Pdf::loadView('admin.reports.pdf.franchisee-sales', [
            'rows' => $rows,
            'franchisees' => $franchisees,
            'chartRows' => $chartRows,
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'filters' => $request->only(['franchisee_id', 'start_date', 'end_date']),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('franchisee-sales-report.pdf');
    }

    private function hasInvalidDateRange(Request $request): bool
    {
        return $request->start_date && $request->end_date && $request->end_date < $request->start_date;
    }

    private function getOrderDateRange(?int $franchiseeId)
    {
        return Order::query()
            ->when($franchiseeId, function ($q) use ($franchiseeId) {
                $q->where('franchisee_id', $franchiseeId);
            })
            ->selectRaw('MIN(order_date) as min_date, MAX(order_date) as max_date')
            ->first();
    }

    private function getOrderDateRangeAll()
    {
        return Order::query()
            ->selectRaw('MIN(order_date) as min_date, MAX(order_date) as max_date')
            ->first();
    }

    private function getStockDateRange(?int $franchiseeId)
    {
        return StockTransaction::query()
            ->when($franchiseeId, function ($q) use ($franchiseeId) {
                $q->where('franchisee_id', $franchiseeId);
            })
            ->selectRaw('MIN(created_at) as min_date, MAX(created_at) as max_date')
            ->first();
    }
}
