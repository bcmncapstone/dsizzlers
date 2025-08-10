@extends('layouts.app')

@section('content')
<h2>Manage Items</h2>

<a href="{{ route('admin.items.create') }}">Add Item</a>

<form method="GET" action="{{ route('admin.items.index') }}">
    <input type="text" name="search" placeholder="Search items..." value="{{ $search ?? '' }}">
    <button type="submit">Search</button>
</form>

<table>
    <thead>
        <tr>
            <th>Name</th><th>Description</th><th>Price</th><th>Quantity</th><th>Item Category</th><th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
        <tr>
            <td>{{ $item->item_name }}</td>
            <td>{{ $item->item_description }}</td>
            <td>{{ $item->price }}</td>
            <td>{{ $item->stock_quantity }}</td>
            <td>{{ $item->item_category }}</td>
            <td>
                <a href="{{ route('admin.items.edit', $item->item_id) }}">Edit</a>
                <form action="{{ route('admin.items.archive', $item->item_id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit">Archive</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
