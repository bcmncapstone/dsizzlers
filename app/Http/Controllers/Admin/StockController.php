<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FranchiseeStock;
use App\Models\StockTransaction;
use App\Models\Franchisee;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StockController extends Controller
{
    /**
     * Display all items in stock management (master catalog)
     */
    public function index(Request $request)
    {
        // Exclude archived items (same JSON file used by ItemController)
        $archivedIds = [];
        if (Storage::disk('local')->exists('archived_items.json')) {
            $raw = Storage::disk('local')->get('archived_items.json');
            $decoded = json_decode($raw, true);
            $archivedIds = is_array($decoded) ? $decoded : [];
        }

        $items = Item::orderBy('item_name')
            ->when(!empty($archivedIds), fn($q) => $q->whereNotIn('item_id', $archivedIds))
            ->get();

        return view('admin.stock.index', compact('items'));
    }
    
    /**
     * Display franchisee inventory summary
     */
    public function franchiseeInventory(Request $request)
    {
        // Get all franchisees for filter dropdown
        $franchisees = Franchisee::orderBy('franchisee_name')->get();

        $query = FranchiseeStock::with(['franchisee', 'item']);

        // Filter by franchisee if selected
        if ($request->has('franchisee_id') && $request->franchisee_id) {
            $query->where('franchisee_id', $request->franchisee_id);
        }

        $stocks = $query->get();

        // Group by franchisee for summary
        $franchiseeSummaries = $stocks->groupBy('franchisee_id')->map(function ($items) {
            $franchisee = $items->first()->franchisee;
            return [
                'franchisee' => $franchisee,
                'total_items' => $items->count(),
                'in_stock' => $items->where('current_quantity', '>', 0)->count(),
                'low_stock' => $items->filter(function($s) { 
                    return $s->current_quantity > 0 && $s->current_quantity <= $s->minimum_quantity; 
                })->count(),
                'out_of_stock' => $items->filter(function($s) { 
                    return $s->current_quantity <= 0; 
                })->count(),
            ];
        });

        // Overall statistics
        $totalFranchisees = $franchiseeSummaries->count();
        $totalLowStockItems = $stocks->filter(function($s) { 
            return $s->current_quantity > 0 && $s->current_quantity <= $s->minimum_quantity; 
        })->count();
        $totalOutOfStockItems = $stocks->filter(function($s) { 
            return $s->current_quantity <= 0; 
        })->count();

        return view('admin.stock.franchisee-inventory', compact(
            'franchisees',
            'stocks',
            'franchiseeSummaries',
            'totalFranchisees',
            'totalLowStockItems',
            'totalOutOfStockItems'
        ));
    }

    /**
     * Show detailed stock for a specific franchisee
     */
    public function show($franchiseeId, Request $request)
    {
        $franchisee = Franchisee::findOrFail($franchiseeId);
        
        $stocks = FranchiseeStock::with('item')
            ->where('franchisee_id', $franchiseeId)
            ->get();

        // Statistics for this franchisee
        $totalItems = $stocks->count();
        $inStock = $stocks->where('current_quantity', '>', 0)->count();
        $lowStock = $stocks->filter(function($s) { 
            return $s->current_quantity > 0 && $s->current_quantity <= $s->minimum_quantity; 
        })->count();
        $outOfStock = $stocks->filter(function($s) { 
            return $s->current_quantity <= 0; 
        })->count();

        // Highlight low stock items
        $lowStockItems = $stocks->filter(function($s) { 
            return $s->current_quantity > 0 && $s->current_quantity <= $s->minimum_quantity; 
        })->values();

        return view('admin.stock.show', compact(
            'franchisee',
            'stocks',
            'totalItems',
            'inStock',
            'lowStock',
            'outOfStock',
            'lowStockItems'
        ));
    }

    /**
     * Show inventory reports with date filter
     */
    public function reports(Request $request)
    {
        $franchisees = Franchisee::orderBy('franchisee_name')->get();

        $query = StockTransaction::with(['franchisee', 'item']);

        // Filter by franchisee
        if ($request->has('franchisee_id') && $request->franchisee_id) {
            $query->where('franchisee_id', $request->franchisee_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Validate date range
        if ($request->has('start_date') && $request->has('end_date') && 
            $request->start_date && $request->end_date &&
            $request->end_date < $request->start_date) {
            return redirect()->back()->with('error', 'End date cannot be earlier than start date.');
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(50);

        // Check if no data available
        $noData = $transactions->isEmpty();

        return view('admin.stock.reports', compact(
            'franchisees',
            'transactions',
            'noData'
        ));
    }
}
