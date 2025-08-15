@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ $item->name }}</h2>

    <p><strong>Category:</strong> {{ $item->category }}</p>
    <p><strong>Price:</strong> ₱{{ number_format($item->price, 2) }}</p>
    <p><strong>Quantity:</strong> {{ $item->quantity }}</p>
    <p><strong>Description:</strong> {{ $item->description }}</p>

    <a href="{{ route('franchisee.items.index') }}" class="btn btn-secondary">Back to Items</a>
</div>
@endsection
