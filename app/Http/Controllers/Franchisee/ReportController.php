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
            return redirect()->back()
            ->with('flash_timeout', 3000);
        }

        $franchisee = Auth::guard('franchisee')->user();

        $query = StockTransaction::query()
            ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
            ->where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
            ->where(function ($q) {
                // Sales are counted only when stock is manually decreased.
                $q->where(function ($manualAdjustments) {
                    $manualAdjustments->where('stock_transactions.transaction_type', 'adjustment')
                        ->where('stock_transactions.quantity', '<', 0);
                })->orWhere(function ($staffOutflow) {
                    $staffOutflow->where('stock_transactions.transaction_type', 'out')
                        ->where('stock_transactions.performed_by_type', 'franchisee_staff');
                });
            })
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('stock_transactions.created_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('stock_transactions.created_at', '<=', $request->end_date);
            })
            ->select(
                'stock_transactions.transaction_id',
                'stock_transactions.quantity',
                'stock_transactions.created_at',
                'stock_transactions.performed_by_type',
                'stock_transactions.notes',
                'items.item_name',
                'items.item_category',
                'items.price'
            );

        $summaryQuery = clone $query;
        $totalSales = $summaryQuery->get()->sum(function ($row) {
            $quantitySold = $this->normalizeSoldQuantity($row->quantity, $row->performed_by_type);

            return $quantitySold * (float) $row->price;
        });
        $totalOrders = $summaryQuery->count();

        $salesEntries = $query->orderBy('stock_transactions.created_at', 'desc')->paginate(50);

        $salesEntries->getCollection()->transform(function ($entry) {
            $entry->quantity_sold = $this->normalizeSoldQuantity($entry->quantity, $entry->performed_by_type);
            $entry->line_total = $entry->quantity_sold * (float) $entry->price;
            $entry->decreased_by = $entry->performed_by_type === 'franchisee_staff' ? 'Franchisee Staff' : 'Franchisee';

            return $entry;
        });
        $noData = $salesEntries->isEmpty();
        $availableRange = $noData ? $this->getManualSalesDateRange($franchisee->franchisee_id) : null;

        // Get chart data using separate method
        $chartQuery = $this->getChartQueryData($franchisee->franchisee_id, $request);
        $topItems = $this->getTopItems($chartQuery);
        $salesByCategory = $this->getSalesByCategory($chartQuery);
        $dailySales = $this->getDailySales($chartQuery);

        return view('franchisee.reports.sales', compact(
            'salesEntries',
            'totalSales',
            'totalOrders',
            'noData',
            'availableRange',
            'topItems',
            'salesByCategory',
            'dailySales'
        ));
    }

    private function getChartQueryData(int $franchiseeId, Request $request)
    {
        return DB::table('stock_transactions')
            ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
            ->where('stock_transactions.franchisee_id', $franchiseeId)
            ->where(function ($q) {
                $q->where(function ($manualAdjustments) {
                    $manualAdjustments->where('stock_transactions.transaction_type', 'adjustment')
                        ->where('stock_transactions.quantity', '<', 0);
                })->orWhere(function ($staffOutflow) {
                    $staffOutflow->where('stock_transactions.transaction_type', 'out')
                        ->where('stock_transactions.performed_by_type', 'franchisee_staff');
                });
            })
            ->select(
                'stock_transactions.item_id',
                'items.item_name',
                'items.item_category',
                'stock_transactions.quantity',
                'stock_transactions.performed_by_type',
                'items.price',
                'stock_transactions.created_at'
            )
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('stock_transactions.created_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('stock_transactions.created_at', '<=', $request->end_date);
            })
            ->get();
    }

    private function getTopItems($chartQuery)
    {
        return collect($chartQuery)
            ->groupBy('item_name')
            ->map(function($group) {
                $quantitySold = $group->sum(function ($item) {
                    return $this->normalizeSoldQuantity($item->quantity, $item->performed_by_type);
                });

                return [
                    'name' => $group->first()->item_name,
                    'quantity' => $quantitySold,
                    'sales' => $group->sum(function ($item) {
                        return $this->normalizeSoldQuantity($item->quantity, $item->performed_by_type) * (float) $item->price;
                    })
                ];
            })
            ->sortByDesc('quantity')
            ->take(10)
            ->values()
            ->toArray();
    }

    private function getSalesByCategory($chartQuery)
    {
        return collect($chartQuery)
            ->groupBy('item_category')
            ->map(function($group) {
                $quantitySold = $group->sum(function ($item) {
                    return $this->normalizeSoldQuantity($item->quantity, $item->performed_by_type);
                });

                return [
                    'category' => $group->first()->item_category ?? 'Uncategorized',
                    'quantity' => $quantitySold,
                    'sales' => $group->sum(function ($item) {
                        return $this->normalizeSoldQuantity($item->quantity, $item->performed_by_type) * (float) $item->price;
                    })
                ];
            })
            ->sortByDesc('sales')
            ->values()
            ->toArray();
    }

    private function getDailySales($chartQuery)
    {
        return collect($chartQuery)
            ->groupBy(function($item) {
                return \Carbon\Carbon::parse($item->created_at)->format('Y-m-d');
            })
            ->map(function($group) {
                $quantitySold = $group->sum(function ($item) {
                    return $this->normalizeSoldQuantity($item->quantity, $item->performed_by_type);
                });

                return [
                    'date' => $group->first()->created_at,
                    'sales' => $group->sum(function ($item) {
                        return $this->normalizeSoldQuantity($item->quantity, $item->performed_by_type) * (float) $item->price;
                    }),
                    'quantity' => $quantitySold
                ];
            })
            ->sortBy('date')
            ->values()
            ->toArray();
    }

    public function salesPdf(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()
            ->with('flash_timeout', 3000);
        }

        $franchisee = Auth::guard('franchisee')->user();

        $salesEntries = StockTransaction::query()
            ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
            ->where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
            ->where(function ($q) {
                $q->where(function ($manualAdjustments) {
                    $manualAdjustments->where('stock_transactions.transaction_type', 'adjustment')
                        ->where('stock_transactions.quantity', '<', 0);
                })->orWhere(function ($staffOutflow) {
                    $staffOutflow->where('stock_transactions.transaction_type', 'out')
                        ->where('stock_transactions.performed_by_type', 'franchisee_staff');
                });
            })
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('stock_transactions.created_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('stock_transactions.created_at', '<=', $request->end_date);
            })
            ->select(
                'stock_transactions.transaction_id',
                'stock_transactions.quantity',
                'stock_transactions.created_at',
                'stock_transactions.performed_by_type',
                'items.item_name',
                'items.price'
            )
            ->orderBy('stock_transactions.created_at', 'desc')
            ->get();

        if ($salesEntries->isEmpty()) {
            return redirect()->back()
            ->with('flash_timeout', 3000);
        }

        $salesEntries->transform(function ($entry) {
            $entry->quantity_sold = $this->normalizeSoldQuantity($entry->quantity, $entry->performed_by_type);
            $entry->line_total = $entry->quantity_sold * (float) $entry->price;
            $entry->decreased_by = $entry->performed_by_type === 'franchisee_staff' ? 'Franchisee Staff' : 'Franchisee';

            return $entry;
        });

        $totalSales = $salesEntries->sum('line_total');
        $totalOrders = $salesEntries->count();

        $pdf = Pdf::loadView('franchisee.reports.pdf.sales', [
            'salesEntries' => $salesEntries,
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'filters' => $request->only(['start_date', 'end_date']),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('sales-report.pdf');
    }

    private function normalizeSoldQuantity(int $quantity, ?string $performedByType = null): int
    {
        if ($performedByType === 'franchisee_staff') {
            return abs($quantity);
        }

        return $quantity < 0 ? abs($quantity) : 0;
    }

    public function inventory(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()
            ->with('flash_timeout', 3000);
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
            return redirect()->back()
            ->with('flash_timeout', 3000);
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
            return redirect()->back()
            ->with('flash_timeout', 3000);
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
            return redirect()->back()
            ->with('flash_timeout', 3000);
        }

        $franchisee = Auth::guard('franchisee')->user();

        $staff = FranchiseeStaff::where('franchisee_id', $franchisee->franchisee_id)
            ->orderBy('fstaff_lname')
            ->orderBy('fstaff_fname')
            ->get();

        $performance = StockTransaction::query()
            ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
            ->where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
            ->where('stock_transactions.transaction_type', 'out')
            ->where('stock_transactions.performed_by_type', 'franchisee_staff')
            ->whereNotNull('stock_transactions.performed_by_id')
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('stock_transactions.created_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('stock_transactions.created_at', '<=', $request->end_date);
            })
            ->select(
                'stock_transactions.performed_by_id as fstaff_id',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('COALESCE(SUM(ABS(stock_transactions.quantity) * items.price), 0) as total_sales')
            )
            ->groupBy('stock_transactions.performed_by_id')
            ->get()
            ->keyBy('fstaff_id');

        $noData = $staff->isEmpty();
        $noPerformanceData = $performance->isEmpty() && ($request->start_date || $request->end_date);
        $availableRange = $this->getStaffSalesDateRange($franchisee->franchisee_id);

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
            return redirect()->back()
            ->with('flash_timeout', 3000);
        }

        $franchisee = Auth::guard('franchisee')->user();

        $staff = FranchiseeStaff::where('franchisee_id', $franchisee->franchisee_id)
            ->orderBy('fstaff_lname')
            ->orderBy('fstaff_fname')
            ->get();

        if ($staff->isEmpty()) {
            return redirect()->back()
            ->with('flash_timeout', 3000);
        }

        $performance = StockTransaction::query()
            ->join('items', 'stock_transactions.item_id', '=', 'items.item_id')
            ->where('stock_transactions.franchisee_id', $franchisee->franchisee_id)
            ->where('stock_transactions.transaction_type', 'out')
            ->where('stock_transactions.performed_by_type', 'franchisee_staff')
            ->whereNotNull('stock_transactions.performed_by_id')
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('stock_transactions.created_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('stock_transactions.created_at', '<=', $request->end_date);
            })
            ->select(
                'stock_transactions.performed_by_id as fstaff_id',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('COALESCE(SUM(ABS(stock_transactions.quantity) * items.price), 0) as total_sales')
            )
            ->groupBy('stock_transactions.performed_by_id')
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

    private function getManualSalesDateRange(int $franchiseeId)
    {
        return StockTransaction::query()
            ->where('franchisee_id', $franchiseeId)
            ->where(function ($q) {
                $q->where(function ($manualAdjustments) {
                    $manualAdjustments->where('transaction_type', 'adjustment')
                        ->where('quantity', '<', 0);
                })->orWhere(function ($staffOutflow) {
                    $staffOutflow->where('transaction_type', 'out')
                        ->where('performed_by_type', 'franchisee_staff');
                });
            })
            ->selectRaw('MIN(created_at) as min_date, MAX(created_at) as max_date')
            ->first();
    }

    private function getStockDateRange(int $franchiseeId)
    {
        return StockTransaction::query()
            ->where('franchisee_id', $franchiseeId)
            ->selectRaw('MIN(created_at) as min_date, MAX(created_at) as max_date')
            ->first();
    }

    private function getStaffSalesDateRange(int $franchiseeId)
    {
        return StockTransaction::query()
            ->where('franchisee_id', $franchiseeId)
            ->where('transaction_type', 'out')
            ->where('performed_by_type', 'franchisee_staff')
            ->whereNotNull('performed_by_id')
            ->selectRaw('MIN(created_at) as min_date, MAX(created_at) as max_date')
            ->first();
    }
}
