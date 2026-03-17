@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        @php
            $prefix = auth()->guard('franchisor_staff')->check() ? 'franchisor-staff' : 'admin';
        @endphp

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route($prefix . '.items.create') }}" class="btn btn-primary">
                Add Item
            </a>
            <a href="{{ route($prefix . '.items.archived') }}" class="btn btn-gray">
                View Archived Items
            </a>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1>Manage Item</h1>
            <p>View, edit, and organize your menu items</p>
        </div>

        <!-- Search Form -->
        <div class="filter-section">
            <form method="GET" action="{{ route($prefix . '.items.index') }}" class="filter-form" style="grid-template-columns: 1fr auto;">
                <div class="filter-group">
                    <input type="text" name="search" class="filter-select" placeholder="Search items by name..." value="{{ $search ?? '' }}">
                </div>
                <button type="submit" class="btn btn-info">Search</button>
            </form>
        </div>

        <!-- Items Table -->
        <div class="table-section">
            <div class="table-section-header">
                <h2>Item List</h2>
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
                        @forelse ($items as $item)
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
                                        <a href="{{ route($prefix . '.items.edit', $item->item_id) }}" class="table-action-btn table-action-edit">
                                            Edit
                                        </a>
                                        <form action="{{ route($prefix . '.items.archive', $item->item_id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to archive this item?');">
                                            @csrf
                                            <button type="submit" class="table-action-btn table-action-archive">
                                                Archive
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="table-empty">
                                    No items found. <a href="{{ route($prefix . '.items.create') }}" style="color: var(--dsizzlers-orange); font-weight: 600;">Create your first item →</a>
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
