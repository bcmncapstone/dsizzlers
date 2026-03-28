<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Order;
use App\Models\FranchiseeStock;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageOrderController extends Controller
{
    // Display all orders
    public function index(Request $request)
    {
        // Keep status choices consistent with the update dropdown in show.blade.
        $availableStatuses = collect(['Pending', 'Preparing', 'Shipped', 'Delivered']);

        $selectedStatus = trim((string) $request->query('status', $request->query('order_status', '')));
        if ($selectedStatus !== '') {
            $selectedStatus = ucfirst(strtolower($selectedStatus));
        }

        if ($selectedStatus !== '' && ! $availableStatuses->contains($selectedStatus)) {
            $selectedStatus = '';
        }

        $orders = Order::query()
            ->when($selectedStatus !== '', function ($query) use ($selectedStatus) {
                $query->whereRaw('LOWER(order_status) = ?', [strtolower($selectedStatus)]);
            })
            ->latest('created_at')
            ->get();

        if (auth('admin')->check()) {
            return view('admin.manageOrder.index', compact('orders', 'availableStatuses', 'selectedStatus'));
        } elseif (auth('franchisor_staff')->check()) {
            return view('franchisor-staff.manageOrder.index', compact('orders', 'availableStatuses', 'selectedStatus'));
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

        if (strcasecmp((string) ($order->order_status ?? ''), 'Cancelled') === 0) {
            if (auth('admin')->check()) {
                return redirect()->route('admin.manageOrder.show', $id)
                    ->with('error', 'Cancelled orders cannot be confirmed for payment.')
                    ->with('flash_timeout', 3000);
            } elseif (auth('franchisor_staff')->check()) {
                return redirect()->route('franchisor-staff.manageOrder.show', $id)
                    ->with('error', 'Cancelled orders cannot be confirmed for payment.')
                    ->with('flash_timeout', 3000);
            }
        }

        $stockAlreadyDeducted = strcasecmp((string) ($order->delivery_status ?? ''), 'Stock Deducted') === 0;
        
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
        
        if (! $stockAlreadyDeducted) {
            // Backward compatibility for orders created before checkout-time deduction.
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

            $order->delivery_status = 'Stock Deducted';
        }
        
        $order->payment_status = 'confirmed';
        $order->save();

        $successMessage = $stockAlreadyDeducted
            ? 'Payment confirmed.'
            : 'Payment confirmed and stock updated.';

        if (auth('admin')->check()) {
            return redirect()->route('admin.manageOrder.show', $id)
                ->with('success', $successMessage)
                ->with('flash_timeout', 3000);
        } elseif (auth('franchisor_staff')->check()) {
            return redirect()->route('franchisor-staff.manageOrder.show', $id)
                ->with('success', $successMessage)
                ->with('flash_timeout', 3000);
        }

        abort(403, 'Unauthorized action.');
    }

    // Update order status
    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::with('orderDetails')->findOrFail($id);

        if (strcasecmp((string) ($order->order_status ?? ''), 'Cancelled') === 0) {
            if (auth('admin')->check()) {
                return redirect()->route('admin.manageOrder.show', $id)
                    ->with('error', 'Cancelled orders can no longer be updated.')
                    ->with('flash_timeout', 3000);
            } elseif (auth('franchisor_staff')->check()) {
                return redirect()->route('franchisor-staff.manageOrder.show', $id)
                    ->with('error', 'Cancelled orders can no longer be updated.')
                    ->with('flash_timeout', 3000);
            }
        }

        if (! in_array(strtolower((string) ($order->payment_status ?? '')), ['confirmed', 'paid'], true)) {
            if (auth('admin')->check()) {
                return redirect()->route('admin.manageOrder.show', $id)
                    ->with('error', 'Confirm payment before updating the order status.')
                    ->with('flash_timeout', 3000);
            } elseif (auth('franchisor_staff')->check()) {
                return redirect()->route('franchisor-staff.manageOrder.show', $id)
                    ->with('error', 'Confirm payment before updating the order status.')
                    ->with('flash_timeout', 3000);
            }
        }

        $oldStatus = $order->order_status;
        $newStatus = $request->input('order_status');

        if ($newStatus === 'Cancelled') {
            return $this->cancelOrder($id);
        }
        
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
        DB::beginTransaction();

        try {
            $order = Order::with('orderDetails')->lockForUpdate()->findOrFail($id);

            $paymentStatus = strtolower((string) ($order->payment_status ?? ''));
            $orderStatus = strtolower((string) ($order->order_status ?? ''));
            $canCancelOrder = ! in_array($paymentStatus, ['confirmed', 'paid'], true)
                && ! in_array($orderStatus, ['preparing', 'shipped', 'delivered', 'completed', 'cancelled'], true);

            if (! $canCancelOrder) {
                DB::rollBack();

                if (auth('admin')->check()) {
                    return redirect()->route('admin.manageOrder.show', $id)
                        ->with('error', 'Order cannot be cancelled in its current state.')
                        ->with('flash_timeout', 3000);
                } elseif (auth('franchisor_staff')->check()) {
                    return redirect()->route('franchisor-staff.manageOrder.show', $id)
                        ->with('error', 'Order cannot be cancelled in its current state.')
                        ->with('flash_timeout', 3000);
                }

                abort(403, 'Unauthorized action.');
            }

            $stockRestored = false;
            if (strcasecmp((string) ($order->delivery_status ?? ''), 'Stock Deducted') === 0) {
                foreach ($order->orderDetails as $detail) {
                    $item = Item::query()->lockForUpdate()->find($detail->item_id);
                    if (! $item) {
                        continue;
                    }

                    $item->stock_quantity += (int) $detail->quantity;
                    $item->save();
                }

                $order->delivery_status = 'Pending';
                $stockRestored = true;
            }

            $order->order_status = 'Cancelled';
            $order->save();

            DB::commit();

            $message = $stockRestored ? 'Order cancelled and stock restored.' : 'Order cancelled.';
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel order: ' . $e->getMessage());

            if (auth('admin')->check()) {
                return redirect()->route('admin.manageOrder.show', $id)
                    ->with('error', 'Failed to cancel order.')
                    ->with('flash_timeout', 3000);
            } elseif (auth('franchisor_staff')->check()) {
                return redirect()->route('franchisor-staff.manageOrder.show', $id)
                    ->with('error', 'Failed to cancel order.')
                    ->with('flash_timeout', 3000);
            }

            abort(403, 'Unauthorized action.');
        }

        if (auth('admin')->check()) {
            return redirect()->route('admin.manageOrder.show', $id)
                ->with('success', $message)
                ->with('flash_timeout', 3000);
        } elseif (auth('franchisor_staff')->check()) {
            return redirect()->route('franchisor-staff.manageOrder.show', $id)
                ->with('success', $message)
                ->with('flash_timeout', 3000);
        }

        abort(403, 'Unauthorized action.');
    }
}
