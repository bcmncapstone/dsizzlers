@extends('layouts.app')

@section('content')
<h2>Archived Items</h2>
<a href="{{ route('admin.items.index') }}">Back to Items</a>

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
        @foreach ($items as $item)
        <tr>
            <td>{{ $item->item_name }}</td>
            <td>{{ $item->item_description }}</td>
            <td>{{ $item->price }}</td>
            <td>{{ $item->stock_quantity }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
