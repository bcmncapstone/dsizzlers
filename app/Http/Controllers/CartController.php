<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;

class CartController extends Controller
{
    /**
     * Determine whether the current user is a franchisee or franchisee staff.
     */
    private function getCartKey()
    {
        return strpos(\Route::currentRouteName(), 'franchisee_staff.') === 0
            ? 'franchisee_staff'
            : 'franchisee';
    }

    /**
     * Display the cart page.
     */
    public function index()
    {
        $cartKey = $this->getCartKey();
        $cart = session()->get($cartKey, []);

        foreach ($cart as $id => &$details) {
            $item = Item::find($id);
            if ($item) {
                $details['stock_quantity'] = $item->stock_quantity;
            }
        }

        session()->put($cartKey, $cart);

        return view('cart.index', [
            'cart' => $cart,
            'total' => collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']),
        ]);
    }

    /**
     * Add an item to the cart (or create a new cart item).
     */
    public function add(Request $request, $id)
    {
        $cartKey = $this->getCartKey();
        $item = Item::findOrFail($id);
        $quantity = (int) $request->input('quantity', 1);

        $cart = session()->get($cartKey, []);

        if (isset($cart[$id])) {
            // Item already exists, just add the quantity
            $newQuantity = $cart[$id]['quantity'] + $quantity;
            if ($newQuantity > $item->stock_quantity) {
                return redirect()->back()->with('error', 'Quantity exceeds available stock!');
            }
            $cart[$id]['quantity'] = $newQuantity;
            $cart[$id]['stock_quantity'] = $item->stock_quantity;
        } else {
            // Add new item to cart
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

        session()->put($cartKey, $cart);

        $prefix = strpos(\Route::currentRouteName(), 'franchisee_staff.') === 0
            ? 'franchisee_staff'
            : 'franchisee';

        return redirect()->route($prefix . '.cart.index')
            ->with('success', 'Item added to cart successfully!');
    }

    /**
     * Update item quantity in the cart.
     */
    public function update(Request $request, $id)
    {
        $cartKey = $this->getCartKey();
        $item = Item::findOrFail($id);
        $quantity = (int) $request->input('quantity', 1);

        $cart = session()->get($cartKey, []);

        if (!isset($cart[$id])) {
            return redirect()->back()->with('error', 'Item not found in cart!');
        }

        if ($quantity > $item->stock_quantity) {
            return redirect()->back()->with('error', 'Quantity exceeds available stock!');
        }

        $cart[$id]['quantity'] = $quantity;
        $cart[$id]['stock_quantity'] = $item->stock_quantity;

        session()->put($cartKey, $cart);

        $prefix = strpos(\Route::currentRouteName(), 'franchisee_staff.') === 0
            ? 'franchisee_staff'
            : 'franchisee';

        return redirect()->route($prefix . '.cart.index')
            ->with('success', 'Cart updated successfully!');
    }

    /**
     * Remove an item from the cart.
     */
    public function remove($id)
    {
        $cartKey = $this->getCartKey();
        $cart = session()->get($cartKey, []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put($cartKey, $cart);
        }

        return redirect()->back()->with('success', 'Item removed from cart!');
    }

    /**
     * Display the checkout page.
     */
   public function checkout()
{
    $cartKey = $this->getCartKey();
    $cart = session()->get($cartKey, []);

    if (empty($cart)) {
        return redirect()->back()->with('error', 'Your cart is empty.');
    }

    return view('cart.checkout', [
        'cart' => $cart,
        'total' => collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']),
        'cartKey' => $cartKey,
    ]);
}

    /**
     * Place an order and clear the cart.
     */
    public function placeOrder(Request $request)
    {
        $cartKey = $this->getCartKey();
        $cart = session()->get($cartKey, []);

        if (empty($cart)) {
            return redirect()->route($this->getCartKey() . '.cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'payment_receipt' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $receiptPath = $request->file('payment_receipt')->store('receipts', 'public');

        $fstaff_id = auth()->guard('franchisee_staff')->check() ? auth()->id() : null;
        $franchisee_id = auth()->guard('franchisee')->check() ? auth()->id() : null;

        $order = Order::create([
            'fstaff_id'       => $fstaff_id,
            'franchisee_id'   => $franchisee_id,
            'total_amount'    => collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']),
            'order_status'    => 'Pending',
            'name'            => $request->name,
            'contact'         => $request->contact,
            'address'         => $request->address,
            'payment_receipt' => $receiptPath,
        ]);

        foreach ($cart as $itemId => $cartItem) {
            OrderDetail::create([
                'order_id' => $order->order_id,
                'item_id'  => $itemId,
                'quantity' => $cartItem['quantity'],
                'price'    => $cartItem['price'],
                'subtotal' => $cartItem['quantity'] * $cartItem['price'],
            ]);
        }

        session()->forget($cartKey);

   if (auth()->guard('franchisee_staff')->check()) {
    $prefix = 'franchisee_staff';
} elseif (auth()->guard('franchisee')->check()) {
    $prefix = 'franchisee';
} else {
    $prefix = 'web'; // fallback just in case
}

        return redirect()->route($prefix . '.orders.index')
            ->with('success', 'Order placed successfully! Pending verification.');
    }
}
