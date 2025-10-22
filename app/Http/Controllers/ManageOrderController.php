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
        $order = Order::findOrFail($id);
        $order->payment_status = 'confirmed';
        $order->save();

        if (auth('admin')->check()) {
            return redirect()->route('admin.manageOrder.show', $id)->with('success', 'Payment confirmed.');
        } elseif (auth('franchisor_staff')->check()) {
            return redirect()->route('franchisor-staff.manageOrder.show', $id)->with('success', 'Payment confirmed.');
        }

        abort(403, 'Unauthorized action.');
    }

    // Update delivery status
    public function updateDelivery(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->delivery_status = $request->input('delivery_status');
        $order->save();

        if (auth('admin')->check()) {
            return redirect()->route('admin.manageOrder.show', $id)->with('success', 'Delivery status updated.');
        } elseif (auth('franchisor_staff')->check()) {
            return redirect()->route('franchisor-staff.manageOrder.show', $id)->with('success', 'Delivery status updated.');
        }

        abort(403, 'Unauthorized action.');
    }

    // Cancel order
    public function cancelOrder($id)
    {
        $order = Order::findOrFail($id);
        $order->order_status = 'cancelled';
        $order->save();

        if (auth('admin')->check()) {
            return redirect()->route('admin.manageOrder.show', $id)->with('success', 'Order cancelled.');
        } elseif (auth('franchisor_staff')->check()) {
            return redirect()->route('franchisor-staff.manageOrder.show', $id)->with('success', 'Order cancelled.');
        }

        abort(403, 'Unauthorized action.');
    }
}
