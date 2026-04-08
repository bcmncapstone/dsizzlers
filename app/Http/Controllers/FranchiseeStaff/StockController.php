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
        $staff = Auth::guard('franchisee_staff')->user();
        $stocks = FranchiseeStock::with('item')
            ->where('franchisee_id', $staff->franchisee_id)
            ->get();

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

        return view('franchisee-staff.stock.index', compact('stocks', 'fifoSnapshots'));
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

        $request->validate([
            'adjust_by' => 'required|integer|min:1',
            'direction' => 'required|in:add,deduct',
            'notes' => 'nullable|string|max:500',
        ]);

        $stock = FranchiseeStock::with('item')
            ->where('franchisee_id', $staff->franchisee_id)
            ->findOrFail($stockId);

        $oldQuantity = $stock->current_quantity;
        $adjustBy = (int) $request->adjust_by;
        $direction = $request->direction;
        $newQuantity = $direction === 'add'
            ? $oldQuantity + $adjustBy
            : $oldQuantity - $adjustBy;

        if ($newQuantity < 0) {
            return redirect()->back()
                ->with('error', 'Invalid quantity. Stock cannot be negative.')
                ->with('flash_timeout', 3000);
        }

        DB::beginTransaction();
        try {
            $stock->current_quantity = $newQuantity;
            $stock->save();

            $transactionType = $direction === 'add' ? 'in' : 'out';

            StockTransaction::create([
                'franchisee_id' => $stock->franchisee_id,
                'item_id' => $stock->item_id,
                'transaction_type' => $transactionType,
                'quantity' => $adjustBy,
                'balance_after' => $newQuantity,
                'reference_type' => null,
                'reference_id' => null,
                'notes' => $request->notes ?? 'Stock adjustment by franchisee staff',
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
