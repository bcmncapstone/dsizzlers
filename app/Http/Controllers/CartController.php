<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;

class CartController extends Controller
{
    // Show cart items
    public function index()
    {
        $cart = session()->get('cart', []);

        // Update stock_quantity in session for each item
        foreach ($cart as $id => &$details) {
            $item = Item::find($id);
            if ($item) {
                $details['stock_quantity'] = $item->stock_quantity;
            }
        }
        session()->put('cart', $cart);

        return view('cart.index', compact('cart'));
    }

    // Add or update item quantity in cart
    public function add(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        $quantity = (int) $request->input('quantity', 1);

        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $newQuantity = $quantity;
            if ($newQuantity > $item->stock_quantity) {
                return redirect()->back()->with('error', 'Quantity exceeds available stock!');
            }
            $cart[$id]['quantity'] = $newQuantity;
            $cart[$id]['stock_quantity'] = $item->stock_quantity;
        } else {
            if ($quantity > $item->stock_quantity) {
                return redirect()->back()->with('error', 'Quantity exceeds available stock!');
            }
            $cart[$id] = [
                "name" => $item->item_name,
                "price" => $item->price,
                "quantity" => $quantity,
                "stock_quantity" => $item->stock_quantity,
            ];
        }

        session()->put('cart', $cart);

        $prefix = strpos(\Route::currentRouteName(), 'franchisee_staff.') === 0 
            ? 'franchisee_staff' 
            : 'franchisee';

        return redirect()->route($prefix . '.cart.index')
            ->with('success', 'Cart updated successfully!');
    }

    // Remove item from cart
    public function remove($id)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return redirect()->back()->with('success', 'Item removed from cart!');
    }

    // Checkout cart
    public function checkout(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        // Create the order
        $order = Order::create([
            'astaff_id'    => null,
            'fstaff_id'    => null,
            'total_amount' => collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']),
            'order_status' => 'Pending',
        ]);

        // Save order details
        foreach ($cart as $itemId => $cartItem) {
            OrderDetail::create([
                'order_id'  => $order->order_id,
                'item_id'   => $itemId, 
                'quantity'  => $cartItem['quantity'],
                'price'     => $cartItem['price'],
                'subtotal'  => $cartItem['quantity'] * $cartItem['price'],
            ]);
        }

        // Clear cart
        session()->forget('cart');

        $prefix = strpos(\Route::currentRouteName(), 'franchisee_staff.') === 0 
            ? 'franchisee_staff' 
            : 'franchisee';

        return redirect()->route($prefix . '.cart.index')
            ->with('success', 'Order placed successfully!');
    }
}
