<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Item;
use App\Services\FifoStockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    private const REPORTS_PER_PAGE = 10;

    public function __construct(private FifoStockService $fifoStockService)
    {
    }

    public function index()
    {
        return view('admin.reports.index');
    }

    public function sales(Request $request)
    {
        $dateBounds = $this->normalizeDateBounds($this->getOrderDateRangeAll(true));

        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()
                ->with('error', 'The end date cannot be earlier than the start date.')
                ->with('flash_timeout', 3000);
        }

        if ($invalidMessage = $this->getUnavailableDateMessage($request, $dateBounds)) {
            return redirect()->back()
                ->with('error', $invalidMessage)
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

        $orderDetails = $query->orderBy('orders.order_date', 'desc')->paginate(self::REPORTS_PER_PAGE);
        $noData = $orderDetails->isEmpty();
        $availableRange = $noData ? $dateBounds : null;

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
            'dateBounds',
            'topItems',
            'salesByCategory',
            'dailySales'
        ));
    }

    public function salesPdf(Request $request)
    {
        $dateBounds = $this->normalizeDateBounds($this->getOrderDateRangeAll(true));

        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()
                ->with('error', 'The end date cannot be earlier than the start date.')
                ->with('flash_timeout', 3000);
        }

        if ($invalidMessage = $this->getUnavailableDateMessage($request, $dateBounds)) {
            return redirect()->back()
                ->with('error', $invalidMessage)
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
        $itemsQuery = Item::query()
            ->when(!empty($archivedIds), fn ($query) => $query->whereNotIn('item_id', $archivedIds));
        $inventoryItems = (clone $itemsQuery)
            ->orderBy('item_name')
            ->get();
        $items = $itemsQuery
            ->orderBy('item_name')
            ->paginate(self::REPORTS_PER_PAGE)
            ->withQueryString();

        // Categorize items by stock status
        $inStock = $inventoryItems->filter(function($item) { return $item->stock_quantity > 10; });
        $lowStock = $inventoryItems->filter(function($item) { return $item->stock_quantity > 0 && $item->stock_quantity <= 10; });
        $outOfStock = $inventoryItems->filter(function($item) { return $item->stock_quantity == 0; });

        // Prepare data for pie chart
        $stockDistribution = [
            'in_stock' => $inStock->count(),
            'low_stock' => $lowStock->count(),
            'out_of_stock' => $outOfStock->count(),
        ];

        // Calculate total inventory value and quantity
        $totalQuantity = $inventoryItems->sum('stock_quantity');
        $totalValue = $inventoryItems->sum(function($item) { return $item->stock_quantity * $item->price; });
        $averagePrice = $inventoryItems->count() > 0 ? $inventoryItems->avg('price') : 0;

        // Get top items by stock quantity
        $topItems = $inventoryItems->sortByDesc('stock_quantity')->take(10);
        $lowStockItems = $lowStock->sortBy('stock_quantity')->take(10);

        // FIFO visibility (selected item)
        $fifoFilterItems = $inventoryItems->sortBy('item_name')->values();
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

    private function hasInvalidDateRange(Request $request): bool
    {
        return $request->start_date && $request->end_date && $request->end_date < $request->start_date;
    }

    private function getUnavailableDateMessage(Request $request, ?array $dateBounds): ?string
    {
        if (!$dateBounds || !$dateBounds['min'] || !$dateBounds['max']) {
            return null;
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (($startDate && $startDate < $dateBounds['min']) || ($endDate && $endDate > $dateBounds['max'])) {
            return 'Please select dates between '
                . Carbon::parse($dateBounds['min'])->format('M d, Y')
                . ' and '
                . Carbon::parse($dateBounds['max'])->format('M d, Y')
                . '.';
        }

        return null;
    }

    private function normalizeDateBounds($range): ?array
    {
        if (!$range || !$range->min_date || !$range->max_date) {
            return null;
        }

        return [
            'min' => Carbon::parse($range->min_date)->toDateString(),
            'max' => Carbon::parse($range->max_date)->toDateString(),
        ];
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

    private function getOrderDateRangeAll(bool $deliveredOnly = false)
    {
        return Order::query()
            ->when($deliveredOnly, function ($q) {
                $q->where('order_status', 'Delivered');
            })
            ->selectRaw('MIN(order_date) as min_date, MAX(order_date) as max_date')
            ->first();
    }

}
