<?php

namespace App\Http\Controllers\Franchisee;

use App\Http\Controllers\Controller;
use App\Models\FranchiseeStock;
use App\Models\StockTransaction;
use App\Models\Item;
use App\Models\Order;
use App\Models\FranchiseeStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Display the franchisee's stock inventory
     */
    public function index(Request $request)
    {
        $franchisee = Auth::guard('franchisee')->user();
        
        // Get stock with item details
        $stocks = FranchiseeStock::with('item')
            ->where('franchisee_id', $franchisee->franchisee_id)
            ->get();

        // Calculate statistics
        $totalItems = $stocks->count();
        $inStock = $stocks->where('current_quantity', '>', 0)->count();
        $lowStock = $stocks->filter(fn($s) => $s->isLowStock())->count();
        $outOfStock = $stocks->filter(fn($s) => $s->isOutOfStock())->count();

        return view('franchisee.stock.index', compact(
            'stocks',
            'totalItems',
            'inStock',
            'lowStock',
            'outOfStock'
        ));
    }

    /**
     * Show the form to adjust stock
     */
    public function edit($stockId)
    {
        $franchisee = Auth::guard('franchisee')->user();
        
        $stock = FranchiseeStock::with('item')
            ->where('stock_id', $stockId)
            ->where('franchisee_id', $franchisee->franchisee_id)
            ->firstOrFail();

        return view('franchisee.stock.edit', compact('stock'));
    }

    /**
     * Update stock quantity (manual adjustment)
     */
    public function update(Request $request, $stockId)
    {
        $franchisee = Auth::guard('franchisee')->user();
        
        $stock = FranchiseeStock::where('stock_id', $stockId)
            ->where('franchisee_id', $franchisee->franchisee_id)
            ->firstOrFail();

        $request->validate([
            'new_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $newQuantity = $request->new_quantity;
        $oldQuantity = $stock->current_quantity;

        // Validate the new quantity is not greater than what was originally delivered
        // This would require tracking total delivered - for now we just check it's not negative
        if ($newQuantity < 0) {
            return redirect()->back()->with('error', 'Quantity cannot be negative.');
        }

        DB::beginTransaction();
        try {
            // Update stock
            $stock->current_quantity = $newQuantity;
            $stock->save();

            // Record transaction
            $quantityChange = $newQuantity - $oldQuantity;
            StockTransaction::create([
                'franchisee_id' => $franchisee->franchisee_id,
                'item_id' => $stock->item_id,
                'transaction_type' => 'adjustment',
                'quantity' => $quantityChange,
                'balance_after' => $newQuantity,
                'reference_type' => 'manual',
                'notes' => $request->notes ?? 'Manual stock adjustment',
                'performed_by_type' => 'franchisee',
                'performed_by_id' => $franchisee->franchisee_id,
            ]);

            DB::commit();

            return redirect()->route('franchisee.stock.index')
                ->with('success', 'Stock updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update stock: ' . $e->getMessage());
        }
    }

    /**
     * Show stock transaction history with date filter
     */
    public function history(Request $request)
    {
        $franchisee = Auth::guard('franchisee')->user();
        
        $query = StockTransaction::with('item')
            ->where('franchisee_id', $franchisee->franchisee_id)
            ->orderBy('created_at', 'desc');

        // Apply date filter if provided
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Validate date range
        if ($request->has('start_date') && $request->has('end_date') && 
            $request->start_date && $request->end_date &&
            $request->end_date < $request->start_date) {
            return redirect()->back()->with('error', 'End date cannot be earlier than start date.');
        }

        $transactions = $query->paginate(20);

        return view('franchisee.stock.history', compact('transactions'));
    }

    /**
     * Show staff orders and their impact on stock
     */
    public function staffOrders(Request $request)
    {
        $franchisee = Auth::guard('franchisee')->user();
        
        // Get pending orders from franchisee staff
        $pendingQuery = Order::with(['orderDetails.item', 'franchiseeStaff'])
            ->where('franchisee_id', $franchisee->franchisee_id)
            ->whereNotNull('fstaff_id') // Orders made by staff
            ->whereIn('order_status', ['Pending', 'Preparing', 'Shipped']);

        // Get delivered orders from franchisee staff
        $deliveredQuery = Order::with(['orderDetails.item', 'franchiseeStaff'])
            ->where('franchisee_id', $franchisee->franchisee_id)
            ->whereNotNull('fstaff_id')
            ->where('order_status', 'Delivered');

        // Apply date filter if provided
        if ($request->has('start_date') && $request->start_date) {
            $pendingQuery->whereDate('created_at', '>=', $request->start_date);
            $deliveredQuery->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $pendingQuery->whereDate('created_at', '<=', $request->end_date);
            $deliveredQuery->whereDate('created_at', '<=', $request->end_date);
        }

        // Validate date range
        if ($request->has('start_date') && $request->has('end_date') && 
            $request->start_date && $request->end_date &&
            $request->end_date < $request->start_date) {
            return redirect()->back()->with('error', 'End date cannot be earlier than start date.');
        }

        $pendingOrders = $pendingQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'pending_page');
        $deliveredOrders = $deliveredQuery->orderBy('updated_at', 'desc')->paginate(10, ['*'], 'delivered_page');

        return view('franchisee.stock.staff-orders', compact('pendingOrders', 'deliveredOrders'));
    }
}
