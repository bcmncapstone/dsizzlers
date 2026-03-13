@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('admin.stock.franchisee-inventory') }}" class="btn btn-accent">
                📦 Manage Franchisee Inventory
            </a>
            <a href="{{ route('admin.items.archived') }}" class="btn btn-gray">
                🗂️ Archived Items
            </a>
            <a href="{{ route('admin.items.create') }}" class="btn btn-info">
                ➕ Add Stock Item
            </a>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1>Manage Stock</h1>
            <p>View and manage item stock inventory</p>
        </div>

        <!-- Stock Table -->
        <div class="table-section">
            <div class="table-section-header">
                <h2>Stock Items</h2>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>
                                    @forelse ($item->item_images as $img)
                                        <img src="{{ media_url($img) }}" class="table-image" alt="{{ $item->item_name }}">
                                    @empty
                                        <span class="table-image-placeholder">No image</span>
                                    @endforelse
                                </td>
                                <td>
                                    <div class="table-item-name">{{ $item->item_name }}</div>
                                </td>
                                <td>
                                    <div class="table-item-desc">{{ $item->item_description }}</div>
                                </td>
                                <td>
                                    <div class="table-price">₱{{ number_format($item->price, 2) }}</div>
                                </td>
                                <td>
                                    <div class="table-quantity">{{ $item->stock_quantity }}</div>
                                </td>
                                <td>
                                    <div class="table-category">{{ $item->item_category }}</div>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.items.edit', $item->item_id) }}" class="table-action-btn table-action-edit">
                                            ✏️ Update
                                        </a>
                                        <form action="{{ route('admin.items.archive', $item->item_id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to archive this item?');">
                                            @csrf
                                            <button type="submit" class="table-action-btn table-action-archive">
                                                🗄️ Archive
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="table-empty">
                                    No items found in stock.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection
