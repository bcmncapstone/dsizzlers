<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

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
        $order = Order::findOrFail($id);

        if (auth('admin')->check()) {
            return view('admin.manageOrder.show', compact('order'));
        } elseif (auth('franchisor_staff')->check()) {
            return view('franchisor-staff.manageOrder.show', compact('order'));
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
                return redirect()->route('admin.manageOrder.show', $id)->with('info', 'Payment already confirmed.');
            } elseif (auth('franchisor_staff')->check()) {
                return redirect()->route('franchisor-staff.manageOrder.show', $id)->with('info', 'Payment already confirmed.');
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
                            ->with('error', "Insufficient stock for {$item->item_name}. Available: {$item->stock_quantity}, Required: {$detail->quantity}");
                    } elseif (auth('franchisor_staff')->check()) {
                        return redirect()->route('franchisor-staff.manageOrder.show', $id)
                            ->with('error', "Insufficient stock for {$item->item_name}. Available: {$item->stock_quantity}, Required: {$detail->quantity}");
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
            return redirect()->route('admin.manageOrder.show', $id)->with('success', 'Payment confirmed and stock updated.');
        } elseif (auth('franchisor_staff')->check()) {
            return redirect()->route('franchisor-staff.manageOrder.show', $id)->with('success', 'Payment confirmed and stock updated.');
        }

        abort(403, 'Unauthorized action.');
    }

    // Update order status
    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $orderStatus = $request->input('order_status');
        
        $order->order_status = $orderStatus;
        $order->save();

        if (auth('admin')->check()) {
            return redirect()->route('admin.manageOrder.show', $id)->with('success', 'Order status updated.');
        } elseif (auth('franchisor_staff')->check()) {
            return redirect()->route('franchisor-staff.manageOrder.show', $id)->with('success', 'Order status updated.');
        }

        abort(403, 'Unauthorized action.');
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
            return redirect()->route('admin.manageOrder.show', $id)->with('success', 'Notes updated.');
        } elseif (auth('franchisor_staff')->check()) {
            return redirect()->route('franchisor-staff.manageOrder.show', $id)->with('success', 'Notes updated.');
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
            return redirect()->route('admin.manageOrder.show', $id)->with('success', 'Order cancelled.');
        } elseif (auth('franchisor_staff')->check()) {
            return redirect()->route('franchisor-staff.manageOrder.show', $id)->with('success', 'Order cancelled.');
        }

        abort(403, 'Unauthorized action.');
    }
}
