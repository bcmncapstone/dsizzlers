<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Branch;
use App\Models\Franchisee;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Storage;

class CartController extends Controller
{
    public function __construct(private CloudinaryService $cloudinary)
    {
    }

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

    private function getArchivedBranchIds(): array
    {
        if (! Storage::disk('local')->exists('archived_branches.json')) {
            return [];
        }

        $raw = Storage::disk('local')->get('archived_branches.json');
        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $decoded)));
    }

    private function resolveCheckoutPrefill(): array
    {
        $franchiseeEmail = null;
        $fallbackName = '';
        $fallbackContact = '';
        $fallbackAddress = '';

        if (auth()->guard('franchisee')->check()) {
            $franchisee = auth()->guard('franchisee')->user();
            $franchiseeEmail = $franchisee?->franchisee_email;
            $fallbackName = (string) ($franchisee?->franchisee_name ?? '');
            $fallbackContact = (string) ($franchisee?->franchisee_contactNo ?? '');
            $fallbackAddress = (string) ($franchisee?->franchisee_address ?? '');
        } elseif (auth()->guard('franchisee_staff')->check()) {
            $staff = auth()->guard('franchisee_staff')->user();
            $fallbackName = trim(($staff->fstaff_fname ?? '') . ' ' . ($staff->fstaff_lname ?? ''));
            $fallbackContact = (string) ($staff->fstaff_contactNo ?? '');

            if (! empty($staff->franchisee_id)) {
                $franchisee = Franchisee::query()
                    ->select('franchisee_email', 'franchisee_address')
                    ->find($staff->franchisee_id);

                $franchiseeEmail = $franchisee?->franchisee_email;
                $fallbackAddress = (string) ($franchisee?->franchisee_address ?? '');
            }
        }

        if (empty($franchiseeEmail)) {
            return [
                'name' => $fallbackName,
                'contact' => $fallbackContact,
                'address' => $fallbackAddress,
            ];
        }

        $archivedBranchIds = $this->getArchivedBranchIds();

        $branch = Branch::query()
            ->whereRaw('LOWER(TRIM(email)) = LOWER(TRIM(?))', [$franchiseeEmail])
            ->whereRaw('branch_status = true')
            ->when(! empty($archivedBranchIds), function ($query) use ($archivedBranchIds) {
                return $query->whereNotIn('branch_id', $archivedBranchIds);
            })
            ->orderByDesc('contract_expiration')
            ->orderByDesc('branch_id')
            ->first();

        if (! $branch) {
            return [
                'name' => $fallbackName,
                'contact' => $fallbackContact,
                'address' => $fallbackAddress,
            ];
        }

        $branchName = trim(($branch->first_name ?? '') . ' ' . ($branch->last_name ?? ''));
        $branchContact = (string) ($branch->contact_number ?? '');
        $branchAddress = (string) ($branch->location ?? '');

        return [
            'name' => $branchName !== '' ? $branchName : $fallbackName,
            'contact' => $branchContact !== '' ? $branchContact : $fallbackContact,
            'address' => $branchAddress !== '' ? $branchAddress : $fallbackAddress,
        ];
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
                return redirect()->back()->with('error', 'Quantity exceeds available stock!')->with('flash_timeout', 3000);
            }
            $cart[$id]['quantity'] = $newQuantity;
            $cart[$id]['stock_quantity'] = $item->stock_quantity;
        } else {
            // Add new item to cart
            if ($quantity > $item->stock_quantity) {
                return redirect()->back()->with('error', 'Quantity exceeds available stock!')->with('flash_timeout', 3000);
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
            ->with('success', 'Item added to cart successfully!')->with('flash_timeout', 3000);
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
            return redirect()->back()->with('error', 'Item not found in cart!')->with('flash_timeout', 3000);
        }

        if ($quantity > $item->stock_quantity) {
            return redirect()->back()->with('error', 'Quantity exceeds available stock!')->with('flash_timeout', 3000);
        }

        $cart[$id]['quantity'] = $quantity;
        $cart[$id]['stock_quantity'] = $item->stock_quantity;
        $cart[$id]['item_image'] = $item->item_image;

        session()->put($cartKey, $cart);

        $prefix = $this->getCartKey();

        return redirect()->route($prefix . '.cart.index')
            ->with('success', 'Cart updated successfully!')->with('flash_timeout', 3000);
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

        return redirect()->back()->with('success', 'Item removed from cart!')->with('flash_timeout', 3000);
    }

    /**
     * Display the checkout page.
     */
   public function checkout(Request $request)
{
    $cartKey = $this->getCartKey();
    $cart = session()->get($cartKey, []);

    // Handle "Buy Now" items passed via GET request
    if ($request->has('items')) {
        $buyNowItems = $request->input('items');
        $tempCart = [];
        
        foreach ($buyNowItems as $buyNowItem) {
            $itemId = $buyNowItem['item_id'];
            $quantity = (int) $buyNowItem['quantity'];
            
            $item = Item::find($itemId);
            if ($item) {
                if ($quantity > $item->stock_quantity) {
                    return redirect()->back()->with('error', 'Quantity exceeds available stock!')->with('flash_timeout', 3000);
                }
                
                $tempCart[$itemId] = [
                    "name" => $item->item_name,
                    "price" => $item->price,
                    "quantity" => $quantity,
                    "stock_quantity" => $item->stock_quantity,
                    "item_images" => $item->item_images,
                ];
            }
        }
        
        // Use the temporary cart for checkout instead of session cart
        $cart = $tempCart;
    }

    if (empty($cart)) {
        return redirect()->back()->with('error', 'Your cart is empty.')->with('flash_timeout', 3000);
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

    $layout = $cartKey === 'franchisee_staff' ? 'layouts.franchisee-staff' : 'layouts.franchisee';
    $checkoutPrefill = $this->resolveCheckoutPrefill();

    return view('cart.checkout', [
        'cart' => $cart,
        'total' => collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']),
        'cartKey' => $cartKey,
        'layout' => $layout,
        'checkoutPrefill' => $checkoutPrefill,
    ]);
}

    /**
     * Place an order and clear the cart.
     */
   public function placeOrder(Request $request)
{
    $cartKey = $this->getCartKey();
    $cart = session()->get($cartKey, []);

    // Handle "Buy Now" items passed from checkout form
    if ($request->has('buy_now_items')) {
        $buyNowItems = json_decode($request->input('buy_now_items'), true);
        $cart = [];
        
        foreach ($buyNowItems as $buyNowItem) {
            $itemId = $buyNowItem['item_id'];
            $quantity = (int) $buyNowItem['quantity'];
            
            $item = Item::find($itemId);
            if ($item) {
                $cart[$itemId] = [
                    "name" => $item->item_name,
                    "price" => $item->price,
                    "quantity" => $quantity,
                    "stock_quantity" => $item->stock_quantity,
                    "item_images" => $item->item_images,
                ];
            }
        }
    }

    if (empty($cart)) {
        return redirect()->route($this->getCartKey() . '.cart.index')
            ->with('error', 'Your cart is empty.')->with('flash_timeout', 3000);
    }

    // Validate order details and payment receipt
    $request->validate([
        'name' => 'required|string|max:255',
        'contact' => 'required|string|max:20',
        'address' => 'required|string|max:255',
        'payment_receipt' => 'required|image|mimes:jpeg,png,jpg|max:5120',
    ]);

    // Store uploaded payment receipt in Cloudinary when configured.
    if ($this->cloudinary->isConfigured()) {
        $upload = $this->cloudinary->upload($request->file('payment_receipt'), 'receipts', 'image');
        $receiptPath = $upload['secure_url'];
    } else {
        $receiptPath = $request->file('payment_receipt')->store('receipts', 'public');
    }

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
        ->with('success', 'Order placed successfully! Pending verification.')->with('flash_timeout', 3000);
}

}