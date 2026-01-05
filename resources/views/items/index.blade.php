@extends('layouts.app')

@section('content')
<h2>Manage Items</h2>

@php
    // Detect whether the logged-in user is franchisor staff or franchisor (admin)
    $prefix = auth()->guard('franchisor_staff')->check() ? 'franchisor-staff' : 'admin';
@endphp

<!-- Action Links -->
<a href="{{ route($prefix . '.items.create') }}">Add Item</a>
<a href="{{ route($prefix . '.items.archived') }}">View Archived Items</a>

<!-- Search Form -->
<form method="GET" action="{{ route($prefix . '.items.index') }}">
    <input type="text" name="search" placeholder="Search items..." value="{{ $search ?? '' }}">
    <button type="submit">Search</button>
</form>

<!-- Items Table -->
<table>
    <thead>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Item Category</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($items as $item)
            <tr>
                <td>
                    @forelse ($item->item_images as $img)
                        <img src="{{ asset('storage/' . $img) }}" width="60" class="me-1 mb-1 rounded" alt="Item Image">
                    @empty
                        No image
                    @endforelse
                </td>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->item_description }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
                <td>{{ $item->stock_quantity }}</td>
                <td>{{ $item->item_category }}</td>
                <td>
                    <!-- Edit Link -->
                    <a href="{{ route($prefix . '.items.edit', $item->item_id) }}">Edit</a>

                    <!-- Archive Form -->
                    <form action="{{ route($prefix . '.items.archive', $item->item_id) }}"
                          method="POST"
                          style="display:inline;"
                          onsubmit="return confirm('Are you sure you want to archive this item?');">
                        @csrf
                        <button type="submit">Archive</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center;">No items found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
