<?php

namespace App\Http\Controllers\FranchiseeStaff;

use App\Http\Controllers\Controller;
use App\Models\FranchiseeStock;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Display items and stock levels for the staff's franchisee
     */
    public function index()
    {
        $perPage = 10;
        $staff = Auth::guard('franchisee_staff')->user();
        $stocksQuery = FranchiseeStock::with('item')
            ->where('franchisee_id', $staff->franchisee_id);

        $totalItems = (clone $stocksQuery)->count();
        $inStock = (clone $stocksQuery)->where('current_quantity', '>', 0)->count();
        $lowStock = (clone $stocksQuery)
            ->where('current_quantity', '>', 0)
            ->whereColumn('current_quantity', '<=', 'minimum_quantity')
            ->count();
        $outOfStock = (clone $stocksQuery)->where('current_quantity', '<=', 0)->count();

        $stocks = $stocksQuery
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->withQueryString();

        // Add FIFO batch snapshot logic (same as franchisee)
        $fifoSnapshots = [];
        $fifoService = app(\App\Services\FranchiseeFifoStockService::class);
        foreach ($stocks as $stockRow) {
            $stock = $stockRow;
            if (! $stock instanceof \App\Models\FranchiseeStock) {
                $resolvedStockId = (int) ($stockRow->stock_id ?? 0);
                if ($resolvedStockId <= 0) {
                    continue;
                }
                $stock = FranchiseeStock::with('item')->find($resolvedStockId);
                if (! $stock) {
                    continue;
                }
            }
            $fifoSnapshots[(int) $stock->stock_id] = $fifoService->getRemainingLots($stock);
        }

        return view('franchisee-staff.stock.index', compact('stocks', 'fifoSnapshots', 'totalItems', 'inStock', 'lowStock', 'outOfStock'));
    }

    /**
     * Show the form to adjust stock
     */
    public function edit($stockId)
    {
        $staff = Auth::guard('franchisee_staff')->user();
        
        $stock = FranchiseeStock::with('item')
            ->where('stock_id', $stockId)
            ->where('franchisee_id', $staff->franchisee_id)
            ->firstOrFail();

        return view('franchisee-staff.stock.edit', compact('stock'));
    }

    /**
     * Update stock quantity
     */
    public function update(Request $request, $stockId)
    {
        $staff = Auth::guard('franchisee_staff')->user();

        $isInlineAdjust = $request->filled('adjust_by') && $request->filled('direction');

        if ($isInlineAdjust) {
            $request->validate([
                'adjust_by' => 'required|integer|min:1',
                'direction' => 'required|in:add,deduct',
                'notes' => 'nullable|string|max:500',
            ]);
        } else {
            $request->validate([
                'new_quantity' => 'required|integer|min:0',
                'notes' => 'nullable|string|max:500',
            ]);
        }

        $stock = FranchiseeStock::with('item')
            ->where('franchisee_id', $staff->franchisee_id)
            ->findOrFail($stockId);

        $oldQuantity = (int) $stock->current_quantity;
        $newQuantity = (int) $request->input('new_quantity', $oldQuantity);
        $transactionType = 'adjustment';
        $quantityChange = $newQuantity - $oldQuantity;

        if ($isInlineAdjust) {
            $adjustBy = (int) $request->adjust_by;
            $direction = $request->direction;
            $newQuantity = $direction === 'add'
                ? $oldQuantity + $adjustBy
                : $oldQuantity - $adjustBy;
            $transactionType = $direction === 'add' ? 'in' : 'out';
            $quantityChange = $direction === 'add' ? $adjustBy : -$adjustBy;
        }

        if ($newQuantity < 0) {
            return redirect()->back()
                ->with('error', 'Invalid quantity. Stock cannot be negative.')
                ->with('flash_timeout', 3000);
        }

        DB::beginTransaction();
        try {
            $stock->current_quantity = $newQuantity;
            $stock->save();

            StockTransaction::create([
                'franchisee_id' => $stock->franchisee_id,
                'item_id' => $stock->item_id,
                'transaction_type' => $transactionType,
                'quantity' => $quantityChange,
                'balance_after' => $newQuantity,
                'reference_type' => null,
                'reference_id' => null,
                'notes' => $request->notes ?? ($isInlineAdjust ? 'Stock adjustment by franchisee staff' : 'Manual stock adjustment by franchisee staff'),
                'performed_by_type' => 'franchisee_staff',
                'performed_by_id' => $staff->fstaff_id
            ]);

            DB::commit();

            return redirect()->route('franchisee-staff.stock.index')
                ->with('success', 'Stock updated successfully.')
                ->with('flash_timeout', 3000);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update stock: ' . $e->getMessage())
                ->with('flash_timeout', 3000);
        }
    }

    /**
     * Cancel/reverse stock adjustment
     */
    public function cancel($stockId)
    {
        // This can be used to reverse a transaction if needed
        return redirect()->route('franchisee-staff.stock.index')
            ->with('info', 'Stock adjustment cancelled.')
            ->with('flash_timeout', 3000);
    }
}
