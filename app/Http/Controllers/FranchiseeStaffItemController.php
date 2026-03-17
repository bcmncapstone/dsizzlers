<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;

class FranchiseeStaffItemController extends Controller
{
    public function index(Request $request)
    {
        $archivedIds = $this->getArchivedItemIds();

        $sortMap = [
            'name'     => 'item_name',
            'price'    => 'price',
            'quantity' => 'stock_quantity',
        ];

        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $selectedCategory = $request->get('item_category');

        $query = Item::query();

        if (!empty($archivedIds)) {
            $query->whereNotIn('item_id', $archivedIds);
        }

        if (!empty($selectedCategory)) {
            $query->where('item_category', $selectedCategory);
        }

        if (array_key_exists($sortBy, $sortMap)) {
            $query->orderBy($sortMap[$sortBy], $sortOrder);
        }

        $items = $query->get();
        // Keep create-form categories visible even if no item exists yet.
        $defaultCategories = collect(['food', 'supplies', 'package']);

        $storedCategories = Item::query()
            ->when(!empty($archivedIds), function ($q) use ($archivedIds) {
                $q->whereNotIn('item_id', $archivedIds);
            })
            ->whereNotNull('item_category')
            ->where('item_category', '!=', '')
            ->where('item_category', '!=', 'none')
            ->select('item_category')
            ->distinct()
            ->orderBy('item_category')
            ->pluck('item_category');

        $categories = $defaultCategories
            ->merge($storedCategories)
            ->unique()
            ->values();

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
        $archivedIds = $this->getArchivedItemIds();
        if (in_array((int) $id, $archivedIds, true)) {
            abort(404, 'Item not found.');
        }

        $item = Item::find($id);

        if (!$item) {
            abort(404, 'Item not found.');
        }

        return view('franchisee-staff.item.show', compact('item'));
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
