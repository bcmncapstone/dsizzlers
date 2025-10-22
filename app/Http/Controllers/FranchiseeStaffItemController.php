<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;

class FranchiseeStaffItemController extends Controller
{
    public function index(Request $request)
    {
        $sortMap = [
            'name'     => 'item_name',
            'price'    => 'price',
            'quantity' => 'stock_quantity',
        ];

        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $selectedCategory = $request->get('item_category');

        $query = Item::query();

        if (!empty($selectedCategory)) {
            $query->where('item_category', $selectedCategory);
        }

        if (array_key_exists($sortBy, $sortMap)) {
            $query->orderBy($sortMap[$sortBy], $sortOrder);
        }

        $items = $query->get();
        $categories = Item::select('item_category')->distinct()->orderBy('item_category')->pluck('item_category');

        return view('franchisee-staff.item.index', compact(
            'categories',
            'items',
            'selectedCategory',
            'sortBy',
            'sortOrder'
        ));
    }

    public function show($id)
    {
        $item = Item::find($id);

        if (!$item) {
            abort(404, 'Item not found.');
        }

        return view('franchisee-staff.item.show', compact('item'));
    }
}
