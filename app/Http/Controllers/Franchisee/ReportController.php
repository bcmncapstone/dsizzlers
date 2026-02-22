<?php

namespace App\Http\Controllers\Franchisee;

use App\Http\Controllers\Controller;
use App\Models\FranchiseeStaff;
use App\Models\Order;
use App\Models\StockTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('franchisee.reports.index');
    }

    public function sales(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()->with('error', 'End date cannot be earlier than start date.');
        }

        $franchisee = Auth::guard('franchisee')->user();

        $query = Order::with(['franchisee', 'orderDetails.item'])
            ->where('franchisee_id', $franchisee->franchisee_id)
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('order_date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('order_date', '<=', $request->end_date);
            });

        $summaryQuery = clone $query;
        $totalSales = $summaryQuery->sum('total_amount');
        $totalOrders = $summaryQuery->count();

        $orders = $query->orderBy('order_date', 'desc')->paginate(50);

        // Compute concatenated item names per order for display
        $orders->getCollection()->transform(function ($order) {
            $order->item_names = $order->orderDetails
                ? $order->orderDetails
                    ->map(function ($detail) {
                        return optional($detail->item)->item_name;
                    })
                    ->filter()
                    ->implode(', ')
                : '';

            return $order;
        });
        $noData = $orders->isEmpty();
        $availableRange = $noData ? $this->getOrderDateRange($franchisee->franchisee_id) : null;

        // Get chart data
        $chartQuery = DB::table('order_details')
            ->join('items', 'order_details.item_id', '=', 'items.item_id')
            ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
            ->where('orders.franchisee_id', $franchisee->franchisee_id)
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

        return view('franchisee.reports.sales', compact(
            'orders',
            'totalSales',
            'totalOrders',
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

        $franchisee = Auth::guard('franchisee')->user();

        $orders = Order::with('franchisee')
            ->where('franchisee_id', $franchisee->franchisee_id)
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('order_date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('order_date', '<=', $request->end_date);
            })
            ->orderBy('order_date', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->back()->with('error', 'No sales data found for the selected filters.');
        }

        $totalSales = $orders->sum('total_amount');
        $totalOrders = $orders->count();

        $pdf = Pdf::loadView('franchisee.reports.pdf.sales', [
            'orders' => $orders,
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'filters' => $request->only(['start_date', 'end_date']),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('sales-report.pdf');
    }

    public function inventory(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()->with('error', 'End date cannot be earlier than start date.');
        }

        $franchisee = Auth::guard('franchisee')->user();

        $query = StockTransaction::with(['franchisee', 'item'])
            ->where('franchisee_id', $franchisee->franchisee_id)
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->end_date);
            });

        $transactions = $query->orderBy('created_at', 'desc')->paginate(50);
        $noData = $transactions->isEmpty();
        $availableRange = $noData ? $this->getStockDateRange($franchisee->franchisee_id) : null;

        // Get franchisee items for stock status summary
        $franchiseeItems = DB::table('franchisee_stock')
            ->join('items', 'franchisee_stock.item_id', '=', 'items.item_id')
            ->where('franchisee_stock.franchisee_id', $franchisee->franchisee_id)
            ->select('franchisee_stock.current_quantity as stock_quantity', 'items.item_name', 'items.item_category', 'items.price')
            ->get();

        // Stock status categorization
        $inStock = $franchiseeItems->filter(fn($item) => $item->stock_quantity > 10);
        $lowStock = $franchiseeItems->filter(fn($item) => $item->stock_quantity > 0 && $item->stock_quantity <= 10);
        $outOfStock = $franchiseeItems->filter(fn($item) => $item->stock_quantity == 0);
        
        $totalQuantity = $franchiseeItems->sum('stock_quantity');
        $totalValue = $franchiseeItems->sum(fn($item) => $item->stock_quantity * $item->price);

        // Top items by stock - convert to array for JSON serialization
        $topItems = $franchiseeItems->sortByDesc('stock_quantity')->take(5)->values()->toArray();

        return view('franchisee.reports.inventory', compact(
            'transactions',
            'noData',
            'availableRange',
            'inStock',
            'lowStock',
            'outOfStock',
            'totalQuantity',
            'totalValue',
            'topItems',
            'franchiseeItems'
        ));
    }

    public function inventoryPdf(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()->with('error', 'End date cannot be earlier than start date.');
        }

        $franchisee = Auth::guard('franchisee')->user();

        $transactions = StockTransaction::with(['franchisee', 'item'])
            ->where('franchisee_id', $franchisee->franchisee_id)
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->end_date);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        if ($transactions->isEmpty()) {
            return redirect()->back()->with('error', 'No inventory data found for the selected filters.');
        }

        $pdf = Pdf::loadView('franchisee.reports.pdf.inventory', [
            'transactions' => $transactions,
            'filters' => $request->only(['start_date', 'end_date']),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('inventory-report.pdf');
    }

    public function staff(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()->with('error', 'End date cannot be earlier than start date.');
        }

        $franchisee = Auth::guard('franchisee')->user();

        $staff = FranchiseeStaff::where('franchisee_id', $franchisee->franchisee_id)
            ->orderBy('fstaff_lname')
            ->orderBy('fstaff_fname')
            ->get();

        $performance = Order::query()
            ->select('fstaff_id', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total_amount) as total_sales'))
            ->where('franchisee_id', $franchisee->franchisee_id)
            ->whereNotNull('fstaff_id')
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('order_date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('order_date', '<=', $request->end_date);
            })
            ->groupBy('fstaff_id')
            ->get()
            ->keyBy('fstaff_id');

        $noData = $staff->isEmpty();
        $noPerformanceData = $performance->isEmpty() && ($request->start_date || $request->end_date);
        $availableRange = $this->getOrderDateRange($franchisee->franchisee_id);

        // Prepare chart data for staff performance
        $staffChartData = [];
        foreach ($staff as $member) {
            $perf = $performance[$member->fstaff_id] ?? null;
            $staffChartData[] = [
                'name' => $member->fstaff_fname . ' ' . $member->fstaff_lname,
                'orders' => $perf->orders_count ?? 0,
                'sales' => $perf->total_sales ?? 0
            ];
        }
        
        // Sort by sales descending and take top 5 for charts
        $topStaffBySales = collect($staffChartData)
            ->sortByDesc('sales')
            ->take(5)
            ->values()
            ->toArray();

        return view('franchisee.reports.staff', compact(
            'staff',
            'performance',
            'noData',
            'noPerformanceData',
            'availableRange',
            'topStaffBySales',
            'staffChartData'
        ));
    }

    public function staffPdf(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()->with('error', 'End date cannot be earlier than start date.');
        }

        $franchisee = Auth::guard('franchisee')->user();

        $staff = FranchiseeStaff::where('franchisee_id', $franchisee->franchisee_id)
            ->orderBy('fstaff_lname')
            ->orderBy('fstaff_fname')
            ->get();

        if ($staff->isEmpty()) {
            return redirect()->back()->with('error', 'No staff data found for this branch.');
        }

        $performance = Order::query()
            ->select('fstaff_id', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total_amount) as total_sales'))
            ->where('franchisee_id', $franchisee->franchisee_id)
            ->whereNotNull('fstaff_id')
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('order_date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('order_date', '<=', $request->end_date);
            })
            ->groupBy('fstaff_id')
            ->get()
            ->keyBy('fstaff_id');

        $pdf = Pdf::loadView('franchisee.reports.pdf.staff', [
            'staff' => $staff,
            'performance' => $performance,
            'filters' => $request->only(['start_date', 'end_date']),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('staff-report.pdf');
    }

    private function hasInvalidDateRange(Request $request): bool
    {
        return $request->start_date && $request->end_date && $request->end_date < $request->start_date;
    }

    private function getOrderDateRange(int $franchiseeId)
    {
        return Order::query()
            ->where('franchisee_id', $franchiseeId)
            ->selectRaw('MIN(order_date) as min_date, MAX(order_date) as max_date')
            ->first();
    }

    private function getStockDateRange(int $franchiseeId)
    {
        return StockTransaction::query()
            ->where('franchisee_id', $franchiseeId)
            ->selectRaw('MIN(created_at) as min_date, MAX(created_at) as max_date')
            ->first();
    }
}
