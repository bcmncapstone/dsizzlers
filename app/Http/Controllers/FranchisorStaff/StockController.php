<?php

namespace App\Http\Controllers\FranchisorStaff;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockIn;
use App\Services\FifoStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    private FifoStockService $fifoStockService;

    public function __construct(FifoStockService $fifoStockService)
    {
        $this->fifoStockService = $fifoStockService;
    }
    /**
     * Display items catalog
     */
    public function index(Request $request)
    {

        // Exclude archived items (same as admin)
        $archivedIds = [];
        if (\Storage::disk('local')->exists('archived_items.json')) {
            $raw = \Storage::disk('local')->get('archived_items.json');
            $decoded = json_decode($raw, true);
            $archivedIds = is_array($decoded) ? $decoded : [];
        }

        $search = trim((string) $request->get('search', ''));
        $selectedCategory = trim((string) $request->get('category', ''));
        $stockStatus = $request->get('stock_status', 'all');

        $items = Item::with(['stockIns' => function($q) {
                $q->orderBy('received_date', 'desc');
            }])
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
        // Force hydration if stdClass is returned
        if ($items->isNotEmpty() && get_class($items->first()) === 'stdClass') {
            $items = Item::hydrate($items->toArray());
        }

        $storedCategories = Item::query()
            ->when(!empty($archivedIds), fn($q) => $q->whereNotIn('item_id', $archivedIds))
            ->whereNotNull('item_category')
            ->where('item_category', '!=', '')
            ->where('item_category', '!=', 'none')
            ->select('item_category')
            ->distinct()
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

        // Build FIFO lot snapshots keyed by item_id for inline display (use FifoStockService for accuracy)
        $fifoSnapshots = [];
        foreach ($items as $item) {
            $fifoSnapshots[(int) $item->item_id] = $this->fifoStockService->getRemainingLots($item);
        }

        return view('franchisor-staff.stock.index', compact(
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
     * Show the form to update item
     */
    public function edit($itemId)
    {
        $item = Item::findOrFail($itemId);

        return view('franchisor-staff.stock.edit', compact('item'));
    }

    /**
     * Update item quantity
     */
    public function update(Request $request, $itemId)
    {
        $request->validate([
            'new_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        $item = Item::findOrFail($itemId);
        $oldQuantity = $item->stock_quantity;
        $newQuantity = $request->new_quantity;

        DB::beginTransaction();
        try {
            // Update item quantity
            $item->stock_quantity = $newQuantity;
            $item->save();

            if ($newQuantity > $oldQuantity) {
                $source = trim((string) $request->input('notes', ''));
                if ($source === '') {
                    $source = 'Franchisor staff adjustment';
                }

                StockIn::create([
                    'item_id' => $item->item_id,
                    'quantity_received' => (int) $newQuantity - (int) $oldQuantity,
                    'received_date' => now(),
                    'supplier_name' => mb_substr($source, 0, 50),
                    'restocked_by' => Auth::guard('franchisor_staff')->id() ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('franchisor-staff.stock.index')
                ->with('success', 'Item quantity updated successfully.')
                ->with('flash_timeout', 3000);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update item: ' . $e->getMessage())
                ->with('flash_timeout', 3000);
        }
    }

    /**
     * Adjust item quantity inline (+/-)
     */
    public function adjustQuantity(Request $request, $itemId)
    {
        $validated = $request->validate([
            'adjust_by' => 'required|integer|min:1|max:100000',
            'direction' => 'required|in:add,deduct',
            'notes' => 'nullable|string|max:255',
        ]);

        $item = Item::findOrFail($itemId);

        $adjustBy = (int) $validated['adjust_by'];
        $newQuantity = $validated['direction'] === 'add'
            ? (int) $item->stock_quantity + $adjustBy
            : (int) $item->stock_quantity - $adjustBy;

        if ($newQuantity < 0) {
            return redirect()->back()
                ->with('error', 'Deduction is too high. Stock quantity cannot go below zero.')
                ->with('flash_timeout', 3000);
        }

        DB::beginTransaction();
        try {
            $item->stock_quantity = $newQuantity;
            $item->save();

            if ($validated['direction'] === 'add' && $adjustBy > 0) {
                StockIn::create([
                    'item_id' => $item->item_id,
                    'quantity_received' => $adjustBy,
                    'received_date' => now(),
                    'supplier_name' => $validated['notes'] ?? 'Franchisor staff adjustment',
                    'restocked_by' => Auth::guard('franchisor_staff')->id() ?? 0,
                ]);
            }

            DB::commit();

            $query = $request->only(['search', 'category', 'stock_status']);

            return redirect()
                ->route('franchisor-staff.stock.index', $query)
                ->with('success', 'Stock updated for ' . $item->item_name . '. New quantity: ' . $newQuantity . '.')
                ->with('flash_timeout', 3000);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to adjust stock: ' . $e->getMessage())
                ->with('flash_timeout', 3000);
        }
    }

    /**
     * Cancel/reverse adjustment
     */
    public function cancel($itemId)
    {
        return redirect()->route('franchisor-staff.stock.index')
            ->with('info', 'Item update cancelled.')
            ->with('flash_timeout', 3000);
    }
}
