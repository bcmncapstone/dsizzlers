<?php

namespace App\Http\Controllers\Franchisee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;

class FranchiseeItemController extends Controller
{
    /**
     * Display a list of items with optional category filter and sorting.
     */
    public function index(Request $request)
    {
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

        // Get all unique categories
        $categories = Item::select('item_category')
            ->distinct()
            ->orderBy('item_category', 'asc')
            ->pluck('item_category');

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
        $item = Item::find($id);

        if (!$item) {
            abort(404, 'Item not found.');
        }

        return view('franchisee.item.show', compact('item'));
    }
}
