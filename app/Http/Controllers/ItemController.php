<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;
use App\Models\StockIn;
use App\Services\CloudinaryService;

class ItemController extends Controller
{
    public function __construct(private CloudinaryService $cloudinary)
    {
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        // Load archived item IDs from a small JSON file (no DB change)
        $archivedIds = $this->getArchivedItemIds();

        // Show only items that are not archived
        $items = Item::query()
            ->when($search, function ($query, $search) {
                return $query->where('item_name', 'ILIKE', "%{$search}%");
            })
            ->when(!empty($archivedIds), function ($query) use ($archivedIds) {
                return $query->whereNotIn('item_id', $archivedIds);
            })
            ->get();

        return view('items.index', compact('items', 'search'));
    }

    public function create()
    {
        return view('items.create');
    }

    /**
     * Check if an item with the given name already exists (AJAX endpoint).
     */
    public function checkDuplicate(Request $request)
    {
        $name = trim((string) $request->input('item_name', ''));

        if ($name === '') {
            return response()->json(['exists' => false]);
        }

        $archivedIds = $this->getArchivedItemIds();

        $existing = Item::query()
            ->whereRaw('LOWER(item_name) = ?', [mb_strtolower($name)])
            ->when(!empty($archivedIds), fn($q) => $q->whereNotIn('item_id', $archivedIds))
            ->first();

        if (!$existing) {
            return response()->json(['exists' => false]);
        }

        $prefix = auth()->guard('franchisor_staff')->check() ? 'franchisor-staff' : 'admin';

        return response()->json([
            'exists' => true,
            'item'   => [
                'item_id'        => $existing->item_id,
                'item_name'      => $existing->item_name,
                'stock_quantity' => $existing->stock_quantity,
                'price'          => $existing->price,
                'item_category'  => $existing->item_category,
            ],
            'edit_url' => route($prefix . '.items.edit', $existing->item_id),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:50',
            'item_description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'stock_quantity' => 'required|integer|min:0',
            'item_category' => 'nullable|string|max:30',
            'item_image' => 'required',
            'item_image.*' => 'image|max:10240',
        ], [
            'stock_quantity.required' => 'Please enter the stock quantity.',
            'stock_quantity.integer' => 'Stock quantity must be a whole number.',
            'stock_quantity.min' => 'Stock quantity cannot be negative.',
            'price.required' => 'Please enter the price.',
            'price.numeric' => 'Price must be a whole number.',
            'price.min' => 'Price must be greater than zero.',
        ]);

    $imagePaths = [];

    if ($request->hasFile('item_image')) {
        foreach ($request->file('item_image') as $file) {
            if ($file) {
                if ($this->cloudinary->isConfigured()) {
                    $upload = $this->cloudinary->upload($file, 'item_images', 'image');
                    $imagePaths[] = $upload['public_id'];
                } else {
                    $imagePaths[] = $file->store('item_images', 'public');
                }
            }
        }
    }

    // Now create the item properly
    $item = Item::create([
        'item_name'        => $request->item_name,
        'item_description' => $request->item_description,
        'price'            => $request->price,
        'stock_quantity'   => $request->stock_quantity,
        'item_category'    => $request->item_category,
        'item_image'       => json_encode($imagePaths),
    ]);

    if ((int) $item->stock_quantity > 0) {
        $restockedBy = auth('admin')->id() ?? auth('franchisor_staff')->id() ?? 0;

        StockIn::create([
            'item_id' => $item->item_id,
            'quantity_received' => (int) $item->stock_quantity,
            'received_date' => now(),
            'supplier_name' => 'Initial stock',
            'restocked_by' => $restockedBy,
        ]);
    }

    return redirect()->route('admin.items.index')
        ->with('success', 'Item added successfully!')
        ->with('flash_timeout', 3000);
}

    public function edit($id)
    {
        $item = Item::findOrFail($id);
        return view('items.edit', compact('item'));
    }

   public function update(Request $request, $id)
{
        $request->validate([
            'item_name' => 'required',
            'item_description' => 'required',
            'price' => 'required|numeric|min:0.01',
            'stock_quantity' => 'required|integer|min:0',
            'item_category' => 'nullable|string|max:30',
            'item_image' => 'nullable',
            'item_image.*' => 'image|max:10240',
        ], [
            'price.min' => 'Price must be greater than zero.',
        ]);

    $item = Item::findOrFail($id);
    $oldStockQuantity = (int) $item->stock_quantity;

    $imagePaths = $item->item_images; // existing images

    if ($request->hasFile('item_image')) {
        $imagePaths = []; // replace all
        foreach ($request->file('item_image') as $file) {
            if ($file) {
                if ($this->cloudinary->isConfigured()) {
                    $upload = $this->cloudinary->upload($file, 'item_images', 'image');
                    $imagePaths[] = $upload['public_id'];
                } else {
                    $imagePaths[] = $file->store('item_images', 'public');
                }
            }
        }
    }

    $item->item_name = $request->item_name;
    $item->item_description = $request->item_description;
    $item->price = $request->price;
    $item->stock_quantity = $request->stock_quantity;
    $item->item_category = $request->item_category;
    $item->item_image = json_encode($imagePaths);

    $item->save();

    $newStockQuantity = (int) $item->stock_quantity;
    if ($newStockQuantity > $oldStockQuantity) {
        $restockedBy = auth('admin')->id() ?? auth('franchisor_staff')->id() ?? 0;

        StockIn::create([
            'item_id' => $item->item_id,
            'quantity_received' => $newStockQuantity - $oldStockQuantity,
            'received_date' => now(),
            'supplier_name' => 'Manual increase',
            'restocked_by' => $restockedBy,
        ]);
    }

    return redirect()->route('admin.items.index')
        ->with('success', 'Item updated successfully!')
        ->with('flash_timeout', 3000);
}

    public function archive($id)
    {
        $item = Item::findOrFail($id);

        if ((int) $item->stock_quantity > 0) {
            return redirect()
                ->back()
                ->with('error', 'Cannot archive this item while stock is still available. ')
                ->with('flash_timeout', 3000);
        }

        // Mark as archived in a JSON file (do NOT delete from DB)
        $archivedIds = $this->getArchivedItemIds();
        if (!in_array($item->item_id, $archivedIds)) {
            $archivedIds[] = $item->item_id;
            $this->saveArchivedItemIds($archivedIds);
        }

        // Redirect back to the archived items page for the current role
        $prefix = auth()->guard('franchisor_staff')->check() ? 'franchisor-staff' : 'admin';

        return redirect()->route($prefix . '.items.archived')
            ->with('success', 'Item archived successfully!')
            ->with('flash_timeout', 3000);
    }

    public function archived()
    {
        // Load archived items based on IDs stored in JSON file
        $archivedIds = $this->getArchivedItemIds();
        $items = empty($archivedIds)
            ? collect()
            : Item::whereIn('item_id', $archivedIds)->get();
        return view('items.archived', compact('items'));
    }

    public function restore($id)
    {
        // Remove the item ID from the archived list
        $archivedIds = $this->getArchivedItemIds();
        $archivedIds = array_values(array_filter($archivedIds, function ($archivedId) use ($id) {
            return (int) $archivedId !== (int) $id;
        }));
        $this->saveArchivedItemIds($archivedIds);

        return redirect()->route('admin.items.archived')
            ->with('success', 'Item restored successfully!')
            ->with('flash_timeout', 3000);
    }

    /**
     * Read archived item IDs from storage/app/archived_items.json
     * This avoids any database schema changes.
     */
    protected function getArchivedItemIds(): array
    {
        if (!Storage::disk('local')->exists('archived_items.json')) {
            return [];
        }

        $raw = Storage::disk('local')->get('archived_items.json');
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Persist archived item IDs into storage/app/archived_items.json
     */
    protected function saveArchivedItemIds(array $ids): void
    {
        // Ensure unique, numeric IDs
        $ids = array_values(array_unique(array_map('intval', $ids)));
        Storage::disk('local')->put('archived_items.json', json_encode($ids));
    }
}
