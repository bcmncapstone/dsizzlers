@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Available Items</h2>

    <div class="row">
        @foreach ($items as $item)
            <div class="col-md-4 mb-3">
                <div class="card p-3">
                    <h4>{{ $item->name }}</h4>
                    <p>₱{{ number_format($item->price, 2) }}</p>

                    <form action="{{ route('orders.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="items[0][item_id]" value="{{ $item->id }}">
                        <input type="number" name="items[0][quantity]" value="1" min="1" class="form-control mb-2">
                        <button type="submit" class="btn btn-primary">Buy Now</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
