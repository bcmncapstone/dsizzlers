<?php

namespace App\Http\Controllers\FranchisorStaff;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Display items catalog
     */
    public function index(Request $request)
    {
        $items = Item::orderBy('item_name', 'asc')->get();

        return view('franchisor-staff.stock.index', compact('items'));
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

            DB::commit();

            return redirect()->route('franchisor-staff.stock.index')
                ->with('success', 'Item quantity updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update item: ' . $e->getMessage());
        }
    }

    /**
     * Cancel/reverse adjustment
     */
    public function cancel($itemId)
    {
        return redirect()->route('franchisor-staff.stock.index')
            ->with('info', 'Item update cancelled.');
    }
}
