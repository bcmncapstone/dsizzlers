@extends('layouts.app')

@section('content')
<h2>Edit Item</h2>

@php
    $prefix = auth()->guard('franchisor_staff')->check() ? 'franchisor-staff' : 'admin';
@endphp

@if ($errors->any())
    <div style="color: #b91c1c; margin-bottom: 12px;">
        <strong>Please fix the highlighted fields.</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route($prefix . '.items.update', $item->item_id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <label>Item Images:</label><br>
    <input type="file" name="item_image[]" accept="image/*" required style="{{ $errors->has('item_image') ? 'border:1px solid #b91c1c;' : '' }}"><br>
    <input type="file" name="item_image[]" accept="image/*"><br>
    <input type="file" name="item_image[]" accept="image/*"><br>
    @error('item_image')
        <div style="color:#b91c1c;">{{ $message }}</div>
    @enderror

    @forelse ($item->item_images as $img)
        <img src="{{ asset('storage/' . $img) }}" alt="Current image" width="120" class="me-1 mb-1">
    @empty
        No images
    @endforelse

    <label for="item_name">Item Name:</label>
    <input type="text" name="item_name" id="item_name" value="{{ old('item_name', $item->item_name) }}" required style="{{ $errors->has('item_name') ? 'border:1px solid #b91c1c;' : '' }}"><br>
    @error('item_name')
        <div style="color:#b91c1c;">{{ $message }}</div>
    @enderror

    <label for="item_description">Description:</label>
    <textarea name="item_description" id="item_description" style="{{ $errors->has('item_description') ? 'border:1px solid #b91c1c;' : '' }}">{{ old('item_description', $item->item_description) }}</textarea><br>
    @error('item_description')
        <div style="color:#b91c1c;">{{ $message }}</div>
    @enderror

    <label for="price">Price:</label>
    <input type="number" step="0.01" name="price" id="price" value="{{ old('price', $item->price) }}" required style="{{ $errors->has('price') ? 'border:1px solid #b91c1c;' : '' }}"><br>
    @error('price')
        <div style="color:#b91c1c;">{{ $message }}</div>
    @enderror

    <label for="stock_quantity">Stock Quantity:</label>
    <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', $item->stock_quantity) }}" required style="{{ $errors->has('stock_quantity') ? 'border:1px solid #b91c1c;' : '' }}"><br>
    @error('stock_quantity')
        <div style="color:#b91c1c;">{{ $message }}</div>
    @enderror

    <label for="item_category">Category:</label>
    <input type="text" name="item_category" id="item_category" value="{{ old('item_category', $item->item_category) }}" style="{{ $errors->has('item_category') ? 'border:1px solid #b91c1c;' : '' }}"><br>
    @error('item_category')
        <div style="color:#b91c1c;">{{ $message }}</div>
    @enderror

    <button type="submit">Update Item</button>
</form>

<a href="{{ route($prefix . '.items.index') }}">Back to Items</a>
@endsection
