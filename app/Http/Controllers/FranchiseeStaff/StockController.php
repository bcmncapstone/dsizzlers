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
        
        // Get stocks for the franchisee that this staff belongs to
        $stocks = FranchiseeStock::with('item')
            ->where('franchisee_id', $staff->franchisee_id)
            ->get();

        return view('franchisee-staff.stock.index', compact('stocks'));
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
            'new_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        $stock = FranchiseeStock::with('item')
            ->where('franchisee_id', $staff->franchisee_id)
            ->findOrFail($stockId);
            
        $oldQuantity = $stock->current_quantity;
        $newQuantity = $request->new_quantity;
        $difference = $newQuantity - $oldQuantity;

        // Check if trying to sell more than available
        if ($newQuantity < 0) {
            return redirect()->back()
                ->with('error', 'Invalid quantity. Stock cannot be negative.');
        }

        DB::beginTransaction();
        try {
            // Update stock
            $stock->current_quantity = $newQuantity;
            $stock->save();

            // Create transaction record
            $transactionType = $difference > 0 ? 'in' : ($difference < 0 ? 'out' : 'adjustment');
            
            StockTransaction::create([
                'franchisee_id' => $stock->franchisee_id,
                'item_id' => $stock->item_id,
                'transaction_type' => $transactionType,
                'quantity' => abs($difference),
                'balance_after' => $newQuantity,
                'reference_type' => null,
                'reference_id' => null,
                'notes' => $request->notes ?? 'Stock adjustment by franchisee staff',
                'performed_by_type' => 'franchisee_staff',
                'performed_by_id' => $staff->fstaff_id
            ]);

            DB::commit();

            return redirect()->route('franchisee-staff.stock.index')
                ->with('success', 'Stock updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update stock: ' . $e->getMessage());
        }
    }

    /**
     * Cancel/reverse stock adjustment
     */
    public function cancel($stockId)
    {
        // This can be used to reverse a transaction if needed
        return redirect()->route('franchisee-staff.stock.index')
            ->with('info', 'Stock adjustment cancelled.');
    }
}
