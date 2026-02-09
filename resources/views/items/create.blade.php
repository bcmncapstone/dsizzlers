@extends('layouts.app')

@section('content')
<h2>Add New Item</h2>

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

<form method="POST" action="{{ route($prefix . '.items.store') }}" enctype="multipart/form-data">

    @csrf

    <label>Item Images:</label><br>
    <input type="file" name="item_image[]" accept="image/*" required style="{{ $errors->has('item_image') ? 'border:1px solid #b91c1c;' : '' }}"><br>
    <input type="file" name="item_image[]" accept="image/*"><br>
    <input type="file" name="item_image[]" accept="image/*"><br>
    @error('item_image')
        <div style="color:#b91c1c;">{{ $message }}</div>
    @enderror

    <label for="item_name">Item Name:</label>
    <input type="text" name="item_name" id="item_name" required value="{{ old('item_name') }}" style="{{ $errors->has('item_name') ? 'border:1px solid #b91c1c;' : '' }}"><br>
    @error('item_name')
        <div style="color:#b91c1c;">{{ $message }}</div>
    @enderror

    <label for="item_description">Description:</label>
    <textarea name="item_description" id="item_description" style="{{ $errors->has('item_description') ? 'border:1px solid #b91c1c;' : '' }}">{{ old('item_description') }}</textarea><br>
    @error('item_description')
        <div style="color:#b91c1c;">{{ $message }}</div>
    @enderror

    <label for="price">Price:</label>
    <input type="number" step="0.01" name="price" id="price" required value="{{ old('price') }}" style="{{ $errors->has('price') ? 'border:1px solid #b91c1c;' : '' }}"><br>
    @error('price')
        <div style="color:#b91c1c;">{{ $message }}</div>
    @enderror

    <label for="stock_quantity">Stock Quantity:</label>
    <input type="number" name="stock_quantity" id="stock_quantity" required value="{{ old('stock_quantity') }}" style="{{ $errors->has('stock_quantity') ? 'border:1px solid #b91c1c;' : '' }}"><br>
    @error('stock_quantity')
        <div style="color:#b91c1c;">{{ $message }}</div>
    @enderror

    <label for="item_category">Category:</label>
    <input type="text" name="item_category" id="item_category" value="{{ old('item_category') }}" style="{{ $errors->has('item_category') ? 'border:1px solid #b91c1c;' : '' }}"><br>
    @error('item_category')
        <div style="color:#b91c1c;">{{ $message }}</div>
    @enderror

    <button type="submit">Save Item</button>
</form>

<a href="{{ route($prefix . '.items.index') }}">Back to Items</a>
@endsection
