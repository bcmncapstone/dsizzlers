<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Display list of orders
    public function index()
    {
        $query = Order::with('orderDetails.item');
        $viewPath = '';

        if (auth()->guard('franchisee')->check()) {
            $query->where('franchisee_id', auth()->guard('franchisee')->id());
            $viewPath = 'franchisee.orders.index';
        } elseif (auth()->guard('franchisee_staff')->check()) {
            $query->where('fstaff_id', auth()->guard('franchisee_staff')->id());
            // views for franchisee staff are stored under "franchisee-staff" directory
            $viewPath = 'franchisee-staff.orders.index';
        } else {
            abort(403, 'Unauthorized');
        }

        $orders = $query->latest()->get();
        return view($viewPath, compact('orders'));
    }

    // Show available items (if needed)
    public function create()
    {
        $items = Item::where('is_archived', false)->get();

        if (auth()->guard('franchisee_staff')->check()) {
            return view('franchisee-staff.orders.create', compact('items'));
        } elseif (auth()->guard('franchisee')->check()) {
            return view('franchisee.orders.create', compact('items'));
        }

        abort(403, 'Unauthorized');
    }

    // Store order
    public function store(Request $request)
    {
        $request->validate([
            'items.*.item_id' => 'required|exists:items,item_id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $orderData = [
            'order_date'   => now(),
            'order_status' => 'Pending',
            'total_amount' => 0,
        ];

        if (auth()->guard('franchisee')->check()) {
            $orderData['franchisee_id'] = auth()->guard('franchisee')->id();
            $redirectRoute = 'franchisee.orders.index';
        } elseif (auth()->guard('franchisee_staff')->check()) {
    $staff = auth()->guard('franchisee_staff')->user();
    $orderData['fstaff_id'] = $staff->fstaff_id;
    $orderData['franchisee_id'] = $staff->franchisee_id;
    $redirectRoute = 'franchisee_staff.orders.index';
        } else {
            abort(403, 'Unauthorized');
        }

        $order = Order::create($orderData);

        $total = 0;
        foreach ($request->items as $item) {
            $product = Item::findOrFail($item['item_id']);
            $subtotal = $product->price * $item['quantity'];
            $total += $subtotal;

            OrderDetail::create([
                'order_id' => $order->order_id,
                'item_id'  => $item['item_id'],
                'quantity' => $item['quantity'],
                'price'    => $product->price,
                'subtotal' => $subtotal,
            ]);
        }

        $order->update(['total_amount' => $total]);

        return redirect()->route($redirectRoute)->with('success', 'Order placed successfully!');
    }

    // Checkout
    public function checkout(Request $request)
    {
        // Resolve cart key: prefer explicit session owner (set by CartController) otherwise derive from route
        $cartKey = session('cart_owner') ?? ((strpos(\Route::currentRouteName() ?? '', 'franchisee_staff.') === 0 || strpos(\Route::currentRouteName() ?? '', 'franchisee-staff.') === 0) ? 'franchisee_staff' : 'franchisee');

        $cart = session()->get($cartKey, []);
        if (empty($cart)) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        $orderData = [
            'order_date'   => now(),
            'order_status' => 'Pending',
            'total_amount' => 0,
        ];

        if (auth()->guard('franchisee')->check()) {
            $orderData['franchisee_id'] = auth()->guard('franchisee')->id();
            $redirectRoute = 'franchisee.orders.index';
        } elseif (auth()->guard('franchisee_staff')->check()) {
    $staff = auth()->guard('franchisee_staff')->user();
    $orderData['fstaff_id'] = $staff->fstaff_id;
    $orderData['franchisee_id'] = $staff->franchisee_id;
    $redirectRoute = 'franchisee_staff.orders.index';
        } else {
            abort(403, 'Unauthorized');
        }

        $order = Order::create($orderData);
        $total = 0;

        foreach ($cart as $itemId => $details) {
            $subtotal = $details['price'] * $details['quantity'];
            $total += $subtotal;

            OrderDetail::create([
                'order_id' => $order->order_id,
                'item_id'  => $itemId,
                'quantity' => $details['quantity'],
                'price'    => $details['price'],
                'subtotal' => $subtotal,
            ]);
        }

        $order->update(['total_amount' => $total]);
    // Clear the correct cart key
    session()->forget($cartKey);

        return redirect()->route($redirectRoute)->with('success', 'Order placed successfully!');
    }

    // Show specific order details
    public function show($id)
    {
        $order = Order::with('orderDetails.item')->findOrFail($id);

        if (auth()->guard('franchisee_staff')->check()) {
            return view('franchisee-staff.orders.show', compact('order'));
        } elseif (auth()->guard('franchisee')->check()) {
            return view('franchisee.orders.show', compact('order'));
        }

        abort(403, 'Unauthorized');
    }
}
