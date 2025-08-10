<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $items = Item::query()
            ->when($search, function ($query, $search) {
                return $query->where('item_name', 'ILIKE', "%{$search}%");
            })
            ->get();

        return view('items.index', compact('items', 'search'));
    }

    public function create()
    {
        return view('items.create');
    }

public function store(Request $request)
{
    $request->validate([
        'item_name' => 'required|string|max:50',
        'item_description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'stock_quantity' => 'required|integer|min:0',
        'item_category' => 'nullable|string|max:30',
    ]);

    Item::create([
        'item_name' => $request->item_name,
        'item_description' => $request->item_description,
        'price' => $request->price,
        'stock_quantity' => $request->stock_quantity,
        'item_category' => $request->item_category,
    ]);

    return redirect()->route('admin.items.index')->with('success', 'Item added successfully!');
}

    public function edit($id)
    {
        $item = Item::findOrFail($id);
        return view('items.edit', compact('item'));
    }

  public function update(Request $request, $id)
{
    $request->validate([
        'item_name' => 'required',
        'item_description' => 'required',
        'price' => 'required|numeric|min:0',
        'stock_quantity' => 'required|integer|min:0',
        'item_category' => 'nullable|string|max:30',
    ]);

    $item = Item::findOrFail($id);
    $item->update([
        'item_name' => $request->item_name,
        'item_description' => $request->item_description,
        'price' => $request->price,
        'stock_quantity' => $request->stock_quantity,
        'item_category' => $request->item_category,
    ]);

    return redirect()->route('admin.items.index')->with('success', 'Item updated successfully!');
}

    public function archive($id)
    {
        $item = Item::findOrFail($id);

        // Simply delete if you want to simulate "archiving" without a status column
        $item->delete();

        return redirect()->route('admin.items.index')->with('success', 'Item deleted successfully!');
    }
}
