@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        @php
            $prefix = auth()->guard('franchisor_staff')->check() ? 'franchisor-staff' : 'admin';
        @endphp

        <!-- Back Button -->
        <div class="action-buttons">
            <a href="{{ route($prefix . '.items.index') }}" class="btn btn-secondary">
                ← Back to Items
            </a>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1>Archived Items</h1>
            <p>View items that have been archived and removed from active inventory</p>
        </div>

        <!-- Archived Items Table -->
        <div class="table-section">
            <div class="table-section-header">
                <h2>Archive History</h2>
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
                                    <form method="POST" action="{{ route($prefix . '.items.restore', $item->item_id) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to restore this item?');">
                                    @csrf
                                    <button type="submit" style="background-color:green; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">
                                    Restore
                                    </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="table-empty">
                                    No archived items found.
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
