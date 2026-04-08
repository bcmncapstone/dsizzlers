<?php

namespace App\Http\Controllers\Franchisee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;

class FranchiseeItemController extends Controller
{
    /**
     * Display a list of items with optional category filter and sorting.
     */
    public function index(Request $request)
    {
        $archivedIds = $this->getArchivedItemIds();

        $search = $request->get('search', '');

        // Sort mapping from UI keys to database columns
        $sortMap = [
            'name'     => 'item_name',
            'price'    => 'price',
            'quantity' => 'stock_quantity',
        ];

        $sortBy = $request->get('sort_by', 'name');      // default sort: name
        $sortOrder = $request->get('sort_order', 'asc'); // default order: ascending
        $selectedCategory = $request->get('item_category');

        // Start query
        $query = Item::query();

        if (!empty($archivedIds)) {
            $query->whereNotIn('item_id', $archivedIds);
        }

        // Apply search filter
        if (!empty($search)) {
                        $query->where(function ($q) use ($search) {
                                $q->where('item_name', 'ILIKE', "%$search%")
                                    ->orWhere('item_description', 'ILIKE', "%$search%");
                        });
        }

        // Apply category filter if selected
        if (!empty($selectedCategory)) {
            $query->where('item_category', $selectedCategory);
        }

        // Apply sorting only if valid
        if (array_key_exists($sortBy, $sortMap)) {
            $query->orderBy($sortMap[$sortBy], $sortOrder);
        } else {
            $query->orderBy('item_name', 'asc'); // fallback
        }

        // Get items
        $items = $query->get();

        // Keep create-form categories visible even if no item exists yet.
        $defaultCategories = collect(['food', 'supplies', 'package']);

        // Get all unique categories from items
        $storedCategories = Item::query()
            ->when(!empty($archivedIds), function ($q) use ($archivedIds) {
                $q->whereNotIn('item_id', $archivedIds);
            })
            ->whereNotNull('item_category')
            ->where('item_category', '!=', '')
            ->where('item_category', '!=', 'none')
            ->select('item_category')
            ->distinct()
            ->orderBy('item_category', 'asc')
            ->pluck('item_category');

        $categories = $defaultCategories
            ->merge($storedCategories)
            ->unique()
            ->values();

        return view('franchisee.item.index', compact(
            'categories',
            'items',
            'selectedCategory',
            'sortBy',
            'sortOrder'
        ));
    }

    /**
     * Show a specific item.
     */
    public function show($id)
    {
        $archivedIds = $this->getArchivedItemIds();
        if (in_array((int) $id, $archivedIds, true)) {
            abort(404, 'Item not found.');
        }

        $item = Item::find($id);

        if (!$item) {
            abort(404, 'Item not found.');
        }

        return view('franchisee.item.show', compact('item'));
    }

    protected function getArchivedItemIds(): array
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
}