<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;    

class OrderController extends Controller
{
    // Show user’s orders
    public function index()
{
    $query = Order::with('orderDetails.item');

    if (auth()->guard('franchisee_staff')->check()) {
        $query->where('fstaff_id', auth()->id());
    } elseif (auth()->guard('admin_staff')->check()) {
        $query->where('astaff_id', auth()->id());
    }

    $orders = $query->get();

    return view('orders.index', compact('orders'));
}

    // Show available items to order
    public function create()
    {
        $items = Item::where('is_archived', false)->get();
        return view('orders.create', compact('items'));
    }

    // Store order when "Buy Now" clicked
    public function store(Request $request)
{
    $request->validate([
        'items.*.item_id' => 'required|exists:items,item_id',
        'items.*.quantity' => 'required|integer|min:1',
    ]);

    // Create order
    $order = Order::create([
        'fstaff_id'    => auth()->id(),   // or astaff_id if admin staff
        'order_date'   => now(),
        'order_status' => 'Pending',      // 👈 FIXED casing
        'total_amount' => 0,
    ]);

    $total = 0;

    // Save order details
    foreach ($request->items as $item) {
        $product = Item::findOrFail($item['item_id']);
        $subtotal = $product->price * $item['quantity'];
        $total += $subtotal;

        OrderDetail::create([
            'order_id' => $order->order_id, // 👈 FIXED
            'item_id'  => $item['item_id'],
            'quantity' => $item['quantity'],
            'price'    => $product->price,
            'subtotal' => $subtotal,
        ]);
    }

    // Update order total
    $order->update(['total_amount' => $total]);

    $prefix = strpos(\Route::currentRouteName(), 'franchisee_staff.') === 0 
        ? 'franchisee_staff' 
        : 'franchisee';

    return redirect()->route($prefix . '.orders.index')
        ->with('success', 'Order placed successfully!');
}

    // Checkout cart into order
  public function checkout(Request $request)
{
    $cart = session()->get('cart', []);

    if (empty($cart)) {
        return redirect()->back()->with('error', 'Your cart is empty.');
    }

    // Create new Order
    $order = Order::create([
        'fstaff_id'    => auth()->id(),   // or astaff_id if admin staff
        'order_date'   => now(),
        'order_status' => 'Pending',      // 👈 FIXED casing
        'total_amount' => 0,
    ]);

    $total = 0;

    // Insert all OrderDetails
    foreach ($cart as $itemId => $details) {
        $subtotal = $details['price'] * $details['quantity'];
        $total += $subtotal;

        OrderDetail::create([
            'order_id' => $order->order_id, // 👈 FIXED
            'item_id'  => $itemId,
            'quantity' => $details['quantity'],
            'price'    => $details['price'],
            'subtotal' => $subtotal,
        ]);
    }

    // Update order total
    $order->update(['total_amount' => $total]);

    // Clear cart
    session()->forget('cart');

    $prefix = strpos(\Route::currentRouteName(), 'franchisee_staff.') === 0 
        ? 'franchisee_staff' 
        : 'franchisee';

    return redirect()->route($prefix . '.cart.index')
        ->with('success', 'Order placed successfully!');
}
}
