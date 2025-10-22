@extends('layouts.app')

@section('content')
<h2>Archived Items</h2>

@php
    $prefix = auth()->guard('franchisor_staff')->check() ? 'franchisor-staff' : 'admin';
@endphp

<a href="{{ route($prefix . '.items.index') }}">Back to Items</a>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Quantity</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($items as $item)
            <tr>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->item_description }}</td>
                <td>{{ $item->price }}</td>
                <td>{{ $item->stock_quantity }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center;">No archived items found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
