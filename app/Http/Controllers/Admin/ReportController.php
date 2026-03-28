<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Franchisee;
use App\Models\Order;
use App\Models\Item;
use App\Models\StockTransaction;
use App\Services\FifoStockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function __construct(private FifoStockService $fifoStockService)
    {
    }

    public function index()
    {
        return view('admin.reports.index');
    }

    public function sales(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()
            ->with('flash_timeout', 3000);
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
            ->where('orders.order_status', 'Delivered')
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
        $availableRange = $noData ? $this->getOrderDateRangeAll(true) : null;

        // Get data for charts (use full query without pagination)
        $chartQuery = DB::table('order_details')
            ->join('items', 'order_details.item_id', '=', 'items.item_id')
            ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
            ->where('orders.order_status', 'Delivered')
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
            return redirect()->back()
            ->with('flash_timeout', 3000);
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
            ->where('orders.order_status', 'Delivered')
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('orders.order_date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('orders.order_date', '<=', $request->end_date);
            });

        $orderDetails = $query->orderBy('orders.order_date', 'desc')->get();

        if ($orderDetails->isEmpty()) {
            return redirect()->back()
            ->with('flash_timeout', 3000);
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
        // Get non-archived items with their current stock levels
        $archivedIds = $this->getArchivedItemIds();
        $items = Item::query()
            ->when(!empty($archivedIds), fn ($query) => $query->whereNotIn('item_id', $archivedIds))
            ->get();

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

        // FIFO visibility (selected item)
        $fifoFilterItems = $items->sortBy('item_name')->values();
        $selectedFifoItemId = (int) $request->integer('fifo_item_id');

        if ($selectedFifoItemId <= 0 && $fifoFilterItems->isNotEmpty()) {
            $selectedFifoItemId = (int) $fifoFilterItems->first()->item_id;
        }

        $selectedFifoItem = $fifoFilterItems->firstWhere('item_id', $selectedFifoItemId);
        if (!$selectedFifoItem && $fifoFilterItems->isNotEmpty()) {
            $selectedFifoItem = $fifoFilterItems->first();
            $selectedFifoItemId = (int) $selectedFifoItem->item_id;
        }

        $fifoSnapshot = $selectedFifoItem
            ? $this->fifoStockService->getRemainingLots($selectedFifoItem)
            : null;

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
            'lowStockItems',
            'fifoFilterItems',
            'selectedFifoItemId',
            'fifoSnapshot'
        ));
    }

    public function inventoryPdf(Request $request)
    {
        // Get non-archived items with their current stock levels
        $archivedIds = $this->getArchivedItemIds();
        $items = Item::query()
            ->when(!empty($archivedIds), fn ($query) => $query->whereNotIn('item_id', $archivedIds))
            ->orderBy('item_name')
            ->get();

        // Categorize items by stock status
        $inStock = $items->filter(function($item) { return $item->stock_quantity > 10; });
        $lowStock = $items->filter(function($item) { return $item->stock_quantity > 0 && $item->stock_quantity <= 10; });
        $outOfStock = $items->filter(function($item) { return $item->stock_quantity == 0; });

        // Calculate totals
        $totalQuantity = $items->sum('stock_quantity');
        $totalValue = $items->sum(function($item) { return $item->stock_quantity * $item->price; });

        // Build FIFO lot snapshots for all items (non-empty lots only)
        $fifoSnapshots = $items->map(function (Item $item) {
            return $this->fifoStockService->getRemainingLots($item);
        })->filter(function (array $snap) {
            return count($snap['lots']) > 0;
        })->values()->toArray();

        $pdf = Pdf::loadView('admin.reports.pdf.inventory', [
            'items' => $items,
            'inStock' => $inStock,
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
            'totalQuantity' => $totalQuantity,
            'totalValue' => $totalValue,
            'fifoSnapshots' => $fifoSnapshots,
        ])->setPaper('A4', 'landscape');

        return $pdf->download('inventory-report.pdf');
    }

    public function franchiseeSales(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()
            ->with('flash_timeout', 3000);
        }

        $franchisees = Franchisee::orderBy('franchisee_name')->get();

        $query = Order::query()
            ->select('franchisee_id', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total_amount) as total_sales'))
            ->whereNotNull('franchisee_id')
            ->where('order_status', 'Delivered')
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
        $availableRange = $noData ? $this->getOrderDateRange($request->franchisee_id, true) : null;

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
            return redirect()->back()
            ->with('flash_timeout', 3000);
        }

        $query = Order::query()
            ->select('franchisee_id', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total_amount) as total_sales'))
            ->whereNotNull('franchisee_id')
            ->where('order_status', 'Delivered')
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
            return redirect()->back()
            ->with('flash_timeout', 3000);
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

    private function getArchivedItemIds(): array
    {
        if (!Storage::disk('local')->exists('archived_items.json')) {
            return [];
        }

        $raw = Storage::disk('local')->get('archived_items.json');
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $data)));
    }

    private function getOrderDateRange(?int $franchiseeId, bool $deliveredOnly = false)
    {
        return Order::query()
            ->when($franchiseeId, function ($q) use ($franchiseeId) {
                $q->where('franchisee_id', $franchiseeId);
            })
            ->when($deliveredOnly, function ($q) {
                $q->where('order_status', 'Delivered');
            })
            ->selectRaw('MIN(order_date) as min_date, MAX(order_date) as max_date')
            ->first();
    }

    private function getOrderDateRangeAll(bool $deliveredOnly = false)
    {
        return Order::query()
            ->when($deliveredOnly, function ($q) {
                $q->where('order_status', 'Delivered');
            })
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
