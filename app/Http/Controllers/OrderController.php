<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\FifoStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(private FifoStockService $fifoStockService)
    {
    }

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
        // For now, show all items; archived filtering is handled
        // in ItemController via a separate JSON-based archive list.
        $items = Item::all();

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

        try {
            DB::transaction(function () use ($request, $orderData) {
                $order = Order::create(array_merge($orderData, [
                    'delivery_status' => 'Stock Deducted',
                ]));

                $total = 0;
                foreach ($request->items as $item) {
                    $itemId = (int) ($item['item_id'] ?? 0);
                    $quantity = (int) ($item['quantity'] ?? 0);

                    $product = Item::query()->lockForUpdate()->find($itemId);
                    if (! $product) {
                        throw new \RuntimeException('One or more items are no longer available.');
                    }

                    if ($quantity <= 0) {
                        throw new \RuntimeException('Invalid quantity detected in checkout.');
                    }

                    $this->fifoStockService->allocateForCheckout($product, $quantity);

                    $product->stock_quantity -= $quantity;
                    $product->save();

                    $subtotal = (float) $product->price * $quantity;
                    $total += $subtotal;

                    OrderDetail::create([
                        'order_id' => $order->order_id,
                        'item_id'  => $itemId,
                        'quantity' => $quantity,
                        'price'    => $product->price,
                        'subtotal' => $subtotal,
                    ]);
                }

                $order->update(['total_amount' => $total]);
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()
                ->with('error', $e->getMessage())
                ->with('flash_timeout', 3000);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Unable to place order right now. Please try again.')
                ->with('flash_timeout', 3000);
        }

        return redirect()->route($redirectRoute)
            ->with('success', 'Order placed successfully!')
            ->with('flash_timeout', 3000);
    }

    // Checkout
    public function checkout(Request $request)
    {
        // Resolve cart key: prefer explicit session owner (set by CartController) otherwise derive from route
        $cartKey = session('cart_owner') ?? ((strpos(\Route::currentRouteName() ?? '', 'franchisee_staff.') === 0 || strpos(\Route::currentRouteName() ?? '', 'franchisee-staff.') === 0) ? 'franchisee_staff' : 'franchisee');

        $cart = session()->get($cartKey, []);
        if (empty($cart)) {
            return redirect()->back()
                ->with('error', 'Your cart is empty.')
                ->with('flash_timeout', 3000);
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

        try {
            DB::transaction(function () use ($cart, $orderData) {
                $order = Order::create(array_merge($orderData, [
                    'delivery_status' => 'Stock Deducted',
                ]));

                $total = 0;

                foreach ($cart as $itemId => $details) {
                    $quantity = (int) ($details['quantity'] ?? 0);
                    $price = (float) ($details['price'] ?? 0);

                    $product = Item::query()->lockForUpdate()->find($itemId);
                    if (! $product) {
                        throw new \RuntimeException('One or more items are no longer available.');
                    }

                    if ($quantity <= 0) {
                        throw new \RuntimeException('Invalid quantity detected in checkout.');
                    }

                    $this->fifoStockService->allocateForCheckout($product, $quantity);

                    $product->stock_quantity -= $quantity;
                    $product->save();

                    $subtotal = $price * $quantity;
                    $total += $subtotal;

                    OrderDetail::create([
                        'order_id' => $order->order_id,
                        'item_id'  => $itemId,
                        'quantity' => $quantity,
                        'price'    => $price,
                        'subtotal' => $subtotal,
                    ]);
                }

                $order->update(['total_amount' => $total]);
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->with('flash_timeout', 3000);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Unable to place order right now. Please try again.')
                ->with('flash_timeout', 3000);
        }

        // Clear the correct cart key
        session()->forget($cartKey);
        session()->forget('cart_owner');

        return redirect()->route($redirectRoute)
            ->with('success', 'Order placed successfully!')
            ->with('flash_timeout', 3000);
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
