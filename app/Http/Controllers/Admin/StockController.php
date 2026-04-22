<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FranchiseeStock;
use App\Models\StockTransaction;
use App\Models\StockIn;
use App\Models\Franchisee;
use App\Models\Item;
use App\Services\FifoStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StockController extends Controller
{
    public function __construct(private FifoStockService $fifoStockService)
    {
    }

    /**
     * Display all items in stock management (master catalog)
     */
    public function index(Request $request)
    {
        $perPage = 10;

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

        $itemsQuery = Item::query()
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
            ->orderBy('item_name');

        $totalItems = (clone $itemsQuery)->count();
        $inStockCount = (clone $itemsQuery)->where('stock_quantity', '>', 10)->count();
        $lowStockCount = (clone $itemsQuery)->whereBetween('stock_quantity', [1, 10])->count();
        $outOfStockCount = (clone $itemsQuery)->where('stock_quantity', '<=', 0)->count();

        $items = $itemsQuery
            ->paginate($perPage)
            ->withQueryString();

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

        // Build FIFO lot snapshots keyed by item_id for inline display
        $fifoSnapshots = [];
        foreach ($items as $item) {
            /** @var Item $item */
            $fifoSnapshots[(int) $item->item_id] = $this->fifoStockService->getRemainingLots($item);
        }

        return view('admin.stock.index', compact(
            'items',
            'categories',
            'search',
            'selectedCategory',
            'stockStatus',
            'totalItems',
            'inStockCount',
            'lowStockCount',
            'outOfStockCount',
            'fifoSnapshots'
        ));
    }

    /**
     * Show the form to update a catalog item's stock quantity.
     */
    public function edit($itemId)
    {
        $item = Item::findOrFail($itemId);

        return view('admin.stock.edit', compact('item'));
    }

    /**
     * Update a catalog item's stock quantity.
     */
    public function update(Request $request, $itemId)
    {
        $request->validate([
            'new_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $item = Item::findOrFail($itemId);
        $oldQuantity = (int) $item->stock_quantity;
        $newQuantity = (int) $request->input('new_quantity');

        DB::beginTransaction();
        try {
            $item->stock_quantity = $newQuantity;
            $item->save();

            if ($newQuantity > $oldQuantity) {
                $source = trim((string) $request->input('notes', ''));
                if ($source === '') {
                    $source = 'Admin adjustment';
                }

                StockIn::create([
                    'item_id' => $item->item_id,
                    'quantity_received' => $newQuantity - $oldQuantity,
                    'received_date' => now(),
                    'supplier_name' => mb_substr($source, 0, 50),
                    'restocked_by' => auth('admin')->id() ?? 0,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.stock.index')
                ->with('success', 'Item quantity updated successfully.')
                ->with('flash_timeout', 3000);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Failed to update item: ' . $e->getMessage())
                ->with('flash_timeout', 3000);
        }
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

        if ($validated['direction'] === 'add' && $adjustBy > 0) {
            $restockedBy = auth('admin')->id() ?? 0;

            StockIn::create([
                'item_id' => $item->item_id,
                'quantity_received' => $adjustBy,
                'received_date' => now(),
                'supplier_name' => 'Admin adjustment',
                'restocked_by' => $restockedBy,
            ]);
        }

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
        $perPage = 10;
        $franchisee = Franchisee::findOrFail($franchiseeId);

        $stocksQuery = FranchiseeStock::with('item')
            ->where('franchisee_id', $franchiseeId);

        $totalItems = (clone $stocksQuery)->count();
        $inStock = (clone $stocksQuery)->where('current_quantity', '>', 0)->count();
        $lowStock = (clone $stocksQuery)
            ->where('current_quantity', '>', 0)
            ->whereColumn('current_quantity', '<=', 'minimum_quantity')
            ->count();
        $outOfStock = (clone $stocksQuery)->where('current_quantity', '<=', 0)->count();

        $lowStockItems = (clone $stocksQuery)
            ->where('current_quantity', '>', 0)
            ->whereColumn('current_quantity', '<=', 'minimum_quantity')
            ->get()
            ->values();

        $stocks = $stocksQuery
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->withQueryString();

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

        $transactions = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        // Check if no data available
        $noData = $transactions->isEmpty();

        return view('admin.stock.reports', compact(
            'franchisees',
            'transactions',
            'noData'
        ));
    }
}
