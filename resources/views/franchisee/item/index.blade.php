@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Items</h1>

    {{-- Filter & Sort Form --}}
    <form method="GET" action="{{ route(request()->route()->getName()) }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="item_category" class="form-label">Filter by Category</label>
            <select name="item_category" id="item_category" class="form-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" {{ $selectedCategory == $category ? 'selected' : '' }}>
                        {{ ucfirst($category) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label for="sort_by" class="form-label">Sort By</label>
            <select name="sort_by" id="sort_by" class="form-select" onchange="this.form.submit()">
                <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Name</option>
                <option value="price" {{ $sortBy == 'price' ? 'selected' : '' }}>Price</option>
                <option value="quantity" {{ $sortBy == 'quantity' ? 'selected' : '' }}>Quantity</option>
            </select>
        </div>

        <div class="col-md-4">
            <label for="sort_order" class="form-label">Order</label>
            <select name="sort_order" id="sort_order" class="form-select" onchange="this.form.submit()">
                <option value="asc" {{ $sortOrder == 'asc' ? 'selected' : '' }}>Ascending</option>
                <option value="desc" {{ $sortOrder == 'desc' ? 'selected' : '' }}>Descending</option>
            </select>
        </div>
    </form>

    {{-- Items Table --}}
    <table class="table table-bordered table-hover mb-5">
        <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Category</th>
                <th>View</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->item_description }}</td>
                    <td>{{ number_format($item->price, 2) }}</td>
                    <td>{{ $item->stock_quantity }}</td>
                    <td>{{ ucfirst($item->item_category) }}</td>
                    <td>
                        <a href="{{ route('franchisee.item.show', $item->item_id) }}" class="btn btn-primary btn-sm">
                            View
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Card Layout with Add to Cart & Buy Now --}}
    <h2 class="mb-4">Available Items</h2>
    <div class="row">
        @foreach ($items as $item)
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $item->item_name }}</h5>
                        <p class="card-text">{{ $item->item_description }}</p>
                        <p class="card-text">₱{{ number_format($item->price, 2) }}</p>

                        @php
                            $prefix = strpos(Route::currentRouteName(), 'franchisee_staff.') === 0 ? 'franchisee_staff' : 'franchisee';
                        @endphp

                        {{-- Add to Cart --}}
                        <form action="{{ route($prefix . '.cart.add', $item->item_id) }}" method="POST" class="mb-2">
                            @csrf
                            <input type="number" name="quantity" value="1" min="1" max="{{ $item->stock_quantity }}" class="form-control mb-2" @if($item->stock_quantity == 0) disabled @endif>
                            <small class="text-muted">Available: {{ $item->stock_quantity }}</small>
                            <button type="submit" class="btn btn-primary w-100" @if($item->stock_quantity == 0) disabled @endif>
                                Add to Cart
                            </button>
                        </form>

                        {{-- Buy Now --}}
                        <form action="{{ route($prefix . '.orders.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="items[0][item_id]" value="{{ $item->item_id }}">
                            <input type="hidden" name="items[0][quantity]" value="1">
                            <button type="submit" class="btn btn-success w-100">Buy Now</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
