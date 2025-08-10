@extends('layouts.app')

@section('content')
<h2>Add New Item</h2>

<form method="POST" action="{{ route('admin.items.store') }}">
    @csrf

    <label for="item_name">Item Name:</label>
    <input type="text" name="item_name" id="item_name" required><br>

    <label for="item_description">Description:</label>
    <textarea name="item_description" id="item_description"></textarea><br>

    <label for="price">Price:</label>
    <input type="number" step="0.01" name="price" id="price" required><br>

    <label for="stock_quantity">Stock Quantity:</label>
    <input type="number" name="stock_quantity" id="stock_quantity" required><br>

    <label for="item_category">Category:</label>
    <input type="text" name="item_category" id="item_category"><br>

    <button type="submit">Save Item</button>
</form>
@endsection
