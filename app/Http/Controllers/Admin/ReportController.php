<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Franchisee;
use App\Models\Order;
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

        $franchisees = Franchisee::orderBy('franchisee_name')->get();

        $query = Order::with('franchisee')
            ->when($request->franchisee_id, function ($q) use ($request) {
                $q->where('franchisee_id', $request->franchisee_id);
            })
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
        $noData = $orders->isEmpty();
        $availableRange = $noData ? $this->getOrderDateRange($request->franchisee_id) : null;

        return view('admin.reports.sales', compact(
            'franchisees',
            'orders',
            'totalSales',
            'totalOrders',
            'noData',
            'availableRange'
        ));
    }

    public function salesPdf(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()->with('error', 'End date cannot be earlier than start date.');
        }

        $query = Order::with('franchisee')
            ->when($request->franchisee_id, function ($q) use ($request) {
                $q->where('franchisee_id', $request->franchisee_id);
            })
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('order_date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('order_date', '<=', $request->end_date);
            });

        $orders = $query->orderBy('order_date', 'desc')->get();

        if ($orders->isEmpty()) {
            return redirect()->back()->with('error', 'No sales data found for the selected filters.');
        }

        $totalSales = $orders->sum('total_amount');
        $totalOrders = $orders->count();

        $pdf = Pdf::loadView('admin.reports.pdf.sales', [
            'orders' => $orders,
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'filters' => $request->only(['franchisee_id', 'start_date', 'end_date']),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('sales-report.pdf');
    }

    public function inventory(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()->with('error', 'End date cannot be earlier than start date.');
        }

        $franchisees = Franchisee::orderBy('franchisee_name')->get();

        $query = StockTransaction::with(['franchisee', 'item'])
            ->when($request->franchisee_id, function ($q) use ($request) {
                $q->where('franchisee_id', $request->franchisee_id);
            })
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->end_date);
            });

        $transactions = $query->orderBy('created_at', 'desc')->paginate(50);
        $noData = $transactions->isEmpty();
        $availableRange = $noData ? $this->getStockDateRange($request->franchisee_id) : null;

        return view('admin.reports.inventory', compact(
            'franchisees',
            'transactions',
            'noData',
            'availableRange'
        ));
    }

    public function inventoryPdf(Request $request)
    {
        if ($this->hasInvalidDateRange($request)) {
            return redirect()->back()->with('error', 'End date cannot be earlier than start date.');
        }

        $query = StockTransaction::with(['franchisee', 'item'])
            ->when($request->franchisee_id, function ($q) use ($request) {
                $q->where('franchisee_id', $request->franchisee_id);
            })
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->end_date);
            });

        $transactions = $query->orderBy('created_at', 'desc')->get();

        if ($transactions->isEmpty()) {
            return redirect()->back()->with('error', 'No inventory data found for the selected filters.');
        }

        $pdf = Pdf::loadView('admin.reports.pdf.inventory', [
            'transactions' => $transactions,
            'filters' => $request->only(['franchisee_id', 'start_date', 'end_date']),
        ])->setPaper('A4', 'portrait');

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

        return view('admin.reports.franchisee-sales', compact(
            'franchisees',
            'rows',
            'noData',
            'availableRange',
            'franchiseeMap'
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

        $pdf = Pdf::loadView('admin.reports.pdf.franchisee-sales', [
            'rows' => $rows,
            'franchisees' => $franchisees,
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
