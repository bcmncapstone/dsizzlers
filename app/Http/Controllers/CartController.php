<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;

class CartController extends Controller
{
    // Determine whether the current user is a franchisee or franchisee staff.
    private function getCartKey()
    {
        // Prefer an explicit session owner if set (keeps cart consistent across route-name variations)
        if (session()->has('cart_owner')) {
            return session('cart_owner');
        }

        $current = \Route::currentRouteName() ?? '';

        // Accept both naming styles — some routes use 'franchisee_staff.' and some use 'franchisee-staff.'
        if (strpos($current, 'franchisee_staff.') === 0 || strpos($current, 'franchisee-staff.') === 0) {
            return 'franchisee_staff';
        }

        return 'franchisee';
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
                $details['item_images'] = $item->item_images;
            } else {
                // Item no longer exists, remove from cart
                unset($cart[$id]);
            }
        }

        session()->put($cartKey, $cart);

    // Persist which guard owns this cart so later routes (checkout/summary) can resolve the same key
    session()->put('cart_owner', $cartKey);

        $layout = $cartKey === 'franchisee_staff' ? 'layouts.franchisee-staff' : 'layouts.franchisee';

        return view('cart.index', [
            'cart' => $cart,
            'total' => collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']),
            'layout' => $layout,
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
                "item_images" => $item->item_images,
            ];
        }

        session()->put($cartKey, $cart);

        // Use the same cart key logic for route prefixes so naming inconsistencies won't break redirects
        $prefix = $this->getCartKey();

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
        $cart[$id]['item_image'] = $item->item_image;

        session()->put($cartKey, $cart);

        $prefix = $this->getCartKey();

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

        // If the cart is now empty, remove the cart_owner marker
        if (empty($cart)) {
            session()->forget('cart_owner');
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

    // Ensure item_images are up to date
    foreach ($cart as $id => &$details) {
        $item = Item::find($id);
        if ($item) {
            $details['item_images'] = $item->item_images;
        } else {
            unset($cart[$id]);
        }
    }
    session()->put($cartKey, $cart);

    $layout = $cartKey === 'franchisee_staff' ? 'layouts.franchisee-staff' : 'layouts.franchisee';

    return view('cart.checkout', [
        'cart' => $cart,
        'total' => collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']),
        'cartKey' => $cartKey,
        'layout' => $layout,
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

    // Validate order details and payment receipt
    $request->validate([
        'name' => 'required|string|max:255',
        'contact' => 'required|string|max:20',
        'address' => 'required|string|max:255',
        'payment_receipt' => 'required|image|mimes:jpeg,png,jpg|max:5120',
    ]);

    // Store uploaded payment receipt
    $receiptPath = $request->file('payment_receipt')->store('receipts', 'public');

    // Identify who is logged in
    $fstaff_id = auth()->guard('franchisee_staff')->check() ? auth()->guard('franchisee_staff')->id() : null;
    $franchisee_id = auth()->guard('franchisee')->check() ? auth()->guard('franchisee')->id() : null;

    // Create the order
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

    // Create the order details
    foreach ($cart as $itemId => $cartItem) {
        OrderDetail::create([
            'order_id' => $order->order_id,
            'item_id'  => $itemId,
            'quantity' => $cartItem['quantity'],
            'price'    => $cartItem['price'],
            'subtotal' => $cartItem['quantity'] * $cartItem['price'],
        ]);
    }

    // Clear the cart session
    session()->forget($cartKey);
    session()->forget('cart_owner');

    // Redirect to proper order index based on user role
    if (auth()->guard('franchisee_staff')->check()) {
        $prefix = 'franchisee_staff';
    } elseif (auth()->guard('franchisee')->check()) {
        $prefix = 'franchisee';
    } else {
        $prefix = 'web';
    }

    return redirect()->route($prefix . '.orders.index')
        ->with('success', 'Order placed successfully! Pending verification.');
}

}
