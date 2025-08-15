{{-- resources/views/franchisee/item/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Items</h1>

    {{-- Filter & Sort Form --}}
    <form method="GET" action="{{ route(request()->route()->getName()) }}" class="row g-3 mb-4">
        {{-- Category Filter --}}
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

        {{-- Sort By --}}
        <div class="col-md-4">
            <label for="sort_by" class="form-label">Sort By</label>
            <select name="sort_by" id="sort_by" class="form-select" onchange="this.form.submit()">
                <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Name</option>
                <option value="price" {{ $sortBy == 'price' ? 'selected' : '' }}>Price</option>
                <option value="quantity" {{ $sortBy == 'quantity' ? 'selected' : '' }}>Quantity</option>
            </select>
        </div>

        {{-- Sort Order --}}
        <div class="col-md-4">
            <label for="sort_order" class="form-label">Order</label>
            <select name="sort_order" id="sort_order" class="form-select" onchange="this.form.submit()">
                <option value="asc" {{ $sortOrder == 'asc' ? 'selected' : '' }}>Ascending</option>
                <option value="desc" {{ $sortOrder == 'desc' ? 'selected' : '' }}>Descending</option>
            </select>
        </div>
    </form>

    {{-- Items Table --}}
    <table class="table table-bordered table-hover">
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
                        {{-- Use the correct named route --}}
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
</div>
@endsection
