<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\FranchiseeStock;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageOrderController extends Controller
{
    // Display all orders
    public function index()
    {
        $orders = Order::all();

        if (auth('admin')->check()) {
            return view('admin.manageOrder.index', compact('orders'));
        } elseif (auth('franchisor_staff')->check()) {
            return view('franchisor-staff.manageOrder.index', compact('orders'));
        }

        abort(403, 'Unauthorized action.');
    }

    // Show specific order details
    public function show($id)
    {
        $order = Order::with([
            'orderDetails.item',
            'franchisee:franchisee_id,franchisee_name',
            'franchiseeStaff:fstaff_id,fstaff_fname,fstaff_lname',
        ])->findOrFail($id);

        $paymentStatus = strtolower((string) ($order->payment_status ?? ''));
        $orderStatus = strtolower((string) ($order->order_status ?? ''));
        $canCancelOrder = ! in_array($paymentStatus, ['confirmed', 'paid'], true)
            && ! in_array($orderStatus, ['preparing', 'shipped', 'delivered', 'completed', 'cancelled'], true);

        if (auth('admin')->check()) {
            return view('admin.manageOrder.show', compact('order', 'canCancelOrder'));
        } elseif (auth('franchisor_staff')->check()) {
            return view('franchisor-staff.manageOrder.show', compact('order', 'canCancelOrder'));
        }

        abort(403, 'Unauthorized action.');
    }

    // Confirm payment
    public function confirmPayment($id)
    {
        $order = Order::with('orderDetails.item')->findOrFail($id);
        
        // Check if payment is already confirmed to prevent duplicate stock reduction
        if ($order->payment_status === 'confirmed') {
            if (auth('admin')->check()) {
                return redirect()->route('admin.manageOrder.show', $id)
                    ->with('info', 'Payment already confirmed.')
                    ->with('flash_timeout', 3000);
            } elseif (auth('franchisor_staff')->check()) {
                return redirect()->route('franchisor-staff.manageOrder.show', $id)
                    ->with('info', 'Payment already confirmed.')
                    ->with('flash_timeout', 3000);
            }
        }
        
        // Reduce stock quantity for each item in the order
        foreach ($order->orderDetails as $detail) {
            if ($detail->item) {
                $item = $detail->item;
                
                // Check if there's enough stock
                if ($item->stock_quantity < $detail->quantity) {
                    if (auth('admin')->check()) {
                        return redirect()->route('admin.manageOrder.show', $id)
                            ->with('error', "Insufficient stock for {$item->item_name}. Available: {$item->stock_quantity}, Required: {$detail->quantity}")
                            ->with('flash_timeout', 3000);
                    } elseif (auth('franchisor_staff')->check()) {
                        return redirect()->route('franchisor-staff.manageOrder.show', $id)
                            ->with('error', "Insufficient stock for {$item->item_name}. Available: {$item->stock_quantity}, Required: {$detail->quantity}")
                            ->with('flash_timeout', 3000);
                    }
                }
                
                // Reduce the stock
                $item->stock_quantity -= $detail->quantity;
                $item->save();
            }
        }
        
        $order->payment_status = 'confirmed';
        $order->save();

        if (auth('admin')->check()) {
            return redirect()->route('admin.manageOrder.show', $id)
                ->with('success', 'Payment confirmed and stock updated.')
                ->with('flash_timeout', 3000);
        } elseif (auth('franchisor_staff')->check()) {
            return redirect()->route('franchisor-staff.manageOrder.show', $id)
                ->with('success', 'Payment confirmed and stock updated.')
                ->with('flash_timeout', 3000);
        }

        abort(403, 'Unauthorized action.');
    }

    // Update order status
    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::with('orderDetails')->findOrFail($id);
        $oldStatus = $order->order_status;
        $newStatus = $request->input('order_status');
        
        DB::beginTransaction();
        try {
            $order->order_status = $newStatus;
            $order->save();

            // If status changed to 'Delivered', merge with franchisee stock
            // This applies to both staff orders (fstaff_id) and direct franchisee orders (franchisee_id)
            if ($newStatus === 'Delivered' && $oldStatus !== 'Delivered' && ($order->fstaff_id || $order->franchisee_id)) {
                $this->mergeStaffOrderToStock($order);
            }

            DB::commit();

            if (auth('admin')->check()) {
                return redirect()->route('admin.manageOrder.show', $id)
                    ->with('success', 'Order status updated.')
                    ->with('flash_timeout', 3000);
            } elseif (auth('franchisor_staff')->check()) {
                return redirect()->route('franchisor-staff.manageOrder.show', $id)
                    ->with('success', 'Order status updated.')
                    ->with('flash_timeout', 3000);
            }

            abort(403, 'Unauthorized action.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update order status: ' . $e->getMessage());
            
            if (auth('admin')->check()) {
                return redirect()->back()
                    ->with('error', 'Failed to update order: ' . $e->getMessage())
                    ->with('flash_timeout', 3000);
            } elseif (auth('franchisor_staff')->check()) {
                return redirect()->back()
                    ->with('error', 'Failed to update order: ' . $e->getMessage())
                    ->with('flash_timeout', 3000);
            }

            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Merge order items into franchisee stock inventory
     * Handles both staff orders (fstaff_id) and direct franchisee orders (franchisee_id)
     */
    private function mergeStaffOrderToStock(Order $order)
    {
        $franchiseeId = $order->franchisee_id;
        
        // If order doesn't have franchisee_id but has fstaff_id, get it from the staff member
        if (!$franchiseeId && $order->fstaff_id) {
            $staff = \App\Models\FranchiseeStaff::find($order->fstaff_id);
            if ($staff) {
                $franchiseeId = $staff->franchisee_id;
            }
        }
        
        // Safety check: ensure we have a franchisee_id
        if (!$franchiseeId) {
            Log::warning("Order #{$order->order_id} has no franchisee_id, cannot merge to stock.");
            throw new \Exception("Cannot merge order without franchisee_id. Please ensure the order is associated with a franchisee.");
        }

        // Check if this order has already been merged by looking for existing transactions
        $existingMerge = StockTransaction::where('reference_type', 'order_delivered')
            ->where('reference_id', $order->order_id)
            ->exists();

        if ($existingMerge) {
            Log::info("Order #{$order->order_id} already merged to stock, skipping.");
            return;
        }

        foreach ($order->orderDetails as $detail) {
            // Find or create franchisee stock record
            $stock = FranchiseeStock::firstOrCreate(
                [
                    'franchisee_id' => $franchiseeId,
                    'item_id' => $detail->item_id,
                ],
                [
                    'current_quantity' => 0,
                    'minimum_quantity' => 10,
                ]
            );

            // Update stock quantity
            $oldQuantity = $stock->current_quantity;
            $stock->current_quantity += $detail->quantity;
            $stock->save();

            // Record the transaction
            StockTransaction::create([
                'franchisee_id' => $franchiseeId,
                'item_id' => $detail->item_id,
                'transaction_type' => 'in',
                'quantity' => $detail->quantity,
                'balance_after' => $stock->current_quantity,
                'reference_type' => 'order_delivered',
                'reference_id' => $order->order_id,
                'notes' => "Order #{$order->order_id} delivered - items added to stock",
                'performed_by_type' => auth('admin')->check() ? 'admin' : 'franchisor_staff',
                'performed_by_id' => auth('admin')->check() ? auth('admin')->id() : auth('franchisor_staff')->id(),
            ]);

            Log::info("Merged order #{$order->order_id}: Added {$detail->quantity} of item #{$detail->item_id} to franchisee #{$franchiseeId} stock (from {$oldQuantity} to {$stock->current_quantity})");
        }
    }

    // Update admin notes
    public function updateNotes(Request $request, $id)
    {
        $request->validate([
            'order_notes' => 'nullable|string|max:1000',
        ]);

        $order = Order::findOrFail($id);
        $order->order_notes = $request->input('order_notes');
        $order->save();

        if (auth('admin')->check()) {
            return redirect()->route('admin.manageOrder.show', $id)
                ->with('success', 'Notes updated.')
                ->with('flash_timeout', 3000);
        } elseif (auth('franchisor_staff')->check()) {
            return redirect()->route('franchisor-staff.manageOrder.show', $id)
                ->with('success', 'Notes updated.')
                ->with('flash_timeout', 3000);
        }

        abort(403, 'Unauthorized action.');
    }

    // Cancel order
    public function cancelOrder($id)
    {
        $order = Order::findOrFail($id);
        $order->order_status = 'Cancelled';
        $order->save();

        if (auth('admin')->check()) {
            return redirect()->route('admin.manageOrder.show', $id)
                ->with('success', 'Order cancelled.')
                ->with('flash_timeout', 3000);
        } elseif (auth('franchisor_staff')->check()) {
            return redirect()->route('franchisor-staff.manageOrder.show', $id)
                ->with('success', 'Order cancelled.')
                ->with('flash_timeout', 3000);
        }

        abort(403, 'Unauthorized action.');
    }
}
