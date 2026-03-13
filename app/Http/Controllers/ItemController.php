<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;
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
                    $imagePaths[] = $upload['secure_url'];
                } else {
                    $imagePaths[] = $file->store('item_images', 'public');
                }
            }
        }
    }

    // Now create the item properly
    Item::create([
        'item_name'        => $request->item_name,
        'item_description' => $request->item_description,
        'price'            => $request->price,
        'stock_quantity'   => $request->stock_quantity,
        'item_category'    => $request->item_category,
        'item_image'       => json_encode($imagePaths),
    ]);

    return redirect()->route('admin.items.index')->with('success', 'Item added successfully!');
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

    $imagePaths = $item->item_images; // existing images

    if ($request->hasFile('item_image')) {
        $imagePaths = []; // replace all
        foreach ($request->file('item_image') as $file) {
            if ($file) {
                if ($this->cloudinary->isConfigured()) {
                    $upload = $this->cloudinary->upload($file, 'item_images', 'image');
                    $imagePaths[] = $upload['secure_url'];
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

    return redirect()->route('admin.items.index')->with('success', 'Item updated successfully!');
}

    public function archive($id)
    {
        $item = Item::findOrFail($id);

        // Mark as archived in a JSON file (do NOT delete from DB)
        $archivedIds = $this->getArchivedItemIds();
        if (!in_array($item->item_id, $archivedIds)) {
            $archivedIds[] = $item->item_id;
            $this->saveArchivedItemIds($archivedIds);
        }

        // Redirect back to the archived items page for the current role
        $prefix = auth()->guard('franchisor_staff')->check() ? 'franchisor-staff' : 'admin';

        return redirect()->route($prefix . '.items.archived')
            ->with('success', 'Item archived successfully!');
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
            ->with('success', 'Item restored successfully!');
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
