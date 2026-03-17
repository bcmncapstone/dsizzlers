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

        $search = trim((string) $request->get('search', ''));
        $selectedCategory = trim((string) $request->get('category', ''));
        $stockStatus = $request->get('stock_status', 'all');

        $items = Item::query()
            ->when(!empty($archivedIds), fn($q) => $q->whereNotIn('item_id', $archivedIds))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('item_name', 'ILIKE', "%{$search}%")
                        ->orWhere('item_description', 'ILIKE', "%{$search}%");
                });
            })
            ->when($selectedCategory !== '' && $selectedCategory !== 'all', fn($q) => $q->where('item_category', $selectedCategory))
            ->when($stockStatus === 'in_stock', fn($q) => $q->where('stock_quantity', '>', 10))
            ->when($stockStatus === 'low_stock', fn($q) => $q->whereBetween('stock_quantity', [1, 10]))
            ->when($stockStatus === 'out_of_stock', fn($q) => $q->where('stock_quantity', '<=', 0))
            ->orderBy('item_name')
            ->get();

        $storedCategories = Item::query()
            ->when(!empty($archivedIds), fn($q) => $q->whereNotIn('item_id', $archivedIds))
            ->whereNotNull('item_category')
            ->where('item_category', '!=', '')
            ->where('item_category', '!=', 'none')
            ->select('item_category')
            ->distinct()
            ->orderBy('item_category')
            ->pluck('item_category');

        $categories = collect(['food', 'supplies', 'package'])
            ->merge($storedCategories)
            ->unique()
            ->values();

        $totalItems = $items->count();
        $inStockCount = $items->where('stock_quantity', '>', 10)->count();
        $lowStockCount = $items->filter(function ($item) {
            return $item->stock_quantity > 0 && $item->stock_quantity <= 10;
        })->count();
        $outOfStockCount = $items->where('stock_quantity', '<=', 0)->count();

        return view('admin.stock.index', compact(
            'items',
            'categories',
            'search',
            'selectedCategory',
            'stockStatus',
            'totalItems',
            'inStockCount',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    /**
     * Adjust stock quantity for a catalog item.
     */
    public function adjustQuantity(Request $request, $itemId)
    {
        $validated = $request->validate([
            'adjust_by' => 'required|integer|min:1|max:100000',
            'direction' => 'required|in:add,deduct',
        ], [
            'adjust_by.required' => 'Please enter an adjustment quantity.',
            'adjust_by.integer' => 'Adjustment quantity must be a whole number.',
            'adjust_by.min' => 'Adjustment quantity must be at least 1.',
            'direction.required' => 'Please choose an adjustment action.',
            'direction.in' => 'Invalid stock adjustment action.',
        ]);

        $item = Item::findOrFail($itemId);

        $adjustBy = (int) $validated['adjust_by'];
        $newQuantity = $validated['direction'] === 'add'
            ? (int) $item->stock_quantity + $adjustBy
            : (int) $item->stock_quantity - $adjustBy;

        if ($newQuantity < 0) {
            return redirect()
                ->back()
                ->with('error', 'Deduction is too high. Stock quantity cannot go below zero.')
                ->with('flash_timeout', 3000);
        }

        $item->stock_quantity = $newQuantity;
        $item->save();

        $query = $request->only(['search', 'category', 'stock_status']);

        return redirect()
            ->route('admin.stock.index', $query)
            ->with('success', 'Stock updated for ' . $item->item_name . '. New quantity: ' . $newQuantity . '.')
            ->with('flash_timeout', 3000);
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
            return redirect()->back()
                ->with('error', 'End date cannot be earlier than start date.')
                ->with('flash_timeout', 3000);
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
