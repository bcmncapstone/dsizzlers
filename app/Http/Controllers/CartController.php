<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Branch;
use App\Models\Franchisee;
use App\Models\CartItem;
use App\Services\CloudinaryService;
use App\Services\FifoStockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CartController extends Controller
{
    public function __construct(
        private CloudinaryService $cloudinary,
        private FifoStockService $fifoStockService
    )
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

    private function getCartOwnerContext(string $cartKey): ?array
    {
        if ($cartKey === 'franchisee_staff' && auth()->guard('franchisee_staff')->check()) {
            return [
                'column' => 'fstaff_id',
                'id' => (int) auth()->guard('franchisee_staff')->id(),
            ];
        }

        if ($cartKey === 'franchisee' && auth()->guard('franchisee')->check()) {
            return [
                'column' => 'franchisee_id',
                'id' => (int) auth()->guard('franchisee')->id(),
            ];
        }

        return null;
    }

    private function getOwnerScopedBaseData(array $context): array
    {
        return [
            'franchisee_id' => $context['column'] === 'franchisee_id' ? $context['id'] : null,
            'fstaff_id' => $context['column'] === 'fstaff_id' ? $context['id'] : null,
        ];
    }

    private function syncSessionCartFromDatabase(string $cartKey): array
    {
        $context = $this->getCartOwnerContext($cartKey);

        if (! $context) {
            $cart = session()->get($cartKey, []);
            session()->put($cartKey, $cart);
            session()->put('cart_owner', $cartKey);
            return $cart;
        }

        $rows = CartItem::query()
            ->where($context['column'], $context['id'])
            ->get(['cart_item_id', 'item_id', 'quantity']);

        $itemIds = $rows->pluck('item_id')->map(fn ($id) => (int) $id)->all();
        $items = Item::query()
            ->whereIn('item_id', $itemIds)
            ->get()
            ->keyBy('item_id');

        $cart = [];

        foreach ($rows as $row) {
            $item = $items->get((int) $row->item_id);
            if (! $item) {
                CartItem::query()->where('cart_item_id', $row->cart_item_id)->delete();
                continue;
            }

            $availableStock = (int) $item->stock_quantity;
            if ($availableStock <= 0) {
                CartItem::query()->where('cart_item_id', $row->cart_item_id)->delete();
                continue;
            }

            $quantity = max(1, min((int) $row->quantity, $availableStock));
            if ((int) $row->quantity !== $quantity) {
                CartItem::query()
                    ->where('cart_item_id', $row->cart_item_id)
                    ->update(['quantity' => $quantity]);
            }

            $cart[$item->item_id] = [
                'name' => $item->item_name,
                'price' => $item->price,
                'quantity' => $quantity,
                'stock_quantity' => $availableStock,
                'item_images' => $item->item_images,
            ];
        }

        session()->put($cartKey, $cart);
        session()->put('cart_owner', $cartKey);

        return $cart;
    }

    private function persistCartItem(string $cartKey, int $itemId, int $quantity): void
    {
        $context = $this->getCartOwnerContext($cartKey);
        if (! $context) {
            return;
        }

        $baseData = $this->getOwnerScopedBaseData($context);

        CartItem::query()->updateOrCreate(
            array_merge($baseData, ['item_id' => $itemId]),
            ['quantity' => $quantity]
        );
    }

    private function removePersistedCartItem(string $cartKey, int $itemId): void
    {
        $context = $this->getCartOwnerContext($cartKey);
        if (! $context) {
            return;
        }

        $baseData = $this->getOwnerScopedBaseData($context);

        CartItem::query()
            ->where($baseData)
            ->where('item_id', $itemId)
            ->delete();
    }

    private function clearPersistedCart(string $cartKey): void
    {
        $context = $this->getCartOwnerContext($cartKey);
        if (! $context) {
            return;
        }

        $baseData = $this->getOwnerScopedBaseData($context);

        CartItem::query()
            ->where($baseData)
            ->delete();
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
        $cart = $this->syncSessionCartFromDatabase($cartKey);

        $layout = $cartKey === 'franchisee_staff' ? 'layouts.franchisee-staff' : 'layouts.franchisee';

        return view('cart.index', [
            'cart' => $cart,
            'total' => collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']),
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
        $quantity = max(1, (int) $request->input('quantity', 1));

        $cart = $this->syncSessionCartFromDatabase($cartKey);

        $existingQuantity = isset($cart[$id]) ? (int) $cart[$id]['quantity'] : 0;
        $newQuantity = $existingQuantity + $quantity;

        if ($newQuantity > (int) $item->stock_quantity) {
            return redirect()->back()->with('error', 'Quantity exceeds available stock!')->with('flash_timeout', 3000);
        }

        $this->persistCartItem($cartKey, (int) $id, $newQuantity);
        $this->syncSessionCartFromDatabase($cartKey);

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
        $quantity = max(1, (int) $request->input('quantity', 1));

        $cart = $this->syncSessionCartFromDatabase($cartKey);

        if (! isset($cart[$id])) {
            return redirect()->back()->with('error', 'Item not found in cart!')->with('flash_timeout', 3000);
        }

        if ($quantity > (int) $item->stock_quantity) {
            return redirect()->back()->with('error', 'Quantity exceeds available stock!')->with('flash_timeout', 3000);
        }

        $this->persistCartItem($cartKey, (int) $id, $quantity);
        $this->syncSessionCartFromDatabase($cartKey);

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

        $this->removePersistedCartItem($cartKey, (int) $id);
        $cart = $this->syncSessionCartFromDatabase($cartKey);

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
        $cart = $this->syncSessionCartFromDatabase($cartKey);

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
                        'name' => $item->item_name,
                        'price' => $item->price,
                        'quantity' => $quantity,
                        'stock_quantity' => $item->stock_quantity,
                        'item_images' => $item->item_images,
                    ];
                }
            }

            // Use the temporary cart for checkout instead of persisted cart
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
            'total' => collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']),
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
        $cart = $this->syncSessionCartFromDatabase($cartKey);
        $isBuyNowFlow = $request->has('buy_now_items');

        // Handle "Buy Now" items passed from checkout form
        if ($isBuyNowFlow) {
            $buyNowItems = json_decode($request->input('buy_now_items'), true);
            $cart = [];

            if (! is_array($buyNowItems)) {
                return redirect()->back()->with('error', 'Invalid checkout item data.')->with('flash_timeout', 3000);
            }

            foreach ($buyNowItems as $buyNowItem) {
                $itemId = (int) ($buyNowItem['item_id'] ?? 0);
                $quantity = (int) ($buyNowItem['quantity'] ?? 0);

                if ($itemId <= 0 || $quantity <= 0) {
                    continue;
                }

                $item = Item::find($itemId);
                if ($item) {
                    $cart[$itemId] = [
                        'name' => $item->item_name,
                        'price' => $item->price,
                        'quantity' => $quantity,
                        'stock_quantity' => $item->stock_quantity,
                        'item_images' => $item->item_images,
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

        try {
            DB::transaction(function () use ($cart, $fstaff_id, $franchisee_id, $request, $receiptPath) {
                foreach ($cart as $itemId => $cartItem) {
                    $item = Item::query()->lockForUpdate()->find($itemId);
                    $quantity = (int) ($cartItem['quantity'] ?? 0);

                    if (! $item) {
                        throw new \RuntimeException('One or more items are no longer available.');
                    }

                    if ($quantity <= 0) {
                        throw new \RuntimeException('Invalid quantity detected in checkout.');
                    }

                    // FIFO validation/allocation based on oldest stock-in lots.
                    $this->fifoStockService->allocateForCheckout($item, $quantity);

                    $item->stock_quantity -= $quantity;
                    $item->save();
                }

                $order = Order::create([
                    'fstaff_id' => $fstaff_id,
                    'franchisee_id' => $franchisee_id,
                    'total_amount' => collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']),
                    'order_status' => 'Pending',
                    'delivery_status' => 'Stock Deducted',
                    'name' => $request->name,
                    'contact' => $request->contact,
                    'address' => $request->address,
                    'payment_receipt' => $receiptPath,
                ]);

                foreach ($cart as $itemId => $cartItem) {
                    $quantity = (int) ($cartItem['quantity'] ?? 0);
                    $price = (float) ($cartItem['price'] ?? 0);

                    OrderDetail::create([
                        'order_id' => $order->order_id,
                        'item_id' => $itemId,
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $quantity * $price,
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage())
                ->with('flash_timeout', 3000);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to place order right now. Please try again.')
                ->with('flash_timeout', 3000);
        }

        // Clear persisted/session cart for normal cart checkout only.
        if (! $isBuyNowFlow) {
            $this->clearPersistedCart($cartKey);
            session()->forget($cartKey);
        }

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